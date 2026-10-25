# Moda Chicago erkek giyim mağazası

https://www.modamchicago.com/

Modern erkek giyim mağazası için hazırlanmış, mobil uyumlu bir WordPress tema
başlangıcıdır. Ürün listeleri, kategoriler, fiyatlar, indirimler, sepet/hesap
bağlantıları ve ürün satın alma bağlantıları WooCommerce verilerini kullanır.
Örnek sipariş, ödeme işleme, canlı sağlayıcı bilgileri veya kargo entegrasyonu
eklenmemiştir.

## Gerçek mağaza için WordPress sürümü

Gerçek ürün, sepet, sipariş ve ödeme akışının temeli WordPress ve WooCommerce
sürümüdür. Proje kökündeki `index.html` ve `admin.html`, yalnızca yerel
JavaScript demosudur; canlı mağazaya dağıtmayın. WordPress çekirdeğini, bu
projedeki `wp-admin`/`wp-includes` kopyalarını veya örnek/demo sayfalarını
barındırma hesabınıza yüklemek yerine yalnızca tema ve yönetim eklentisi ZIP
paketlerini kullanın.

Tema ve eklenti için kurulabilir ZIP dosyalarını oluşturun:

```sh
sh scripts/package-wordpress-release.sh
```

Paketler `dist/moda-atelier.zip`, `dist/moda-store-admin.zip`,
`dist/moda-pwa.zip`, `dist/moda-checkout-acceptance.zip`,
`dist/moda-support.zip`, `dist/moda-commerce-tools.zip` ve `dist/moda-instagram-top.zip` konumlarında oluşur.
WordPress'te **Görünüm > Temalar > Yeni tema ekle > Tema yükle** bölümünden tema
ZIP'ini; **Eklentiler > Yeni eklenti ekle > Eklenti yükle** bölümünden ihtiyaç
duyulan eklenti ZIP'lerini yükleyin. Ardından WooCommerce'i WordPress eklenti
dizininden kurup etkinleştirin. Checkout kabul eklentisi WooCommerce gerektirir.
`dist/` içeriği dağıtım çıktısıdır; kaynak kontrolüne eklenmemelidir.

`moda-support` eklentisi etkinleştirilince **Sıkça Sorulan Sorular**
(`/sikca-sorulan-sorular/`) ve **Destek ve Talep Formu** (`/destek-talebi/`)
sayfalarını oluşturur. Form; talep türü, iletişim bilgileri, sipariş numarası ve
en fazla 3 görsel (JPG/PNG/WebP, 5 MB) kabul eder. Talepler yönetim panelinde
**Destek Talepleri** altında saklanır, yöneticiye e-postayla bildirilir; talep
kalıcı silindiğinde görselleri de silinir. SSS içeriği `moda_support_faqs`
filtresiyle özelleştirilebilir.

`moda-commerce-tools` WooCommerce mağaza araçları; müşteri hesabında sunucuya
kaydedilen favorileri, ürün soru-cevap moderasyonunu, e-posta doğrulamalı stok
ve hedef fiyat bildirimlerini, sipariş hesabındaki kargo takip alanlarını ve
hesabından açıkça izin veren giriş yapmış müşteriler için her terk edilmiş
sepet başına tek hatırlatmayı sunar.
Destek talepleri **Hesabım > Destek taleplerim** bölümünde müşteriye özel
gösterilir; ürün soruları mağaza yöneticisinin yanıtlaması için moderasyona
alınır. Ürün bildirimi ve soru formlarında e-posta kullanımı için açık onay
alınır; form gönderimleri temel bot önlemleriyle korunur. Beden rehberi
**WooCommerce > Moda Mağaza Araçları** bölümünden hazırlanır; doğrulanmış
ölçüler girilene kadar ürün sayfalarında tablo gösterilmez. Kargo firması
entegrasyonu yoktur: siparişe takip numarası ve
takip URL'si WooCommerce sipariş yönetiminden manuel kaydedilir.
**WooCommerce > Moda Mağaza Araçları** altındaki ücretsiz kargo eşiği doluysa sepette
bilgilendirme çubuğu gösterilir (WooCommerce kargo kuralıyla aynı tutarı girin);
iade ve değişim notu doluysa ürün sayfasında gösterilir. İkisi de boşken görünmez. Hatırlatma
zamanı WordPress zamanlayıcısına bağlıdır. Ürün
yorumları WooCommerce'in yerleşik değerlendirme sistemiyle çalışır; yorum ve
satın alma doğrulaması ayarlarını WooCommerce > Ayarlar > Ürünler bölümünden
kontrol edin.

