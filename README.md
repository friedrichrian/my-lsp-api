# LSP API Documentation

## Overview
API Laravel untuk sistem LSP (Lembaga Sertifikasi Profesi) yang mencakup manajemen asesi, assesor, form APL01, APL02, dan FR.IA.01.

## Base URL
```
http://localhost:8000/api
```

## Authentication
Semua endpoint memerlukan authentication menggunakan Laravel Sanctum. Include header:
```
Authorization: Bearer {token}
```

## Rate Limiting
- **10 requests per minute** untuk endpoint auth dan public
- **Unlimited** untuk endpoint yang memerlukan authentication

---

## 🔐 Authentication Endpoints

### 1. POST /auth/register
**Register user baru**

**Request Body:**
```json
{
    "username": "john_doe",
    "email": "john@example.com",
    "password": "password123",
    "jurusan_id": 1
}
```

**Response (201 Created):**
```json
{
    "message": "User created successfully",
    "user": {
        "id": 1,
        "username": "john_doe",
        "email": "john@example.com",
        "jurusan_id": 1
    }
}
```

### 2. POST /auth/login
**Login user**

**Request Body:**
```json
{
    "input": "john@example.com",
    "password": "password123"
}
```

**Response (200 OK):**
```json
{
    "message": "login success",
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "username": "john_doe",
        "email": "john@example.com",
        "jurusan_id": 1
    }
}
```

### 3. POST /auth/logout
**Logout user (memerlukan auth)**

**Response (200 OK):**
```json
{
    "message": "logout success"
}
```

### 4. GET /jurusan
**Mendapatkan daftar jurusan**

**Response (200 OK):**
```json
[
    {
        "id": 1,
        "kode_jurusan": "TI",
        "nama_jurusan": "Teknologi Informasi",
        "jenjang": "S1",
        "deskripsi": "Program Studi Teknologi Informasi"
    }
]
```

### 5. GET /user
**Mendapatkan data user yang sedang login (memerlukan auth)**

**Response (200 OK):**
```json
{
    "user": {
        "id": 1,
        "username": "john_doe",
        "email": "john@example.com",
        "role": "assesi",
        "jurusan_id": 1
    }
}
```

---

## 👥 User Management (Admin Only)

### 6. POST /assesi
**Membuat asesi baru (Admin only)**

**Request Body:**
```json
{
    "username": "jane_doe",
    "email": "jane@example.com",
    "password": "password123",
    "jurusan_id": 1,
    "nama_lengkap": "Jane Doe",
    "no_ktp": "1234567890123456",
    "tempat_lahir": "Jakarta",
    "tanggal_lahir": "1990-01-01",
    "alamat": "Jl. Contoh No. 123",
    "no_telepon": "081234567890",
    "jenis_kelamin": "Perempuan",
    "kode_pos": "12345",
    "kualifikasi_pendidikan": "S1"
}
```

### 7. PUT /assesi/{id}
**Update data asesi (Admin only)**

### 8. DELETE /assesi/{id}
**Hapus asesi (Admin only)**

### 9. POST /assesor
**Membuat assesor baru (Admin only)**

**Request Body:**
```json
{
    "nama_lengkap": "Dr. John Smith",
    "no_registrasi": "ASR001",
    "email": "john.smith@example.com",
    "jenis_kelamin": "Laki-laki",
    "no_telepon": "081234567890",
    "kompetensi": "Pemrograman Web"
}
```

### 10. GET /assesor/{id}
**Mendapatkan data assesor (Admin only)**

### 11. PUT /assesor/{id}
**Update data assesor (Admin only)**

### 12. DELETE /assesor/{id}
**Hapus assesor (Admin only)**

---

## 📋 Assessment Endpoints

### 13. POST /assesment/formapl01
**Submit form APL01 (memerlukan auth)**

**Request Body:**
```json
{
    "nama_lengkap": "John Doe",
    "no_ktp": "1234567890123456",
    "tanggal_lahir": "1990-01-01",
    "tempat_lahir": "Jakarta",
    "jenis_kelamin": "Laki-laki",
    "kebangsaan": "Indonesia",
    "alamat_rumah": "Jl. Contoh No. 123",
    "kode_pos": "12345",
    "no_telepon_rumah": "0211234567",
    "no_telepon": "081234567890",
    "email": "john@example.com",
    "kualifikasi_pendidikan": "S1",
    "nama_institusi": "Universitas Contoh",
    "jabatan": "Programmer",
    "alamat_kantor": "Jl. Kantor No. 456",
    "kode_pos_kantor": "67890",
    "fax_kantor": "0211234568",
    "email_kantor": "john@company.com",
    "status": "pending",
    "attachments": [
        {
            "file": "file.pdf",
            "description": "Ijazah S1"
        },
        {
            "file": "file2.pdf",
            "description": "Sertifikat Pelatihan"
        }
    ]
}
```

