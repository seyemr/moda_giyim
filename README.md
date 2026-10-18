# Moda Chicago WordPress teması

Premium giyim mağazası için hazırlanmış, mobil uyumlu bir WordPress tema
başlangıcıdır. Ürün listeleri, kategoriler, fiyatlar, indirimler, sepet/hesap
bağlantıları ve ürün satın alma bağlantıları WooCommerce verilerini kullanır.
Örnek sipariş, ödeme işleme, canlı sağlayıcı bilgileri veya kargo entegrasyonu
eklenmemiştir.

## PHP kullanmadan statik demo önizlemesi

Proje kökündeki `index.html`, PHP/WordPress olmadan tarayıcıda açılabilen
Moda Chicago'nun görsel dili Chicago şehir bayrağının gök mavisi ve kırmızı
vurgularını, çelik tonlarını, güçlü tipografiyi ve şehir mimarisinden esinlenen
grafik çizgileri kullanır. Aynı tasarım yaklaşımı WordPress temasına ve yönetim
demo paneline de uygulanmıştır. Statik vitrinde ürün kartları, kategori kutuları,
filtreler ve yönetim kartları daha yumuşak yüzeyler, belirgin odak durumları ve
dar ekranlara göre ayarlanan boşluklarla sunulur.

HTML/CSS/JavaScript demo mağazasıdır. Örnek katalogda arama, kategori/fiyat/stok
filtreleri, sıralama, favoriler ve bu tarayıcıda saklanan demo sepeti çalışır.
JavaScript veya tarayıcı depolaması kısıtlıysa sepet kalıcı olmayabilir.
Ürün görseline tıklayınca ürün adı, kategori, fiyat, renk, beden seçenekleri ve
demo stok bilgisini gösteren ürün ayrıntı penceresi açılır. Yönetim demosunda
ürüne renk varyantı ekleyebilir, her renk için altı adede kadar farklı açıdan
fotoğraf yükleyebilir ve ürün penceresindeki galeriyle fotoğraflar arasında
geçiş yapabilirsin. Her renk varyantının demo stoğu ayrı tutulur.
Yüklenen fotoğraflar en fazla 1200 px uzun kenara ve 350 KB dosya boyutuna
küçültülür; kaynak dosya sınırı 8 MB'tır. Katalog tarayıcı depolamasında kaldığı
için kullanılabilir toplam alan tarayıcıya göre sınırlıdır.
`admin.html` ürün kataloğunun yanında marka/SEO bilgilerini, duyuru ve menü
metinlerini, ana sayfa hero/kategori/hikâye/bilgi alanlarını, bölüm
görünürlüğünü, footer metinlerini ve marka renklerini düzenler. Hero, hikâye ve
kategori görselleri eklenebilir. İçerik ve tasarım ayarları, katalog ve demo stok
aynı tarayıcıdaki `localStorage` alanında tutulur. Metin, renk veya görsel
değişiklikleri açık mağaza sekmesine aktarılır; sekme kapalıysa mağaza yeniden
açıldığında yüklenir. Site editöründeki **Kaydetmeden önce önizle** düğmesi,
formdaki taslak ayarları ayrı bir mağaza penceresinde gösterir; bu önizleme
değişiklikleri kaydetmez. Önizlemeyi kapatıp düzenlemeye devam edebilir ve
değişiklikleri yalnızca kaydetme düğmesiyle kalıcı ayarlara aktarabilirsin.
Bu panelin oturum açma, yetkilendirme veya güvenli veri saklama özelliği yoktur.
Mağaza sayfasının altındaki **Yönetim paneline giriş** bağlantısı `admin.html`
sayfasını açar. Bu, herkese açık demo bağlantısıdır; gerçek yönetim paneli
olarak veya erişim güvenliği varmış gibi kullanılmamalıdır.
Yönetim panelinden JPG, PNG veya WebP görseller seçilebilir; ürün görselleri
en fazla 350 KB, site görselleri en fazla 250 KB olarak tarayıcıda küçültülür.
Kaynak dosya sınırı 8 MB, uzun kenar sınırı 1200 pikseldir. Tarayıcı depolama
sınırları nedeniyle küçük görseller kullanın.
Tarayıcıda bozuk veya eski bir ürün kaydı varsa katalog gösterimi için geçersiz
kayıtlar ayıklanır; ürün ve site ayarları forma uygulanırken renk, beden ve
görsel alanları güvenli varsayılanlarla normalize edilir.

En kolay yerel sunum:

```sh
python3 -m http.server 8000
```

Komutu proje kök klasöründe çalıştırıp `http://localhost:8000` adresini açın.
Python kurulu değilse `index.html` dosyasını doğrudan tarayıcıya da
açabilirsiniz; yerel sunucu tercih edilir.

Bu statik demo gerçek üyelik, sipariş, ödeme, veritabanı stoku, kupon,
işlemsel e-posta veya kargo işlemez. Ödeme düğmesi yalnızca bunu belirten demo
mesajı gösterir. Gerçek satış ve güvenli sunucu tarafı doğrulama için alttaki
WordPress/WooCommerce kurulumu veya ayrı bir commerce backend'i gerekir.

## Gereksinimler

- WordPress 6.4 veya üzeri
- PHP 8.0 veya üzeri
- Mağaza, katalog, sepet, hesap ve ödeme işlevleri için WooCommerce

WooCommerce olmadan da tema etkinleştirilebilir; mağaza bölümleri çalışıyormuş
gibi gösterilmek yerine kurulum uyarısı sunar.

## Bu çalışma alanındaki WordPress kurulumu

WordPress çekirdek dosyaları resmi WordPress indirme adresinden bu klasöre
kurulmuştur. Şu an çekirdek sürümü 7.1.3'tür; proje teması ve mağaza yönetim
eklentisi mevcut `wp-content` altında korunmuştur.

