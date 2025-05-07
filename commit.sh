#!/bin/bash

# Hata durumunda betiği durdur
set -e

# Commit ve push fonksiyonu
commit_and_push() {
    local file_path="$1"
    local commit_message="$2"

    echo "İşleniyor: $file_path"
    
    # Dosyanın var olup olmadığını kontrol et
    if [ ! -f "$file_path" ]; then
        echo "$file_path bulunamadı, atlanıyor."
        echo "------------------------"
        return
    fi

    git add "$file_path"

    # Sadece gerçekten değişiklik varsa commit at ve push et
    # `git diff --cached --quiet` komutu, eğer stage'de değişiklik yoksa 0 (başarılı) döner.
    # Değişiklik varsa 1 (başarısız) döner, bu yüzden `!` ile tersini kontrol ediyoruz.
    if ! git diff --cached --quiet -- "$file_path"; then
        echo "Commit atılıyor: $commit_message"
        git commit -m "$commit_message"
        
        echo "Değişiklikler push ediliyor (main branch): $file_path"
        git push origin main # Varsayılan olarak 'main' branch'ine push eder. Gerekirse değiştirin.
        
        echo "Başarıyla push edildi: $file_path"
    else
        echo "$file_path için commit atılacak değişiklik yok."
    fi
    echo "------------------------"
}

# Ana Proje Dosyaları
commit_and_push "public/index.php" "Ana uygulama giriş noktası güncellendi"
commit_and_push ".env.example" "Örnek çevre değişkenleri dosyası güncellendi"
commit_and_push "composer.json" "Composer bağımlılıkları güncellendi"
commit_and_push "composer.lock" "Composer lock dosyası güncellendi"
commit_and_push "README.md" "Proje dokümantasyonu (README) güncellendi"
commit_and_push "swagger-simple.json" "API dokümantasyonu (Swagger) güncellendi"
commit_and_push ".htrouter.php" "PHP dahili sunucu yönlendirici betiği güncellendi"

# Veritabanı Dosyaları
commit_and_push "database/schema.sql" "Veritabanı şeması güncellendi"
commit_and_push "database/data.sql" "Veritabanı başlangıç verileri güncellendi"

# Temel Altyapı Sınıfları (Core)
commit_and_push "app/Core/Database.php" "Veritabanı bağlantı sınıfı güncellendi"
commit_and_push "app/Core/Router.php" "URL yönlendirme sınıfı güncellendi"
commit_and_push "app/Core/JWTService.php" "JWT servis sınıfı güncellendi"
commit_and_push "app/Core/Response.php" "API yanıt sınıfı güncellendi"

# Rota Tanımları (Routes)
commit_and_push "app/Routes/api.php" "API rota tanımları güncellendi"

# Model Sınıfları (Models)
commit_and_push "app/Models/User.php" "Kullanıcı modeli güncellendi"
commit_and_push "app/Models/Product.php" "Ürün modeli güncellendi"
commit_and_push "app/Models/Cart.php" "Sepet modeli güncellendi"
commit_and_push "app/Models/Order.php" "Sipariş modeli güncellendi"

# Kontrolcü Sınıfları (Controllers)
commit_and_push "app/Controllers/UserController.php" "Kullanıcı kontrolcüsü güncellendi"
commit_and_push "app/Controllers/ProductController.php" "Ürün kontrolcüsü güncellendi"
commit_and_push "app/Controllers/CartController.php" "Sepet kontrolcüsü güncellendi"
commit_and_push "app/Controllers/OrderController.php" "Sipariş kontrolcüsü güncellendi"

# Bu betiğin kendisi
commit_and_push "commit.sh" "Commit betiği güncellendi"

echo "Tüm belirtilen dosyalar işlendi." 