### 14. POST /assesment/formapl02
**Submit form APL02 (memerlukan auth)**

**Request Body:**
```json
{
    "skema_id": 1,
    "submissions": [
        {
            "unit_ke": 1,
            "kode_unit": "J.610000.001.01",
            "elemen": [
                {
                    "elemen_id": 1,
                    "kompetensinitas": "k",
                    "bukti_yang_relevan": [
                        {
                            "bukti_description": "Ijazah S1"
                        }
                    ]
                }
            ]
        }
    ]
}
```

### 15. GET /assesi
**Mendapatkan daftar asesi (memerlukan auth)**

### 16. GET /assesor
**Mendapatkan daftar assesor (memerlukan auth)**

### 17. GET /schema
**Mendapatkan daftar skema (memerlukan auth + approve middleware)**

### 18. GET /debug
**Debug endpoint (memerlukan auth)**

---

## ✅ Approval Endpoints (Admin Only)

### 19. GET /approvement/assesment/formapl01/{id}
**Melihat detail form APL01 untuk approval**

**Response (200 OK):**
```json
{
    "id": 1,
    "nama_lengkap": "John Doe",
    "no_ktp": "1234567890123456",
    "status": "pending",
    "user": {
        "id": 1,
        "username": "john_doe",
        "email": "john@example.com"
    },
    "attachments": [
        {
            "id": 1,
            "nama_dokumen": "ijazah.pdf",
            "description": "Ijazah S1",
            "view_url": "http://localhost:8000/api/form-apl01/attachment/1/view"
        }
    ]
}
```

### 20. POST /approvement/assesment/formapl01/{id}
**Approve/reject form APL01**

**Request Body:**
```json
{
    "status": "accepted"
}
```

### 21. GET /form-apl01/attachment/{id}/view
**Melihat attachment form APL01**

**Response:** File PDF

---

## 📊 APL02 Import & Management (Admin Only)

### 22. POST /apl02/import
**Import data APL02 dari file Word**

**Request Body:**
```json
{
    "file": "document.docx",
    "jurusan_id": 1
}
```

### 23. GET /apl02/{id}
**Mendapatkan data APL02**

---

## 🔍 FR.IA.01 - Ceklis Observasi Aktivitas

### Base URL: `/fr-ia01`

### 24. POST /fr-ia01/sessions
**Membuat sesi asesmen FR.IA.01 baru**

**Request Body:**
```json
{
    "judul_skema": "Pemrograman Junior",
    "nomor_skema": "SSP.BNSP.001.2023",
    "tuk": "TUK Teknologi Informasi Jakarta",
    "assesor_id": 1,
    "assesi_id": 1,
    "tanggal_asesmen": "2024-01-15"
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Sesi asesmen FR.IA.01 berhasil dibuat",
    "data": {
        "id": 1,
        "judul_skema": "Pemrograman Junior",
        "nomor_skema": "SSP.BNSP.001.2023",
        "tuk": "TUK Teknologi Informasi Jakarta",
        "tanggal_asesmen": "2024-01-15",
        "hasil_asesmen": null,
        "catatan_asesor": null,
        "status": "draft",
        "assesor": {
            "id": 1,
            "nama": "John Doe"
        },
        "assesi": {
            "id": 1,
            "nama": "Jane Smith"
        },
        "kelompok_pekerjaan": [
            {
                "id": 1,
                "nama_kelompok": "Kelompok 1",
                "umpan_balik": null,
                "unit_kompetensi": [
                    {
                        "id": 1,
                        "kode_unit": "J.610000.001.01",
                        "judul_unit": "Menggunakan Algoritma Pemrograman Dasar",
                        "elemen": [
                            {
                                "id": 1,
                                "nama_elemen": "Mengidentifikasi kebutuhan algoritma",
                                "kriteria_unjuk_kerja": [
                                    {
                                        "id": 1,
                                        "deskripsi_kuk": "Kebutuhan algoritma diidentifikasi sesuai dengan spesifikasi program",
                                        "ya": false,
                                        "tidak": false,
                                        "standar_industri": null,
                                        "penilaian_lanjut": null,
                                        "catatan": null
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        ]
    }
}
```

