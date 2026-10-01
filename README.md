# 🎓 Sistem Informasi OBE (Outcome-Based Education)
### Program Studi S1 Teknologi Informasi — Fakultas Teknik, Universitas Lambung Mangkurat
**Dosen Pengampu / Pengembang:** Ir. Nor Anisa & Tim Dosen ULM

---

## 📌 Ringkasan Sistem

Sistem Informasi OBE ini dikembangkan menggunakan **Laravel 11 Framework** untuk mengelola kurikulum berbasis *Outcome-Based Education* (CPL, CPMK, Sub-CPMK, dan penilaian portofolio mahasiswa).

Proyek ini telah dikonfigurasi dengan pipeline **CI/CD AutoDeploy** modern:
* **Framework:** Laravel 11 (PHP 8.2 / 8.3)
* **Basis Data:** MySQL / MariaDB (atau SQLite untuk testing)
* **Frontend Assets:** Vite + TailwindCSS
* **CI/CD Pipeline:** GitHub Actions (`.github/workflows/deploy.yml`)
* **Deployment Platform:** VPS Cloud Ubuntu 24.04 LTS via Dokploy Containerization

---

## 🚀 Alur CI/CD AutoDeploy (Pola Industri)

```text
Developer / Dosen git push ke branch main
        │
        ▼
GitHub Actions Runner Berjalan Otomatis:
  • 🔍 Step 1: Detect & Validate (Mendeteksi Laravel artisan & composer.json)
  • 🧪 Step 2: PHP Syntax Linting (Verifikasi sintaks seluruh file PHP)
  • 📦 Step 3: Package Deployment ZIP (Dotfiles disertakan, vendor & node_modules dikecualikan)
  • 🌐 Step 4: Notifikasi Webhook ke Server VPS (103.180.124.142:8080)
        │
        ▼
Server VPS (Dokploy / Nginx):
  • Mengambil rilis terbaru dan mengeksekusi container build
  • Memperbarui aplikasi secara real-time
        │
        ▼
Aplikasi live di:
  • http://103-180-124-142.sslip.io (Domain Server)
  • http://103.180.124.142:3000 (Dokploy Dashboard)

Di tab Actions → Job Summary:
Tersaji laporan lengkap status build, ukuran paket, dan waktu eksekusi.
```

---

## 💻 Panduan Menjalankan Secara Lokal (Local Development)

Jika ingin menjalankan proyek ini di laptop/PC menggunakan XAMPP:

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/NourAnisa/obe-sistem-informasi.git
   cd obe-sistem-informasi
   ```

2. **Pasang Dependensi PHP (Composer):**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment:**
   Salin file template `.env.example`:
   ```bash
   cp .env.example .env
   ```
   Buka file `.env` dan sesuaikan koneksi database MySQL XAMPP Anda:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=obe_unism
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Jalankan Migrasi Database:**
   ```bash
   php artisan migrate
   ```

6. **Pasang Dependensi Frontend & Jalankan Dev Server:**
   ```bash
   npm install
   npm run dev
   ```

7. **Jalankan Aplikasi:**
   ```bash
   php artisan serve
   ```
   Akses di browser melalui: `http://127.0.0.1:8000`

---

## ☁️ Panduan Deploy ke VPS Dokploy (Production)

1. Buka dashboard Dokploy di: [http://103.180.124.142:3000](http://103.180.124.142:3000).
2. Buat Project baru: `OBE-Sistem-Informasi`.
3. Klik **Add Application** ➔ Pilih **GitHub**.
4. Hubungkan repositori: `NourAnisa/obe-sistem-informasi`.
5. Pilih **Build Type:** `Nixpacks` (Dokploy akan otomatis mengenali Laravel).
6. Tambahkan environment variable di tab **Environment**:
   * `APP_KEY` (hasil dari artisan key:generate)
   * `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, dll.
7. Klik **Deploy** ➔ Aplikasi Laravel Anda live secara otomatis!

---

## 📄 Lisensi
Hak Cipta © 2026 Program Studi S1 Teknologi Informasi, Fakultas Teknik, Universitas Lambung Mangkurat.
Dilisensikan di bawah [MIT License](LICENSE).
