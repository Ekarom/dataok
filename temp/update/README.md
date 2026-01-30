# Update System - Documentation

## Overview
Sistem update otomatis yang mendukung update dari server lokal maupun remote dengan proxy untuk bypass CORS/Cloudflare.

## File Structure
```
data/
├── chckupdate.php          # UI untuk check update (admin dashboard)
├── proxy_check_update.php  # Server-side proxy untuk bypass CORS
└── update/
    ├── config.php          # Konfigurasi update system
    ├── files.php           # API endpoint untuk scan update files
    ├── download_update.php # Handler untuk download & install
    ├── get_progress.php    # Progress tracker
    └── files/              # Directory untuk .zip update files
        ├── 1.0.2.zip
        ├── 1.0.2.json      # Release notes (optional)
        ├── 1.0.3.zip
        └── 1.0.3.json
```

## Configuration

### Switching Between Local and Remote

Edit `update/config.php`:

```php
// Mode: 'local' atau 'remote'
define('UPDATE_MODE', 'remote'); // Ubah ke 'local' untuk testing lokal

// Remote Update Server URLs
define('REMOTE_UPDATE_SERVER', 'https://arsip.p171.net/update/files.php');
define('REMOTE_FILES_BASE', 'https://arsip.p171.net/update/files/');

// Local Update Server URLs
define('LOCAL_UPDATE_SERVER', 'http://localhost/data/update/files.php');
define('LOCAL_FILES_BASE', 'http://localhost/data/update/files/');
```

### Debug Mode

```php
// Enable untuk melihat error detail
define('UPDATE_DEBUG', true);
```

## How It Works

### Update Check Flow
1. User klik "Check for Updates" di dashboard
2. `chckupdate.php` → memanggil `proxy_check_update.php`
3. `proxy_check_update.php` → fetch dari server (local/remote) sesuai config
4. `files.php` → scan directory `files/` untuk .zip terbaru
5. Return JSON dengan info version, message, download_url
6. UI menampilkan info update + tombol download

### Download & Install Flow
1. User klik "Download Auto"
2. `chckupdate.php` → memanggil `update/download_update.php`
3. Download .zip dari URL
4. Extract ke root directory (`c:\wamp64\www\data\`)
5. Update version di database
6. Progress ditampilkan real-time

## Adding New Updates

### 1. Prepare Update Package
```bash
# Create zip file dengan nama versi
# Example: 1.0.3.zip
# Isi zip harus memiliki struktur yang sama dengan root directory
```

### 2. Create Release Notes (Optional)
```bash
# Create file JSON dengan nama yang sama
# Example: 1.0.3.json
```

Content `1.0.3.json`:
```json
- Fixed login bug
- Added 2FA support
- Improved update system
- Performance improvements
```

### 3. Upload Files
- **Local**: Copy ke `c:\wamp64\www\data\update\files\`
- **Remote**: Upload ke server remote di `update/files/`

### 4. Test
1. Set `UPDATE_MODE` sesuai kebutuhan
2. Akses dashboard → Check for Updates
3. Verify version terdeteksi
4. Test download & install

## Troubleshooting

### Error: "Failed to fetch"
- **Cause**: CORS atau network issue
- **Solution**: Sistem otomatis menampilkan tombol "Check Manually"

### Error: "HTTP 403"
- **Cause**: Cloudflare/WAF blocking
- **Solution**: 
  - Proxy sudah menggunakan browser headers
  - Jika tetap gagal, gunakan mode local atau whitelist IP

### Error: "Invalid JSON"
- **Cause**: Server return HTML instead of JSON
- **Solution**: 
  - Enable `UPDATE_DEBUG` untuk lihat response
  - Verify `files.php` accessible dan return JSON

### Update tidak terdeteksi
- **Check**: File .zip ada di directory `files/`
- **Check**: Nama file format `x.y.z.zip` (contoh: `1.0.3.zip`)
- **Check**: Version lebih tinggi dari current version
- **Check**: Version >= `1.0.1` (minimum version)

## Security Notes

1. **Authentication**: Uncomment auth check di `proxy_check_update.php` untuk production
2. **SSL**: Untuk remote server, pastikan SSL certificate valid atau disable verification
3. **File Validation**: Sistem hanya accept file dengan format `x.y.z.zip`
4. **Directory Permissions**: Ensure `update/files/` writable untuk upload

## API Response Format

### Success Response
```json
{
  "status": "update_available",
  "version": "1.0.3",
  "current": "1.0.2",
  "message": "Release notes here...",
  "download_url": "http://localhost/data/update/files/1.0.3.zip",
  "file_size": 1234567,
  "mode": "remote"
}
```

### Up-to-date Response
```json
{
  "status": "up_to_date",
  "version": "1.0.2",
  "current": "1.0.2",
  "message": "Sistem sudah menggunakan versi terbaru.",
  "download_url": "",
  "mode": "remote"
}
```

### Error Response
```json
{
  "error": true,
  "message": "Error description",
  "http_code": 403,
  "mode": "remote"
}
```
