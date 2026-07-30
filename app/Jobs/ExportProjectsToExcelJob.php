<?php

namespace App\Jobs;

use App\Exports\ProjectsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportProjectsToExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $filters,
        public string $path
    ) {
        $this->onQueue('exports');
    }

    public function handle(): void
    {
        Excel::store(new ProjectsExport($this->filters), $this->path, 'local');
    }
}