Ana sayfa ve tema metinlerini **Görünüm > Özelleştir** ekranındaki **Ana sayfa
hero alanı** ve **Site metinleri ve bölümler** bölümlerinden yönetin. Hero
alanında üç slaytın başlık, açıklama, buton bağlantısı ve görseli değiştirilebilir;
carousel 5 saniyede bir ilerler ve oklar, noktalar ya da duraklatma düğmesiyle
kontrol edilir. Hareket tercihi azaltılmış cihazlarda otomatik oynatma kapalıdır.
Aynı panelden kampanya, kategori, koleksiyon, favori ve bülten alanlarını
gösterebilir/gizleyebilir; duyuru, marka rengi ve footer metinlerini
düzenleyebilirsiniz. Logo ve site adı **Görünüm > Özelleştir > Site kimliği**,
üst ve alt menüler ise **Görünüm > Menüler** bölümünden yönetilir. WordPress
sayfalarının içerikleri sayfa düzenleyicisinde, WooCommerce ürünleri ve ürün
kategorileri WooCommerce yönetiminde düzenlenir. Ana sayfa metin düzenleme
seçenekleri tema özelleştirmesidir; sayfa/ürün verilerini değiştirmez.

Moda Store Admin'in güncel sürümünde yöneticiler WordPress yönetimindeki
**Moda Chicago** ekranından basit ve beden/renk seçenekli ürünleri arayabilir,
ekleyebilir ve düzenleyebilir; fiyat, SKU, durum, kategori, görsel ve stok
bilgilerini yönetebilir. Beden ve renk değerleri virgülle ayrılarak girilir;
WooCommerce her kombinasyon için seçilebilir gerçek ürün varyasyonları
oluşturur. Varyasyonların fiyatı ortaktır ve stok miktarı ürün düzeyinde
paylaşılır. Diğer varyasyon özellikleri WooCommerce'in yerleşik ayrıntılı
düzenleyicisinde yönetilir. Ürün formundan MP4 veya WebM video yüklenebilir;
video ürün sayfasındaki görsel galerisinde oynatılır ve mevcut video formdan
izlenip kaldırılabilir. Yükleme boyutu WordPress/hosting sunucusunun sınırına
bağlıdır. Tema katalog görsellerini 720 px, ürün detay görsellerini 1200 px
boyuta kadar sunar. Eklenti güncellemesi için yeni
`dist/moda-store-admin.zip` paketini WordPress'ten yükleyin.

WooCommerce'in **Hesabım** sayfası müşteri kayıt, e-posta ile giriş ve parola
sıfırlama işlemlerini; oturum açtıktan sonra da sipariş, adres, hesap bilgisi
ve çıkış bağlantılarını sunar. Yeni kayıt için WooCommerce otomatik güçlü bir
parola üretir ve etkin **Yeni hesap** e-postasındaki parola belirleme bağlantısı
müşterinin kendi parolasını kurmasını sağlar; parolalar ekranda veya açık
metinle gösterilmez. Bu e-posta WooCommerce > Ayarlar > E-postalar bölümünde
etkin olmalı ve SMTP teslimatı test edilmelidir. Checkout sırasında hesap açma
seçeneği de sunulur; istemeyen müşteri misafir olarak alışverişini
tamamlayabilir.

Mobil uygulama kurulumu ve çevrimdışı bilgilendirme için Moda PWA eklentisini
etkinleştirin. Manifest ve service worker adresleri HTTPS üzerinden site
kökünde sunulur; web sunucusunun WordPress isteklerini bu adreslere yönlendirmesi
gerekir. Tarayıcı destekliyorsa ziyaretçi uygulamayı ana ekrana ekleyebilir.
Push bildirimleri isteğe bağlıdır; bunun için sunucuda OpenSSL, sitede HTTPS ve
kullanıcının açık bildirim izni gerekir. Yönetici bildirim gönderimini WordPress
kontrol panelindeki **Mobil Uygulama** ekranından yapar. Kurulumdan sonra
manifesti, uygulama yükleme akışını ve çevrimdışı ekranı iOS Safari ve Android
Chrome'da test edin.

Checkout kabul eklentisi klasik checkout'ta ve WooCommerce Checkout Block'ta
Mesafeli Satış Sözleşmesi/Ön Bilgilendirme kabulünü ve KVKK bilgilendirme
onayını ayrı ayrı zorunlu tutar. Checkout Block alanları için WooCommerce 8.9
veya üzeri gerekir; daha eski sürümlerde klasik checkout kullanın veya
WooCommerce'i yükseltin. Eklentiyi etkinleştirmeden önce ilgili yasal sayfaları
yayımlayıp checkout'ta test edin; KVKK kutusu pazarlama izni veya açık rıza
yerine kullanılmamalıdır.

