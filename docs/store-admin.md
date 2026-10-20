# Mağaza sahibinin yönetim paneli

Moda Store Admin, WordPress yönetim menüsüne **Moda Chicago** adında özel bir
yönetici panosu ekler. Menü ve panel yalnızca `manage_options` yetkisine sahip
site yöneticilerine açıktır. Erişim, WordPress'in kendi oturum açma ve parola
korumasını kullanır; tema/eklenti içinde ayrı, ortak bir panel parolası
saklanmaz.

Basit ürünleri eklentinin aranabilir yönetim ekranından ekleyip düzenleyebilir;
varyasyon, sipariş ve gelişmiş WooCommerce ayarlarını yerleşik ekranlarda
yönetebilirsiniz. Değişiklikler WooCommerce ürün API'siyle veritabanına
kaydedilir. Her işlem yetki, nonce ve sunucu tarafı doğrulamasıyla korunur.
`admin.html` içindeki tarayıcı demosu ve `localStorage` verileri canlı mağazanın
yönetim paneli değildir; bu veriler sunucuya aktarılmaz.

## Kurulum

1. `wp-content/plugins/moda-store-admin` klasörünü WordPress sitesinin
   `wp-content/plugins/` dizinine kopyalayın.
2. **Eklentiler > Yüklü eklentiler** bölümünden **Moda Store Admin** eklentisini
   etkinleştirin. WooCommerce de etkin olmalıdır.
3. WordPress yönetim menüsündeki **Moda Chicago** panosuna gidin.

ZIP ile güncellemek için yeni `moda-store-admin.zip` paketini **Eklentiler >
Yeni eklenti ekle > Eklenti yükle** bölümünden yükleyin. WordPress eski sürümü
değiştirme seçeneği sunarsa değişikliği onaylayın; sunmuyorsa yedek aldıktan
sonra mevcut eklenti klasörünü FTP/hosting Dosya Yöneticisi üzerinden
güncelleyin. Eklenti dosyalarını değiştirirken `wp-config.php` veya
`wp-content/uploads` içeriğini silmeyin.

Özel panelde yayındaki/taslak ürün ve sipariş sayısı, aranabilir ürün listesi,
basit ve beden/renk varyasyonlu ürün formu, taslak/yayın durumu, fiyat, SKU,
görsel, video, kategori ve stok alanları ile hızlı sipariş/mağaza ayarları
bağlantıları bulunur. Beden ve renk seçenekleri virgülle ayrılarak girilir;
WooCommerce her kombinasyon için gerçek varyasyon oluşturur. Varyasyon fiyatı
ortaktır ve stok ürün düzeyinde paylaşılır. Başka varyasyon alanları veya farklı
varyasyon fiyat/stokları WooCommerce'in ayrıntılı düzenleyicisinde yönetilir.
Ürün videosu MP4/WebM olarak yüklenebilir; mevcut video formdan izlenip
kaldırılabilir ve ürün sayfasının görsel galerisinde oynatılır. Görsel ve video
yüklemeleri WordPress/hosting dosya boyutu sınırına tabidir; panel geçerli
yükleme sınırını formda gösterir. Tema ürün kartlarında 720 px, ürün detayında
1200 px genişliğe kadar görsel boyutları kullanır. Silme işlemi ürünü doğrudan
kalıcı silmek yerine çöp kutusuna taşır. Ürün sayısı sıfırsa panel ödeme
etkinleştirmeden önce ürün ve teslimat bilgilerini hazırlamanızı hatırlatır.

Eklenti WP-CLI ile kurulabiliyorsa:

```sh
wp plugin activate moda-store-admin
```

WP-CLI kullanırken komutu doğru WordPress kurulum dizininde çalıştırın.

## Yönetim ekranları

| İş | Yerleşik yönetim ekranı |
|---|---|
| Ürün ekleme/düzenleme/silme | Ürünler > Tüm ürünler; ürün düzenleyicide varyasyonlar |
| Varyasyon, beden/renk, SKU ve stok | Ürün düzenleyicide Varyasyonlar sekmesi |
| Toplu ürün düzenleme | Ürün listesindeki satır seçimleri ve Toplu işlemler |
| CSV içe aktarma / mevcut ürünleri güncelleme / dışa aktarma | Ürünler > Tüm ürünler > İçe aktar / Dışa aktar |
| Kategoriler ve ürün etiketleri | Ürünler > Kategoriler / Etiketler |
| Sipariş, durum, iade | WooCommerce > Siparişler |
| Müşteri | WooCommerce > Müşteriler |
| Kupon | Pazarlama > Kuponlar veya WooCommerce > Kuponlar; menü WooCommerce sürümüne göre değişebilir |
| Kampanya bannerları | Görünüm > Özelleştir > Ana sayfa hero alanı |
| Satış ve stok analitiği | WooCommerce > Analizler |

