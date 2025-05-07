#!/bin/bash

# Hata durumunda betiği durdur
set -e

# Commit ve push fonksiyonu
commit_and_push() {
    local file_path="$1"
    local commit_message="$2"

    if [ -z "$file_path" ] || [ -z "$commit_message" ]; then
        echo "Hata: Dosya yolu ve commit mesajı gereklidir."
        echo "Kullanım: ./commit.sh <dosya_yolu> \"<commit_mesajı>\""
        return 1
    fi

    echo "Dosya hazırlanıyor: $file_path"
    git add "$file_path"

    echo "Commit atılıyor: $commit_message"
    git commit -m "$commit_message"

    echo "Değişiklikler push ediliyor (main branch)..."
    git push origin main # Varsayılan olarak 'main' branch'e push eder. Gerekirse değiştirin.

    echo "Başarıyla push edildi: $file_path"
    echo "------------------------"
}

# Betiğe argüman olarak verilen dosya ve mesajı kullanarak fonksiyonu çağır
commit_and_push "$1" "$2" 