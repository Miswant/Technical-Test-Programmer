<template>
  <div style="font-family: DejaVu Sans, sans-serif; padding: 40px; color: #111827;">
    <div style="text-align: center; margin-bottom: 30px;">
      <h1 style="margin: 0; font-size: 26px;">Sertifikat Pengesahan</h1>
      <p style="margin: 8px 0 0; color: #6b7280;">Sistem Informasi Persetujuan Dokumen Kelayakan</p>
    </div>

    <div style="border: 2px solid #111827; padding: 24px; border-radius: 12px;">
      <p style="font-size: 14px; line-height: 1.8; text-align: justify;">
        Dengan ini dinyatakan bahwa permohonan dengan kode <strong>{{ $project->project_code }}</strong>
        atas nama <strong>{{ $project->user?->name }}</strong> telah <strong>DISAHKAN</strong> oleh sistem
        pada tanggal <strong>{{ optional($project->certificate_generated_at)->format('d F Y H:i') }}</strong>.
      </p>

      <table style="width: 100%; margin-top: 24px; border-collapse: collapse;">
        <tr>
          <td style="padding: 8px 0; width: 30%; color: #6b7280;">Kode Project</td>
          <td style="padding: 8px 0;">: {{ $project->project_code }}</td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #6b7280;">Pemohon</td>
          <td style="padding: 8px 0;">: {{ $project->user?->name }}</td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #6b7280;">Status Akhir</td>
          <td style="padding: 8px 0;">: APPROVED</td>
        </tr>
      </table>
    </div>
  </div>
</template>