Misafir müşteriler, sipariş onay e-postasındaki sipariş numarası ile ödeme
sırasında kullandıkları fatura e-posta adresini [Sipariş Takibi](https://www.modamchicago.com/siparis-takibi/)
sayfasında girerek sipariş durumunu görür. Sipariş numarası tek başına yeterli
değildir; e-posta eşleştirmesi sipariş bilgilerinin başkalarınca görülmesini
önlemeye yardımcı olur. Üye müşteriler giriş yaptıktan sonra **Hesabım >
Siparişler** bölümünde sipariş ve durumlarını görür. Yönetici ve yetkili mağaza
personeli müşteri/sipariş bilgilerini WordPress **WooCommerce > Siparişler** ve
**WooCommerce > Müşteriler** ekranlarında yönetir. Ürün yönetimi için kullanılan
Moda Chicago ekranı müşteri siparişlerinin yönetim alanı değildir.

E-posta teslimatı için alan adı e-postasını ve SMTP teslimatını test edin;
gizlilik politikasını müşteri verisi toplamaya göre güncelleyin.

Bir staging sitesi için hosting hesabında ayrı bir veritabanı ve HTTPS adresi
oluşturun; WordPress'i o ortama kurun, arama motoru indekslemesini kapatın ve
test ortamını erişim korumasına alın. WooCommerce ayarlarında Türkiye/TRY
başlangıcını, mağaza adresini, gönderim bölgelerini ve vergileri işletme
bilgileriyle doğrulayın. Bu projede hosting, veritabanı, gerçek ürün kataloğu,
onaylı hukuki metinler veya ödeme sağlayıcısı hesabı yoktur. Ürün ve işletme
bilgileri tamamlanana, ödeme sağlayıcısının sandbox testi geçene kadar canlı
ödeme yöntemlerini etkinleştirmeyin. Ayrıntılı kontrol listesi için
[`docs/woocommerce-checkout.md`](docs/woocommerce-checkout.md) ve
[`docs/prelaunch-test-plan.md`](docs/prelaunch-test-plan.md) dosyalarına bakın.

## PHP kullanmadan statik demo önizlemesi

Proje kökündeki `index.html`, PHP/WordPress olmadan tarayıcıda açılabilen
Moda Chicago'nun erkek giyim vitrini Chicago Bulls renklerinden esinlenen kırmızı,
siyah ve beyaz paleti; şehir boğasını betimleyen özgün bir yan siluet; güçlü
tipografi ve sade ürün sunumu kullanır. Resmî takım logosu veya maskotu
kopyalanmamıştır. Aynı görsel yaklaşım WordPress temasına ve yönetim demo
paneline de uygulanmıştır. Statik vitrinde ürün kartları, kategori kutuları,
filtreler ve yönetim kartları belirgin odak durumları ve dar ekranlara göre
ayarlanan boşluklarla sunulur.

HTML/CSS/JavaScript erkek giyim demo mağazasıdır. Örnek katalogdaki gömlek,
tişört, pantolon, ceket ve aksesuar ürünlerinde arama, kategori/fiyat/stok
filtreleri, sıralama, favoriler ve bu tarayıcıda saklanan demo sepeti çalışır.
JavaScript veya tarayıcı depolaması kısıtlıysa sepet kalıcı olmayabilir.
Ürün görseline tıklayınca ürün adı, kategori, fiyat, renk, beden seçenekleri ve
demo stok bilgisini gösteren ürün ayrıntı penceresi açılır. Yönetim demosunda
ürüne renk varyantı ekleyebilir, her renk için altı adede kadar farklı açıdan
fotoğraf yükleyebilir ve ürün penceresindeki galeriyle fotoğraflar arasında
geçiş yapabilirsin. Her renk varyantının demo stoğu ayrı tutulur.
Yüklenen ürün fotoğrafları en fazla 1800 px uzun kenara ve 600 KB dosya boyutuna
küçültülür; kaynak dosya sınırı 20 MB'tır. Katalog tarayıcı depolamasında kaldığı
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
en fazla 600 KB, site görselleri en fazla 250 KB olarak tarayıcıda küçültülür.
Ürün görselinde kaynak dosya sınırı 20 MB, uzun kenar sınırı 1800 pikseldir.
Site görsellerinde kaynak dosya sınırı 8 MB, uzun kenar sınırı 1200 pikseldir. Tarayıcı depolama
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

## Instagram Vitrini

`moda-instagram-top` eklentisi Instagram Graph API ile business/creator hesabın son
paylaşımlarını okur; en çok görüntülenen (veya en çok etkileşim alan) paylaşımı
ana sayfada gösterir. WooCommerce > Instagram Vitrini sayfasından erişim anahtarı
(token) girilir; veri 6 saatte bir yenilenir, anahtar aylık otomatik uzatılır.
Instagram tek tek tıklama sayısı vermez; ölçüt görüntülenme veya etkileşimdir.
Erişim anahtarı depoya veya sohbete yazılmaz, yalnızca yönetim ayarına girilir.
