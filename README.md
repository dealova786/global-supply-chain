# Global Supply Chain Risk Intelligence Platform

Global Supply Chain Risk Intelligence Platform adalah website monitoring risiko rantai pasok global berbasis multi-API dan analitik data. Aplikasi ini digunakan untuk memantau risiko suatu negara berdasarkan cuaca, indikator ekonomi, nilai tukar, berita, sentimen, pelabuhan, dan skor risiko total.

## Fitur Utama

- **Country Intelligence Dashboard** 
  Menampilkan informasi negara, cuaca, ekonomi, currency, news intelligence, dan risk score.

- **Risk Score Dashboard**  
  Menampilkan skor risiko negara berdasarkan weather risk, inflation risk, news risk, dan currency risk.

- **Weather Map**  
  Menampilkan peta cuaca global menggunakan Leaflet.js.

- **Currency Risk Dashboard**  
  Menampilkan nilai tukar mata uang dan risiko kurs.

- **News Intelligence**  
  Menampilkan berita negara dan analisis sentimen berbasis positive words dan negative words.

- **Port Dashboard**  
  Menampilkan data pelabuhan berdasarkan negara.

- **Country Comparison**  
  Membandingkan dua negara berdasarkan indikator risiko.

- **Watchlist**  
  Menyimpan daftar negara yang ingin dipantau user.

- **Admin Dashboard**  
  Admin dapat mengelola user, artikel, port, sentiment words, dan sinkronisasi API.

## Teknologi yang Digunakan

| Bagian | Teknologi |
|---|---|
| Backend | Laravel |
| Database | MySQL |
| Frontend | Bootstrap, JavaScript |
| Chart | Chart.js |
| Map | Leaflet.js |
| Auth | Laravel Breeze |
| API Negara | REST Countries API |
| API Cuaca | Open-Meteo API |
| API Ekonomi | World Bank API |
| API Berita | GNews API |
| API Pelabuhan | GeoNames API |
| API Kurs | Currency API |

---

## Kebutuhan Sistem

Pastikan perangkat sudah memiliki:

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm
- MySQL
- Git
- XAMPP/Laragon atau server lokal sejenis

---

## Cara Menjalankan Website

### 1. Masuk ke Folder Project

```bash
cd supply-chain-risk
```

Contoh:

```bash
cd C:\Users\Acer\supply-chain-risk
```

---

### 2. Install Dependency

Install dependency Laravel:

```bash
composer install
```

Install dependency frontend:

```bash
npm install
npm run build
```

Jika ingin menjalankan frontend mode development:

```bash
npm run dev
```

---

### 3. Buat File Environment

Copy file `.env.example` menjadi `.env`:

```bash
copy .env.example .env
```

---

### 4. Atur Konfigurasi `.env`

Sesuaikan konfigurasi berikut pada file `.env`:

```env
APP_NAME="Supply Chain Risk"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supply_chain_risk
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

REST_COUNTRIES_BASE_URL=https://api.restcountries.com/countries/v5
WORLD_BANK_BASE_URL=https://api.worldbank.org/v2

GNEWS_API_KEY=your_gnews_api_key
GNEWS_BASE_URL=https://gnews.io/api/v4

GEONAMES_USERNAME=your_geonames_username
GEONAMES_BASE_URL=https://secure.geonames.org
```

Catatan:  
API key disimpan pada file `.env` dan tidak perlu dibagikan secara publik.

---

### 5. Buat Database

Buat database MySQL dengan nama:

```text
supply_chain_risk
```

---

### 6. Generate App Key

```bash
php artisan key:generate
```

---

### 7. Jalankan Migration

```bash
php artisan migrate
```

---

### 8. Jalankan Seeder

Seeder digunakan untuk mengisi data awal, seperti positive words dan negative words.

```bash
php artisan db:seed
```

Atau jalankan seeder tertentu:

```bash
php artisan db:seed --class=PositiveWordSeeder
php artisan db:seed --class=NegativeWordSeeder
```

---

### 9. Buat Akun Admin

Jalankan perintah berikut:

```bash
php artisan tinker --execute="App\Models\User::updateOrCreate(['email'=>'admin@gmail.com'], ['name'=>'Admin', 'password'=>Illuminate\Support\Facades\Hash::make('password123'), 'role'=>'admin', 'email_verified_at'=>now()]);"
```

Akun admin default:

