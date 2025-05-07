İşte Cursor AI'da kullanabileceğin, PHP ile yazılacak ve PostgreSQL kullanan bir e-ticaret sitesi backend'i için gelişmiş, net ve amaca uygun bir prompt:

---

**🎯 Prompt:**

> **Sen bir yazılım geliştiricisisin. Aşağıdaki detaylara göre sade, okunabilir ve MVC mimarisine uygun bir e-ticaret sitesi için PHP backend API'si yazmanı istiyorum. Gereksiz kod kalabalığından kaçınılmalı. Güvenlik konusunda CSRF ve input sanitization gibi detaylar şu anda öncelikli değil.**
>
> **Genel gereksinimler:**
> - Dil: PHP (8.x tercih edilir)
> - Mimari: MVC
> - Veritabanı: PostgreSQL
> - ORM: PDO kullanılabilir (Laravel veya başka framework olmayacak)
> - Autoload: Composer
> - Ortam değişkenleri için `.env` dosyası kullanılmalı (örnek `.env.example` ile birlikte)
> - Token bazlı authentication için JWT kullanılmalı (örneğin `firebase/php-jwt` kütüphanesi)
> - Her API çıktısı JSON olmalı
> - Temel hata yönetimi uygulanmalı (örneğin `try-catch` blokları ve `ErrorResponse` sınıfı)
>
> **API modülleri:**
> 1. **Kullanıcı Modülü**
>    - POST `/api/register` → kullanıcı kaydı
>    - POST `/api/login` → giriş yapar ve JWT döner
>    - GET `/api/profile` → token ile kullanıcı profili döner
>
> 2. **Ürün Modülü**
>    - GET `/api/products` → tüm ürünleri listeler
>    - GET `/api/products/:id` → ürün detayını getirir
>    - POST `/api/products` → ürün oluşturur (sadece admin için)
>    - PUT `/api/products/:id` → ürün günceller
>    - DELETE `/api/products/:id` → ürün siler
>
> 3. **Sepet Modülü**
>    - POST `/api/cart/add` → sepete ürün ekler
>    - GET `/api/cart` → kullanıcıya ait sepeti döner
>    - DELETE `/api/cart/:id` → sepetten ürün çıkarır
>
> 4. **Sipariş Modülü**
>    - POST `/api/orders` → sipariş oluşturur
>    - GET `/api/orders` → kullanıcının siparişlerini listeler
>
> **Kod yapısı şu şekilde organize edilmeli:**
> ```
> /app
>   /Controllers
>   /Models
>   /Routes
>   /Core (Router, JWTService, DB sınıfı vb.)
> /public
>   index.php
> .env
> composer.json
> ```
>
> **İlk olarak şu adımları kodla:**
> - Basit bir router
> - `.env` dosyasını okuyacak yapı
> - JWT oluşturma ve doğrulama işlemleri
> - Kullanıcı kayıt/giriş işlemleri
> - Basit bir ürün listeleme endpoint’i
>
> **Kodlarda kısa yorumlar yaz ve sadece gerekli olanları yap.** Güvenlik konularında çok fazla detaya girme, token kontrolü yeterli. Başlayabilirsin.

---

İstersen bu promptu sadeleştirebilir ya da ek modüller (favori ürünler, kullanıcı rolleri, vs.) ile genişletebiliriz. Promptu Cursor'a verirken `codegen` veya `agent` modunu kullanarak test edebilirsin. Devam etmemi ister misin?