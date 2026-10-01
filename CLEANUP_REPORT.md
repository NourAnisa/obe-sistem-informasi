# 🔍 CODE AUDIT & STATIC ANALYSIS REPORT

**Target Workspace:** `c:\xampp\htdocs\OBE_sistem_informasi`

**Scanned Metadata:**
- **PHP Backend Files:** 153 files scanned
- **Blade View Files:** 124 files scanned
- **Controller Routes Checked:** 243 route-to-controller linkages checked
- **Total Issues Identified:** 16 issues

## 📊 ISSUES SUMMARY
| Issue Type | Count | Severity |
|---|---|---|
| Security Warning (Credential) | 5 | High |
| Routing Warning | 10 | Medium/Low |
| Security Warning (SQLi) | 1 | High |

## 📋 DETAILED FINDINGS

### 1. Security Warning (Credential): Potential Hardcoded Credentials (array map)
- **File:** [app\Http\Controllers\BapPenilaianController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/BapPenilaianController.php)
- **Line:** 170
- **Evidence:**
  ```php
  'token' => 'nullable|string|max:8',
  ```

----------------------------------------

### 2. Routing Warning: Method 'show' is expected by routes but not found in controller class.
- **File:** [app\Http\Controllers\CpmkController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/CpmkController.php)
- **Line:** N/A
- **Evidence:**
  ```php
  public function show()
  ```

----------------------------------------

### 3. Routing Warning: Method '__invoke' is expected by routes but not found in controller class.
- **File:** [app\Http\Controllers\CpmkController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/CpmkController.php)
- **Line:** N/A
- **Evidence:**
  ```php
  public function __invoke()
  ```

----------------------------------------

### 4. Routing Warning: Method 'show' is expected by routes but not found in controller class.
- **File:** [app\Http\Controllers\DistribusiDosenController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/DistribusiDosenController.php)
- **Line:** N/A
- **Evidence:**
  ```php
  public function show()
  ```

----------------------------------------

### 5. Routing Warning: Method '__invoke' is expected by routes but not found in controller class.
- **File:** [app\Http\Controllers\DistribusiDosenController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/DistribusiDosenController.php)
- **Line:** N/A
- **Evidence:**
  ```php
  public function __invoke()
  ```

----------------------------------------

### 6. Security Warning (SQLi): Potential SQL Injection in DB::select/statement (interpolated variable)
- **File:** [app\Http\Controllers\ImportNilaiController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/ImportNilaiController.php)
- **Line:** 400
- **Evidence:**
  ```php
  DB::statement("ALTER TABLE users MODIFY COLUMN role $new");
  ```

----------------------------------------

### 7. Routing Warning: Method 'show' is expected by routes but not found in controller class.
- **File:** [app\Http\Controllers\KrsMahasiswaController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/KrsMahasiswaController.php)
- **Line:** N/A
- **Evidence:**
  ```php
  public function show()
  ```

----------------------------------------

### 8. Routing Warning: Method '__invoke' is expected by routes but not found in controller class.
- **File:** [app\Http\Controllers\KrsMahasiswaController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/KrsMahasiswaController.php)
- **Line:** N/A
- **Evidence:**
  ```php
  public function __invoke()
  ```

----------------------------------------

### 9. Security Warning (Credential): Potential Hardcoded Credentials (array map)
- **File:** [app\Http\Controllers\MahasiswaController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/MahasiswaController.php)
- **Line:** 40
- **Evidence:**
  ```php
  'password' => 'required|min:6',
  ```

----------------------------------------

### 10. Routing Warning: Method 'show' is expected by routes but not found in controller class.
- **File:** [app\Http\Controllers\SubCpmkController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/SubCpmkController.php)
- **Line:** N/A
- **Evidence:**
  ```php
  public function show()
  ```

----------------------------------------

### 11. Routing Warning: Method '__invoke' is expected by routes but not found in controller class.
- **File:** [app\Http\Controllers\SubCpmkController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/SubCpmkController.php)
- **Line:** N/A
- **Evidence:**
  ```php
  public function __invoke()
  ```

----------------------------------------

### 12. Security Warning (Credential): Potential Hardcoded Credentials (array map)
- **File:** [app\Http\Controllers\UserController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/UserController.php)
- **Line:** 29
- **Evidence:**
  ```php
  'password' => 'required|string|min:6',
  ```

----------------------------------------

### 13. Security Warning (Credential): Potential Hardcoded Credentials (array map)
- **File:** [app\Http\Controllers\UserController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/UserController.php)
- **Line:** 63
- **Evidence:**
  ```php
  'password' => 'nullable|string|min:6',
  ```

----------------------------------------

### 14. Routing Warning: Method 'show' is expected by routes but not found in controller class.
- **File:** [app\Http\Controllers\UserController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/UserController.php)
- **Line:** N/A
- **Evidence:**
  ```php
  public function show()
  ```

----------------------------------------

### 15. Routing Warning: Method '__invoke' is expected by routes but not found in controller class.
- **File:** [app\Http\Controllers\UserController.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Http/Controllers/UserController.php)
- **Line:** N/A
- **Evidence:**
  ```php
  public function __invoke()
  ```

----------------------------------------

### 16. Security Warning (Credential): Potential Hardcoded Credentials (array map)
- **File:** [app\Models\User.php](file:///c:/xampp/htdocs/OBE_sistem_informasi/app/Models/User.php)
- **Line:** 61
- **Evidence:**
  ```php
  'password'          => 'hashed',
  ```

----------------------------------------