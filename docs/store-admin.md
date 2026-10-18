# Mağaza sahibinin yönetim paneli

Moda Store Admin, WordPress yönetim menüsüne **Mağaza yönetimi** panosu ekler.
Pano kendi başına ürün/sipariş verisi değiştirmez; mevcut WooCommerce
ekranlarına bağlantı sağlar. Her kart ve bağlantı uygun WordPress/WooCommerce
yeteneğiyle kontrol edilir. Bağlantıya doğrudan gidildiğinde hedef ekranın kendi
WordPress/WooCommerce yetki kontrolleri de geçerlidir.

## Kurulum

1. `wp-content/plugins/moda-store-admin` klasörünü WordPress sitesinin
   `wp-content/plugins/` dizinine kopyalayın.
2. **Eklentiler > Yüklü eklentiler** bölümünden **Moda Store Admin** eklentisini
   etkinleştirin. WooCommerce de etkin olmalıdır.
3. Yönetim menüsündeki **Mağaza yönetimi** panosuna gidin.

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

- Shop Manager: izinli ürün, sipariş, kupon ve rapor kartlarını görür; kullanıcı,
  eklenti ve site güvenlik ayarlarına erişemez.
- Ürün personeli (özelleştirilmiş rol): yalnızca ürün/kategori ekranları için
  gereken `edit_products` / `manage_product_terms` yetenekleri varsa o
  bağlantıları görür; sipariş/müşteri/kupon kartları görünmez.
- Customer: **Mağaza yönetimi** panosuna erişemez.
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
