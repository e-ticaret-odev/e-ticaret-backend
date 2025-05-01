# E-Ticaret Backend API

Bu proje, PHP 8.x ve PostgreSQL kullanılarak geliştirilen bir e-ticaret backend API'sidir. MVC mimarisine uygun olarak tasarlanmıştır.

## Özellikler

- Kullanıcı yönetimi (kayıt, giriş, profil görüntüleme)
- Ürün yönetimi (listeleme, detay görüntüleme, oluşturma, güncelleme, silme)
- Sepet yönetimi (ekleme, listeleme, güncelleme, silme)
- Sipariş yönetimi (oluşturma, listeleme, detay görüntüleme)
- JWT tabanlı kimlik doğrulama
- PostgreSQL veritabanı entegrasyonu
- JSON API yanıtları

## Gereksinimler

- PHP 8.0+
- PostgreSQL 12+
- Composer

## Kurulum

1. Projeyi klonlayın:
```
git clone https://github.com/kullanici/e-ticaret-backend.git
cd e-ticaret-backend
```

2. Bağımlılıkları yükleyin:
```
composer install
```

3. `.env` dosyasını düzenleyin:
```
cp .env.example .env
```
Veritabanı bağlantı bilgilerinizi ve JWT anahtarını güncelleyin.

4. Veritabanını oluşturun:
```
psql -U postgres -f database/schema.sql
```

5. Web sunucusunu başlatın:
```
cd public
php -S localhost:8000
```

## API Endpoints

### Kullanıcı Modülü
- `POST /api/register` - Kullanıcı kaydı
- `POST /api/login` - Giriş yapar ve JWT döner
- `GET /api/profile` - Token ile kullanıcı profili döner

### Ürün Modülü
- `GET /api/products` - Tüm ürünleri listeler
- `GET /api/products/:id` - Ürün detayını getirir
- `POST /api/products` - Ürün oluşturur (sadece admin için)
- `PUT /api/products/:id` - Ürün günceller
- `DELETE /api/products/:id` - Ürün siler

### Sepet Modülü
- `GET /api/cart` - Kullanıcıya ait sepeti döner
- `POST /api/cart/add` - Sepete ürün ekler
- `PUT /api/cart/:id` - Sepetteki ürün miktarını günceller
- `DELETE /api/cart/:id` - Sepetten ürün çıkarır
- `DELETE /api/cart` - Sepeti temizler

### Sipariş Modülü
- `POST /api/orders` - Sipariş oluşturur
- `GET /api/orders` - Kullanıcının siparişlerini listeler
- `GET /api/orders/:id` - Sipariş detayını getirir
- `PUT /api/orders/:id/status` - Sipariş durumunu günceller (sadece admin için)

## Örnek İstekler

### Kullanıcı Kaydı
```
POST /api/register
Content-Type: application/json

{
  "name": "Test Kullanıcı",
  "email": "test@example.com",
  "password": "123456"
}
```

### Giriş Yapma
```
POST /api/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "123456"
}
```

### Ürün Listeleme
```
GET /api/products
Authorization: Bearer your_jwt_token
```

### Sepete Ürün Ekleme
```
POST /api/cart/add
Authorization: Bearer your_jwt_token
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 2
}
```

### Sipariş Oluşturma
```
POST /api/orders
Authorization: Bearer your_jwt_token
```

## Lisans

MIT