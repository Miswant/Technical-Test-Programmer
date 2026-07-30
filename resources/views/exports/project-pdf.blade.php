<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Project {{ $project->project_code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1, h2, h3 { margin: 0; }
        .header { margin-bottom: 20px; }
        .muted { color: #6b7280; }
        .section { margin-top: 16px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 9999px; background: #eef2ff; color: #3730a3; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Project {{ $project->project_code }}</h1>
        <p class="muted">{{ $project->title }}</p>
    </div>

    <div class="section">
        <table>
            <tr><th>Pemohon</th><td>{{ $project->user?->name }}</td></tr>
            <tr><th>Email</th><td>{{ $project->user?->email }}</td></tr>
            <tr><th>Status</th><td><span class="badge">{{ $project->status->value ?? $project->status }}</span></td></tr>
            <tr><th>Deskripsi</th><td>{{ $project->description ?? '-' }}</td></tr>
            <tr><th>Dibuat</th><td>{{ $project->created_at?->format('d F Y H:i') }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Dokumen</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama File</th>
                    <th>Tipe</th>
                    <th>Ukuran</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($project->documents as $doc)
                    <tr>
                        <td>{{ $doc->file_name }}</td>
                        <td>{{ $doc->mime_type }}</td>
                        <td>{{ number_format($doc->file_size / 1024, 2) }} KB</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Tidak ada dokumen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Audit Log</h3>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Actor</th>
                    <th>Perubahan</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($project->logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('d F Y H:i') }}</td>
                        <td>{{ $log->actor?->name }}</td>
                        <td>{{ $log->old_status?->value ?? '-' }} → {{ $log->new_status?->value ?? '-' }}</td>
                        <td>{{ $log->remarks ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Tidak ada audit log.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
