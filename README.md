# Technical-Test-Programmer

Sistem Informasi Persetujuan Dokumen Kelayakan (SI Persetujuan)

## Pemilik

Proyek ini dimiliki oleh pemilik repository ini dan digunakan sebagai technical test / portfolio implementation.

## Tech Stack

- PHP 8.2+
- Laravel 11/12
- Vue 3 + Vite + Pinia + Axios + Vue Router
- PostgreSQL
- Redis
- Docker + Nginx

## Struktur

- `app/` Backend Laravel
- `database/` Migration, factory, seeder
- `frontend/` Frontend Vue 3
- `routes/` API routes
- `resources/` View PDF export
- `docker-compose.yml` Environment container

## Instalasi

### 1. Clone repository

```bash
git clone <repository-url>
cd Technical-Test-Programmer
```

### 2. Setup backend

```bash
cp .env.example .env
composer install
php artisan key:generate
```

### 3. Setup database

Pastikan PostgreSQL dan Redis aktif, lalu sesuaikan `.env` jika perlu.

```bash
php artisan migrate --seed
```

### 4. Setup frontend

```bash
cd frontend
cp .env.example .env
npm install
```

## Menjalankan Aplikasi

### Opsi 1: Docker

```bash
docker compose up --build
```

Akses aplikasi Laravel melalui:

- `http://localhost:8000`

### Opsi 2: Jalankan manual

#### Backend

```bash
php artisan serve
```

#### Queue worker

```bash
php artisan queue:work redis
```

#### Frontend

```bash
cd frontend
npm run dev
```

## Akun Seed Default

- Pemohon: `pemohon1@persetujuan.id` / `password`
- Penilai: `penilai1@persetujuan.id` / `password`

## Fitur Utama

- Auth & RBAC pemohon / penilai
- Workflow status permohonan
- Audit trail dan notifikasi
- Export Excel dan PDF
- Dashboard statistik dan chart
- Upload dokumen pendukung

## Lisensi

Proyek ini menggunakan lisensi sesuai pemilik repository. Jika tidak ditentukan lain, seluruh hak cipta tetap milik pemilik repository.
