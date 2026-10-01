# Desain Aman Untuk Nilai `Sub-CPMK`

Dokumen ini menjelaskan perbaikan struktur data yang disarankan tanpa mengubah data existing saat ini.

## Masalah Saat Ini

- Tabel `nilai_sub_cpmk` hanya menyimpan `mahasiswa_id` dan `sub_cpmk_id`.
- Tidak ada `mata_kuliah_id` atau `semester_aktif` di tabel tersebut.
- Beberapa `Sub-CPMK` dipakai oleh lebih dari satu mata kuliah.
- Akibatnya, satu mahasiswa hanya bisa punya satu nilai untuk satu `Sub-CPMK`, walaupun `Sub-CPMK` itu muncul di beberapa mata kuliah berbeda.

## Dampak

- Nilai `Sub-CPMK` lintas mata kuliah bisa bentrok.
- Import aman harus melewati beberapa mata kuliah untuk menghindari overwrite.
- Pelacakan histori per semester tidak lengkap.

## Rekomendasi Struktur

Pilihan paling aman:

- Tambahkan kolom `mata_kuliah_id`
- Tambahkan kolom `semester_aktif`
- Jadikan kombinasi unik:
  `mahasiswa_id + mata_kuliah_id + semester_aktif + sub_cpmk_id`

## Bentuk Data Yang Disarankan

Setiap baris `nilai_sub_cpmk` idealnya merepresentasikan:

- satu mahasiswa
- satu mata kuliah
- satu semester aktif
- satu `Sub-CPMK`
- satu set komponen nilai

## Keuntungan

- `Sub-CPMK` yang sama bisa dipakai aman di banyak mata kuliah
- import per semester menjadi presisi
- laporan OBE lebih dapat diaudit
- tidak perlu skip mata kuliah yang berbagi `Sub-CPMK`

## Strategi Migrasi Aman

1. Buat tabel baru, misalnya `nilai_sub_cpmk_course`
2. Salin data existing dari `nilai_sub_cpmk` ke tabel baru dengan aturan fallback
3. Ubah importer dan kalkulasi OBE agar membaca tabel baru
4. Setelah stabil, baru pertimbangkan deprecate tabel lama

## Catatan Penting

- Dokumen ini hanya proposal desain
- Belum ada perubahan schema atau migrasi yang dijalankan
- Bobot existing tetap aman dan tidak disentuh