Bu henüz çalışan bir WordPress sitesi değildir: çalışma ortamında PHP ve MySQL
çalışma zamanı bulunmadığı için `wp-config.php` oluşturulmamış, veritabanı
kurulmamış ve ilk kurulum sihirbazı tamamlanmamıştır. Bu nedenle şu anda
çalışan bir `localhost` URL'si yoktur. Yerel bir PHP + MySQL/MariaDB ortamı
kurup WordPress kurulum sihirbazını tamamlayın; ardından WooCommerce'i kurup
etkinleştirin. Yerel ortamı farklı bir WordPress kök klasöründe oluşturursanız,
bu projedeki tema ve eklenti klasörlerini o sitenin `wp-content/themes/` ve
`wp-content/plugins/` klasörlerine kopyalayın.

## Kurulum

1. `wp-content/themes/moda-atelier` klasörünü WordPress kurulumundaki
   `wp-content/themes/` dizinine kopyalayın veya klasörü ZIP yapıp
   **Görünüm > Temalar > Yeni tema ekle > Tema yükle** bölümünden yükleyin.
2. WooCommerce’i WordPress eklenti dizininden kurup etkinleştirin.
3. **Görünüm > Temalar** bölümünden **Moda Chicago** temasını etkinleştirin.
4. Site adını **Moda Chicago** ve logoyu **Görünüm > Özelleştir > Site Kimliği**
   bölümünden ayarlayın.
5. Hero ve iki kampanya alanının metinlerini, görsellerini ve hedef sayfalarını
   **Görünüm > Özelleştir > Ana sayfa hero alanı** bölümünden düzenleyin.
   Aynı bölümde gerçek bülten formu/sağlayıcı bağlantısı tanımlandığında bülten
   abonelik butonu görünür.
6. **Görünüm > Menüler** bölümünde kategori öğelerini alt menü olarak
   hiyerarşik düzenleyip ana menüye atayın; alt menüler masaüstünde mega menü,
   mobilde açılır kategori listesi olarak görünür.
7. WooCommerce’de gerçek ürün ve kategorileri ekleyin. Ana sayfa yeni ürünleri,
   satış verisine göre çok satanları ve indirimli ürünleri otomatik listeler.
8. Canlıya almadan önce staging ortamında masaüstü, tablet ve mobil testlerini
   yapın.

## Dahil olanlar

- Duyarlı ana sayfa ve mobil menü.
- WooCommerce ürün kategorileri, yeni ürünler, çok satanlar ve indirimli ürünler.
- Ürün arşivinde WooCommerce katalog sıralaması ve GET tabanlı kategori, beden,
  renk, marka, stok durumu ve min./maks. fiyat filtreleri. Beden/renk filtreleri
  WooCommerce ürün özelliklerinde `Beden/Size` ve `Renk/Color` olarak tanımlanan
  global özellikleri; marka filtresi WooCommerce Brands veya `brand`/`marka`
  olarak kayıtlı ürün taksonomisini kullanır. Filtrelerin görünmesi için ilgili
  global özelliklerin/marka taksonomisinin ve terimlerin mağazada oluşturulmuş
  olması gerekir.
- Mobil arşiv filtre paneli; filtreler sayfa yüklemesiyle uygulanır, URL'de
  paylaşılabilir ve JavaScript kapalıyken form görünür kalır.
- WooCommerce araması, ürün, sepet ve hesap bağlantıları.
- WordPress özel logo, menü konumları ve düzenlenebilir hero alanı.
- Özelleştirilebilir hero/kampanya görselleri ve WooCommerce kategori görselleri.
- Ürün kartı favorileri bu tarayıcıda saklanır; üyeler arasında/cihazlar arasında
  eşitlenmez.
- Ürün varyasyon stok miktarı ve SKU takibi WooCommerce ürün/varyasyon
  ayarlarından yönetilir. Filtre stok durumunu WooCommerce ürün stok durumuna
  göre uygular; varyasyonların kendi stok yönetimi WooCommerce tarafından
  yürütülür.
- Gerçek bülten kayıtları için mevcut abonelik sayfası/servisi bağlantısını
  Customizer'da tanımlayın. Bağlantı olmadan kayıt formu veya sahte abonelik
  sunulmaz; izin, abonelikten çıkma ve veri işleme sağlayıcı tarafında
  yapılandırılmalıdır.
- Klavye odak görünümü ve hareket azaltma tercihi desteği.

## Henüz yapılandırılmayanlar

Ödeme sağlayıcısı ve API bilgileri, canlı/test ödeme hesapları, kargo firması
sözleşmeleri, işletmeye özel yasal bilgiler, pazarlama e-postası izinleri ve
yapay zekâ servis anahtarları ayrıca sağlanıp yapılandırılmalıdır. Gizli bilgileri
tema kaynak koduna eklemeyin. Ödeme sağlayıcılarını desteklenen WooCommerce
ayarlarından yapılandırın ve canlı işlem öncesinde sandbox akışını test edin.

Sepet/ödeme ayarları, Türkiye sağlayıcı karşılaştırması ve staging test planı için
[WooCommerce sepet ve ödeme kurulum rehberine](./docs/woocommerce-checkout.md)
bakın.

Mağaza personeli rolleri, CSV ürün yönetimi ve yetki kontrollü yönetim panosu
kurulumu için [mağaza yönetim rehberine](./docs/store-admin.md) bakın.

Yayına çıkış öncesi uçtan uca senaryolar, beklenen sonuçlar, hata giderme ve
yayın kabul kapısı için [yayın öncesi test planını](./docs/prelaunch-test-plan.md)
izleyin.
# moda_giyim
