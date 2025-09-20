# Bayilik Sistemi

Bu proje PHP ve SQLite kullanılarak hazırlanmış yönetici/bayi panelli bir bayilik otomasyon sistemidir. Laravel gibi ek bağımlılıklar gerektirmez; PHP 8+ ve SQLite eklentisi yeterlidir.

## Özellikler

- Yönetici ve bayi rolleri
- Ürün yönetimi (kategori, stok, görsel, bayi bazlı fiyatlandırma, CSV ile toplu ekleme)
- Sipariş yönetimi (stok ve bakiye kontrolleri, özel fiyatlar, negatif bakiye limiti)
- Talepli ürün modülü (ticket benzeri mesajlaşma, dosya paylaşımı)
- Destek sistemi ve ayrı ödeme bildirimi destek hattı
- Yönetici tarafından bayilere toplu bildirim gönderme
- Bayi bakiyesi ve kredi limiti takibi, işlem geçmişi
- Telegram bildirim entegrasyonu (bot token ve chat id girildiğinde)
- Karanlık/açık tema, mobil uyumlu arayüz

## Kurulum

1. Depoyu klonlayın ve proje klasörüne geçin.
2. PHP yerleşik sunucusuyla çalıştırmak için:
   ```bash
   php -S localhost:8000
   ```
3. Tarayıcıda `http://localhost:8000/index.php` adresini açın.
4. İlk yönetici girişi için varsayılan hesap: `admin@bayi.local` / `admin123`
5. Güvenliğiniz için ilk girişten sonra yeni bir yönetici hesabı oluşturup şifreyi değiştirin.

## Yapılandırma

- Telegram bildirimleri için terminalinizde aşağıdaki değişkenleri tanımlayın veya sunucu ortamınıza ekleyin:
  ```bash
  export TELEGRAM_BOT_TOKEN="bot_tokeniniz"
  export TELEGRAM_ADMIN_CHAT_ID="admin_chat_id"
  ```
- Dosya yüklemeleri `uploads/` klasöründe saklanır.
- Veritabanı dosyası `database/database.sqlite` olarak otomatik oluşturulur.

## CSV Formatı

Toplu ürün eklemede CSV satırı şu sırayla olmalıdır:
```
Ürün Adı;Kategori;Açıklama;Fiyat;Stok
```

## Notlar

- Sistem, PHP oturumlarını kullanır. HTTPS altında çalıştırmanız önerilir.
- Yüklenen dosyalar için maksimum boyut 5 MB olarak sınırlandırılmıştır.
- WordPress eklenti/tema kategorisindeki ürünlerde sipariş formu panel bilgilerini zorunlu kılar.