### 25. GET /fr-ia01/sessions/{id}
**Menampilkan data lengkap sesi asesmen**

### 26. PUT /fr-ia01/kuks/{id}
**Update penilaian KUK (Ya/Tidak, catatan, dll)**

**Request Body:**
```json
{
    "ya": true,
    "tidak": false,
    "standar_industri": "Menggunakan flowchart dan pseudocode",
    "penilaian_lanjut": "Kompeten",
    "catatan": "Asesi menunjukkan pemahaman yang baik"
}
```

### 27. PUT /fr-ia01/groups/{id}/feedback
**Update umpan balik untuk kelompok**

**Request Body:**
```json
{
    "umpan_balik": "Asesi menunjukkan pemahaman yang baik dalam algoritma dasar"
}
```

### 28. PUT /fr-ia01/sessions/{id}/result
**Update hasil asesmen final**

**Request Body:**
```json
{
    "hasil_asesmen": "kompeten",
    "catatan_asesor": "Asesi telah memenuhi semua kriteria",
    "status": "completed"
}
```

### 29. DELETE /fr-ia01/sessions/{id}
**Hapus sesi asesmen**

---

## 🔧 Middleware

### Authentication Middleware
- `auth:sanctum` - Memerlukan token authentication
- `admin` - Memerlukan role admin
- `approve` - Memerlukan permission approval
- `throttle:10,1` - Rate limiting 10 requests per minute

---

## 📊 Response Format

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": { ... }
}
```

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "field_name": ["The field name field is required."]
    }
}
```

### Not Found Error (404)
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### Unauthorized Error (401)
```json
{
    "message": "Invalid credentials"
}
```

---

## 🚀 Setup & Installation

### 1. Install Dependencies
```bash
composer install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup
```bash
php artisan migrate
php artisan db:seed --class=FrIa01Seeder
```

### 4. Storage Setup
```bash
php artisan storage:link
```

### 5. Run Server
```bash
php artisan serve
```

---

## 📁 File Structure

```
app/
├── Http/Controllers/
│   ├── AuthController.php
│   ├── UserController.php
│   ├── AssesiController.php
│   ├── AssesorController.php
│   ├── AssesmentController.php
│   ├── ApprovementController.php
│   ├── Apl02ImportController.php
│   └── FrIa01Controller.php
├── Models/
│   ├── User.php
│   ├── Assesi.php
│   ├── Assesor.php
│   ├── FormApl01.php
│   ├── FormApl02Submission.php
│   ├── AssessmentSession.php
│   ├── ObservationGroup.php
│   ├── ObservationUnit.php
│   ├── ObservationElement.php
│   └── ObservationKuk.php
└── Middleware/
    ├── AdminMiddleware.php
    └── ApprovementMiddleware.php
```

---

## 🔍 Testing

### Menggunakan Postman/Insomnia
1. Import collection dari dokumentasi
2. Set base URL: `http://localhost:8000/api`
3. Set authentication token di header

### Menggunakan HTTP Client Files
- `FR_IA_01_API_EXAMPLES.http` - Contoh testing FR.IA.01

---

## 📚 Documentation Files

1. **README.md** - Dokumentasi lengkap API (ini)
2. **FR_IA_01_API_DOCUMENTATION.md** - Dokumentasi khusus FR.IA.01
3. **FR_IA_01_API_EXAMPLES.http** - Contoh testing FR.IA.01
4. **setup_fr_ia01.md** - Panduan setup FR.IA.01
5. **check_database_structure.md** - Cek struktur database

---

## 🛠️ Troubleshooting

### Common Issues

1. **Authentication Error**
   - Pastikan token valid dan tidak expired
   - Cek format header: `Authorization: Bearer {token}`

2. **File Upload Error**
   - Pastikan file tidak melebihi 2MB
   - Format file harus PDF untuk attachments

3. **Database Error**
   - Jalankan `php artisan migrate:fresh --seed`
   - Cek koneksi database di `.env`

4. **Rate Limiting**
   - Tunggu 1 menit sebelum request berikutnya
   - Gunakan token yang berbeda

---

## 📞 Support

Untuk bantuan lebih lanjut:
- Buat issue di repository
- Hubungi tim development
- Cek dokumentasi lengkap di file terpisah