## CSV ve toplu güncelleme kontrol listesi

WooCommerce'in yerleşik CSV aracı yeni ürün eklemeyi, dışa aktarmayı ve SKU/ID
ile eşleştirilen mevcut ürünleri güncellemeyi destekler. Varyasyonlar, nitelikler,
fiyat, indirim, SKU ve stok sütunları CSV şablonuna göre eşleştirilebilir.

1. Canlıdan önce staging veritabanı/yedeği alın.
2. Ürünler > Tüm ürünler > Dışa aktar ile güncel kataloğun yedeğini alın.
3. UTF-8 kodlamalı CSV kullanın; SKU'ların benzersizliğini ve varyasyon
   ebeveyn-bağlantılarını kontrol edin.
4. İçe aktarmada yalnızca mevcut ürün güncellenecekse **Update existing
   products** seçeneğini kullanın; eşleşmenin SKU veya ürün kimliğiyle doğru
   olduğundan emin olun.
5. Sütun eşlemesini ve ürün/varyasyon satırlarını önizleyin; önce az sayıda
   test ürünüyle çalıştırın.
6. Stok, fiyat, indirim ve varyasyon seçimini ürün sayfasında kontrol edin;
   sonuç doğruysa tam dosyayı içe aktarın.
7. İçe aktarma sürerken sayfayı yenilemeyin/kapatmayın; tamamlanma raporunu ve
   hata satırlarını kaydedin.

CSV dosyaları toplu ve geniş kapsamlı değişiklik yapabildiğinden mağaza sahibi
veya yetkilendirilmiş ürün yöneticisi dışında yükleme izni vermeyin.

## Personel rolleri

- **Yönetici (Administrator):** Eklentiler, site ayarları, kullanıcı rolleri ve
  tema gibi tüm site yönetimi görevleri. Bu rolü yalnızca az sayıda güvenilir
  kişiye verin.
- **Mağaza yöneticisi (Shop Manager):** WooCommerce ürün, sipariş, kupon,
  müşteri ve raporları yönetebilir; tam WordPress yöneticisi değildir. Ancak
  WooCommerce ayarlarına erişimi geniştir.
- **Müşteri (Customer):** Kendi hesabı ve siparişleriyle sınırlıdır; yönetim
  paneli mağaza bağlantıları göstermez.

Shop Manager rolü tema görünümünü değiştirme (`edit_theme_options`) yetkisi
almadığından kampanya/hero düzenleme kartını görmeyebilir. Tema ve yasal/site
ayarlarını yalnızca yönetici yönetsin. Personel için özel/azaltılmış rol
gerekiyorsa rol yetkilerini ayrıca denetleyen, bakımı süren bir rol eklentisi
kullanıp staging'de rol bazında test edin; bu yardımcı eklenti rol veya
capability eklemez/değiştirmez.

## Yetki testi

Staging'de ayrı test kullanıcılarıyla doğrulayın:

- Administrator: WordPress hesabı ve parolasıyla oturum açtıktan sonra
  **Moda Chicago** panosunu görür; ürün/sipariş işlemleri WooCommerce'in yerel
  ekranlarına gider.
- Shop Manager: WooCommerce'in kendi yetki kurallarıyla izinli mağaza
  ekranlarını kullanabilir, ancak `manage_options` yetkisi olmadığı için özel
  **Moda Chicago** panosunu göremez ve doğrudan URL ile açamaz.
- Ürün personeli (özelleştirilmiş rol): yalnızca ürün/kategori ekranları için
  gereken `edit_products` / `manage_product_terms` yetenekleri varsa o
  bağlantıları görür; sipariş/müşteri/kupon kartları görünmez.
- Customer: **Moda Chicago** panosuna erişemez.
- Yetkisiz kullanıcı, panonun URL'sini elle açmayı dener: WordPress erişimi
  reddeder. Her hedef WooCommerce ekranında da doğrudan erişim testi yapın.
- Görsel personeli: `edit_theme_options` verilmemişse kampanya ayarlarına
  erişemez; verilirse bu yetkinin tema/site ayarlarına daha geniş erişim
  sağlayabileceğini kabul edip rolü sınırlayın.

Bu eklenti salt-okunur bir bağlantı panelidir; veri değiştiren işlemleri
WooCommerce'in kendi nonce, form doğrulaması ve yetki denetimi korur.

## Kaynak

- [WooCommerce ürün CSV içe/dışa aktarma ve toplu güncelleme](https://woocommerce.com/document/product-csv-importer-exporter/)
- [WooCommerce kullanıcı rolleri ve yetkiler](https://woocommerce.com/document/roles-capabilities/)
