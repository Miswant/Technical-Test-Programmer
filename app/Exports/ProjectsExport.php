<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProjectsExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithEvents, ShouldQueue
{
    public function __construct(
        private readonly array $filters = []
    ) {
    }

    public function query()
    {
        return Project::query()
            ->with(['user:id,name,email'])
            ->when($this->filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ilike', "%{$search}%")
                        ->orWhere('project_code', 'ilike', "%{$search}%");
                });
            })
            ->when($this->filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($this->filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($this->filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Project Code',
            'Pemohon',
            'Email',
            'Judul',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    public function map($project): array
    {
        return [
            $project->project_code,
            $project->user?->name,
            $project->user?->email,
            $project->title,
            $project->status?->value ?? $project->status,
            $project->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->freezePane('A2');
            },
        ];
    }
}