```text
Email    : admin@gmail.com
Password : password123
```

---

### 10. Jalankan Website

```bash
php artisan serve
```

Buka di browser:

```text
http://127.0.0.1:8000
```

---

## Alur Pengambilan Data API

Aplikasi menggunakan beberapa API eksternal. Alur pengambilan data secara umum:

```text
User/Admin memilih negara
        ↓
Sistem memanggil service Laravel
        ↓
Service mengambil data dari API eksternal
        ↓
Data diproses dan dihitung nilai risikonya
        ↓
Data disimpan ke database sebagai cache
        ↓
Dashboard menampilkan hasil analisis
```

Data disimpan sebagai cache agar website tetap dapat menampilkan data terakhir meskipun API mengalami timeout, limit, atau koneksi gagal.

---

## Sinkronisasi dan Pengambilan Data

Setelah login sebagai admin, lakukan beberapa proses berikut:

### 1. Sync Countries API

```text
Admin Dashboard → Sync Countries API
```

Fitur ini mengambil data negara dari REST Countries API dan menyimpannya ke tabel `countries`.

---

### 2. Analyze Country Dashboard

```text
Country Dashboard → pilih negara → Analyze
```

Fitur ini mengambil dan menampilkan data:

- cuaca dari Open-Meteo API
- ekonomi dari World Bank API
- currency
- news intelligence
- risk score

---

### 3. Analyze Currency

```text
Currency Dashboard → pilih negara → Analyze
```

Fitur ini mengambil data nilai tukar mata uang dan menghitung currency risk.

---

### 4. Analyze News

```text
News Intelligence → pilih negara → Analyze
```

Fitur ini mengambil berita dari GNews API dan melakukan analisis sentimen.

Hasil analisis sentimen:

| Sentiment | News Risk |
|---|---|
| Positive | 20 |
| Neutral | 50 |
| Negative | 60–80 |

---

### 5. Sync Ports API

```text
Admin → Manage Ports → pilih negara → Sync Ports API
```

Fitur ini mengambil data pelabuhan dari GeoNames API.

---

## Risk Score

Risk score dihitung dari beberapa indikator:

- Weather Risk
- Inflation Risk
- News Risk
- Currency Risk

Kategori risiko:

| Risk Score | Kategori |
|---|---|
| 0–39 | Low |
| 40–69 | Medium |
| 70–100 | High |

---

## Tabel Database Utama

| Tabel | Fungsi |
|---|---|
| users | Data user dan admin |
| countries | Data negara |
| weather_cache | Cache data cuaca |
| economic_indicators | Data ekonomi |
| currency_rates | Data nilai tukar |
| news_cache | Data berita dan sentimen |
| risk_scores | Skor risiko |
| ports | Data pelabuhan |
| articles | Data artikel |
| positive_words | Kata positif |
| negative_words | Kata negatif |
| watchlists | Data watchlist user |

---

## Route Utama

| Route | Fungsi |
|---|---|
| `/login` | Login |
| `/register` | Register user |
| `/dashboard` | Dashboard utama |
| `/country-dashboard` | Country Intelligence Dashboard |
| `/risk-scores` | Risk Score Dashboard |
| `/weather-map` | Weather Map |
| `/currency-dashboard` | Currency Dashboard |
| `/news-intelligence` | News Intelligence |
| `/ports` | Port Dashboard |
| `/compare-countries` | Country Comparison |
| `/watchlists` | Watchlist |
| `/admin/users` | Admin Users |
| `/admin/articles` | Admin Articles |
| `/admin/ports` | Admin Manage Ports |
| `/admin/sentiment-words` | Admin Sentiment Words |

---

## Perintah Artisan yang Sering Digunakan

Membersihkan cache aplikasi:

```bash
php artisan optimize:clear
```

Membersihkan config cache:

```bash
php artisan config:clear
```

Membersihkan route cache:

```bash
php artisan route:clear
```

Menjalankan migration:

```bash
php artisan migrate
```

Menjalankan server lokal:

```bash
php artisan serve
```

## Penutup

Website ini dibuat untuk membantu monitoring risiko rantai pasok global melalui integrasi berbagai API dan dashboard analitik. Dengan fitur country dashboard, weather map, currency risk, news intelligence, port dashboard, watchlist, dan risk score, pengguna dapat melihat gambaran risiko suatu negara secara lebih cepat dan terstruktur.
