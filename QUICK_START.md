# Quick Start - Copy & Paste

## ✅ Format Composer.json yang Benar

Gunakan salah satu dari opsi berikut di **composer.json** project Laravel Anda:

### Opsi 1: Dengan Alias (Recommended untuk Production)
```json
{
    "require": {
        "php": ">=7.0.0",
        "maatwebsite/excel": "dev-php74-fix-2.1 as 2.1.0"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/lerry1597/Laravel-Excel"
        }
    ]
}
```

### Opsi 2: Langsung ke Branch
```json
{
    "require": {
        "php": ">=7.0.0",
        "maatwebsite/excel": "dev-php74-fix-2.1"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/lerry1597/Laravel-Excel"
        }
    ]
}
```

### Opsi 3: Dengan Commit Hash (Paling Stable)
```json
{
    "require": {
        "php": ">=7.0.0",
        "maatwebsite/excel": "dev-php74-fix-2.1#dda3e20"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/lerry1597/Laravel-Excel"
        }
    ]
}
```

## 📦 Install Commands

```bash
# 1. Clear composer cache
composer clear-cache

# 2. Update maatwebsite/excel
composer update maatwebsite/excel

# 3. Verify installation
composer show maatwebsite/excel

# 4. Fix PHPExcel dependency
php fix-phpexcel-curly-braces.php
```

## 🔧 Download Fix Script

```bash
# Download script fix PHPExcel
curl -o fix-phpexcel-curly-braces.php https://raw.githubusercontent.com/lerry1597/Laravel-Excel/php74-fix-2.1/fix-phpexcel-curly-braces.php

# Atau dengan wget
wget https://raw.githubusercontent.com/lerry1597/Laravel-Excel/php74-fix-2.1/fix-phpexcel-curly-braces.php

# Windows PowerShell
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/lerry1597/Laravel-Excel/php74-fix-2.1/fix-phpexcel-curly-braces.php" -OutFile "fix-phpexcel-curly-braces.php"
```

## ✅ Test

```php
// routes/web.php
Route::get('/test-excel', function() {
    return Excel::create('Test', function($excel) {
        $excel->sheet('Sheet1', function($sheet) {
            $sheet->fromArray([
                ['Name', 'Email', 'Phone'],
                ['John Doe', 'john@test.com', '123456']
            ]);
        });
    })->download('xlsx');
});
```

Buka: `http://localhost:8000/test-excel`

## 🐛 Troubleshooting

### Error: Invalid version string "2.1.0-php74"
**❌ SALAH:**
```json
"maatwebsite/excel": "2.1.0-php74"
```

**✅ BENAR:**
```json
"maatwebsite/excel": "dev-php74-fix-2.1 as 2.1.0"
```

### Error: "VCS repository does not contain..."
```bash
# Clear cache dan coba lagi
composer clear-cache
composer update maatwebsite/excel -vvv
```

### Error masih ada setelah install
```bash
# Fix PHPExcel dependency
php fix-phpexcel-curly-braces.php

# Clear Laravel cache
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

## 🐳 Docker Setup

**Dockerfile:**
```dockerfile
FROM php:7.4-fpm

RUN apt-get update && apt-get install -y git zip unzip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy fix script dan jalankan
COPY fix-phpexcel-curly-braces.php ./
RUN php fix-phpexcel-curly-braces.php

# Copy rest of files
COPY . .

EXPOSE 9000
CMD ["php-fpm"]
```

## 📝 Catatan Penting

1. **Format Version:**
   - ❌ `"2.1.0-php74"` - TIDAK VALID
   - ✅ `"dev-php74-fix-2.1"` - VALID
   - ✅ `"dev-php74-fix-2.1 as 2.1.0"` - VALID (Recommended)

2. **Repositories:**
   - Harus ada `repositories` section di composer.json
   - URL harus tepat: `https://github.com/lerry1597/Laravel-Excel`

3. **PHPExcel:**
   - Laravel-Excel sendiri sudah bersih dari curly braces
   - Yang perlu di-fix adalah PHPExcel (dependency)
   - Jalankan script `fix-phpexcel-curly-braces.php` setelah `composer install`

4. **Docker:**
   - Fix PHPExcel di Dockerfile agar persistent
   - Jangan manual edit vendor (akan ke-replace)

---

**Repository:** https://github.com/lerry1597/Laravel-Excel  
**Branch:** php74-fix-2.1  
**Latest Commit:** dda3e20
