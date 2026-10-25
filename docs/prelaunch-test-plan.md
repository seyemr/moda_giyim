# Yayına çıkış öncesi uçtan uca test planı

Bu plan, mağazanın **staging** kopyasında yürütülmelidir. Staging; canlı
ödeme almamalı, arama motorlarından gizlenmeli, gerçek müşterilere e-posta
göndermemeli ve mümkünse canlı müşteri verisi içermemelidir. Ödeme sağlayıcısının
resmî sandbox/test hesabı ve test kartları dışında gerçek kart kullanmayın.
Test sonuçları sağlayıcı hesabında da doğrulanmadan bir sipariş başarılı kabul
edilmez.

## 1. Test kaydı ve kabul kuralları

Her senaryoda **Sonuç** alanını `Geçti`, `Kaldı` veya `Engellendi` olarak
doldurun. Kalan test için sipariş/test kaydı kimliğini, zamanı, tarayıcı-cihazı,
görüntüyü (gizli bilgi içermeden), ilgili WooCommerce/sağlayıcı günlüklerini ve
uygulanan düzeltmeyi ekleyin. Kart numarası, CVV, parola, API anahtarı veya
kişisel veriyi kayda koymayın.

| Test ID | Sonuç | Tarih / test eden | Kanıt / sipariş no | Hata ve düzeltme |
|---|---|---|---|---|
| Örnek: PAY-02 |  |  |  |  |

**Yayına çıkış kapısı:** Kritik akışların tamamı (ürün/varyasyon satın alma,
stok, başarılı ve başarısız ödeme, sipariş/e-posta, kargo ve iade, yedekten geri
dönüş, SSL/yetki güvenliği) geçtiğinde yayın onayı verin. Engellenmiş veya
kalmış kritik test varsa canlı ödeme açmayın. Düzeltme sonrası etkilenen testi
ve ilişkili regresyon senaryolarını yeniden çalıştırın. Çalıştırılmamış test
`Geçti` sayılmaz.

## 2. Test öncesi hazırlık

1. Staging URL'sinin HTTPS kullandığını; staging'in parola/IP koruması ve
   `noindex`/arama motoru engeli ile kapalı olduğunu doğrulayın. Canlı e-posta
   listesi, arama motoru ve webhook hedefleriyle karışmadığını kontrol edin.
2. WordPress, tema, WooCommerce, ödeme/kargo eklentilerinin sürümlerini ve PHP
   sürümünü kaydedin. Canlıya alınacak sürümler staging'deki test edilenlerle
   aynı olmalıdır.
3. WooCommerce mağaza ülkesi, TRY para birimi, vergi, kargo bölgeleri/ücretleri,
   kupon ve stok ayarlarını işletme/mali müşavir onaylı değerlerle yapılandırın.
   Bilinmeyen vergi veya kargo tutarını tahmin etmeyin.
4. Gerçek işletme bilgileri ve onaylı hukuki metinler tamamlanmadan ödeme
   sağlayıcısı incelemesini/yayını tamamlanmış saymayın.
5. İki farklı e-posta adresi (müşteri ve mağaza yöneticisi), bir misafir, bir
   test müşteri hesabı, erişimi sınırlandırılmış bir personel hesabı ve test
   adresleri hazırlayın.
6. Basit ürün, değişken ürün (en az iki beden/renk varyasyonu), sıfır stoklu
   varyasyon, indirimli ürün, test kuponu ve test kargo bölgeleri oluşturun.
   Stok miktarlarını kaydedip test sonunda başlangıç değerlerine döndürün.
7. Sağlayıcının resmî sandbox/test anahtarlarını yalnızca staging'de ayarlayın.
   Başarı/ret senaryosu için sağlayıcının güncel test talimatlarını izleyin;
   gerçek kart verisi veya canlı anahtar kullanmayın.
8. Her testte kullanılan verileri not edin. Testleri bitirince staging sipariş,
   kupon ve stok verilerini temizleme/başlangıca döndürme yöntemini kararlaştırın.

## 3. Masaüstü, mobil ve erişilebilirlik

Gerçek cihaz veya tarayıcı geliştirici araçlarında en az masaüstü geniş ekran
(1440 px civarı), tablet (768 px civarı) ve dar mobil (360–390 px civarı)
görünümleri kontrol edin. Safari/iOS ve Chrome/Android gibi hedef müşteri
tarayıcılarını ayrıca deneyin.

| ID | Adımlar | Beklenen sonuç | Kalırsa düzeltme |
|---|---|---|---|
| UI-01 | Ana sayfayı, ürün arşivini, ürün detayını, sepeti, ödeme, hesabım ve içerik sayfasını masaüstünde açın. | Taşma/kırık görsel yok; başlık, navigasyon, varyasyonlar ve eylem düğmeleri görünür; sayfalar hata vermeden yüklenir. | Hatalı URL/şablon ve tema-eklenti çakışmasını bulun; konsol ve ağ hatalarını inceleyin, ilgili şablon/CSS'i düzeltip tüm şablonları tekrar kontrol edin. |
| UI-02 | Aynı sayfaları mobilde açıp dikey/yatay kaydırın; menü, arama, filtre, favori, sepet ve ödeme kontrollerini dokunarak deneyin. | Yatay taşma ve üst üste binme yok; hedefler dokunmaya uygun; menü/panel açılıp kapanır ve sayfa kullanılabilir kalır. | Etkilenen breakpoint/CSS veya JS etkileşimini düzeltin; küçük gerçek cihazda ve dar viewport'ta tekrar test edin. |
| UI-03 | Mobil filtre panelini açın, fiyat/kategori/beden/renk/stok seçip uygula ve temizle işlemlerini yapın. | Panel klavye/dokunmayla kontrol edilir; filtreler URL'ye ve sonuçlara yansır; temizleme varsayılan listeyi döndürür. | Form alanı adları, GET parametreleri, panel durumu ve filtre sorgusunu düzeltin; JS kapalıyken formun kullanılabilirliğini de doğrulayın. |
| UI-04 | Klavye ile logo, menü, arama, ürün kartı, filtre, beden/renk ve ödeme alanlarında gezin; odak görünümünü gözleyin. | Odak sırası mantıklı, odak görünür, açılır öğeler klavyeyle kullanılabilir; form etiketleri/hata mesajları belirgindir. | Eksik etiket, odak stili, klavye olayı ve erişilebilir adları düzeltip ekran okuyucu/klavye ile tekrarlayın. |
| UI-05 | Yavaş bağlantıda ana sayfa ve ürün görsellerini açın; hareket azaltma tercihini etkinleştirin. | Görsel boyutları sayfayı bozmaz; içerik anlaşılır kalır; gereksiz/rahatsız edici animasyon yok. | Görselleri uygun boyut/formatta optimize edin, tema/eklenti kaynaklarını inceleyin; animasyonları azaltma tercihine uydurun. |
| UI-06 | Header logo/arama/hesap/favori/sepet, mega menü alt öğeleri, kampanya bannerları, kategori kartları, öküz maskotlu footer, WhatsApp ve bülten bağlantılarını takip edin. | Her bağlantı doğru hedefe gider; WhatsApp düğmesi işletmenin doğru sohbetini açar. Bülten servisi bağlanmadıysa sahte kayıt başarılı mesajı/formu görünmez. | WordPress menü konumlarını, Customizer hedef URL/telefonunu ve mağaza/hesap sayfa atamalarını düzeltin; geçersiz/boş hedefleri kaldırın. |
| UI-07 | Ana sayfa carousel'inde 5 saniye bekleyin; önceki/sonraki, nokta ve duraklatma kontrollerini deneyin. Hover, klavye odağı ve sekme görünürlüğü durumlarını; ayrıca hareket azaltma tercihini kontrol edin. | Slayt ve metin birlikte değişir; kontroller doğru slaytı işaretler; otomatik geçiş hover/odak/gizli sekmede durur, duraklatma ile kapanır ve hareket azaltma açıkken başlamaz. | Carousel JavaScript/CSS yüklenmesini ve erişilebilir durumları inceleyin; masaüstü ve mobilde yeniden test edin. |
| UI-08 | WordPress Customizer'da hero görsel/metinlerini, bölüm görünürlüğünü, marka rengini, duyuru/footer metinlerini ve ülke kodlu WhatsApp numarasını değiştirip kaydedin; logo, menüler, sayfalar, ürün ve kategorileri kendi yönetim ekranlarından güncelleyin. | Kaydedilen içerikler mağaza ön yüzünde görünür; gizlenen bölümler kaybolur; ürün ve sayfa içerikleri ilgili WordPress/WooCommerce kayıtlarından gelir. | Tema sürümünü, doğru ana sayfa/menü atamasını ve cache'i kontrol edin; değişiklik sonrası ön yüzü farklı ekran genişliklerinde doğrulayın. |
| UI-09 | Moda PWA eklentisi etkin ve HTTPS açıkken manifesti, ana ekrana ekleme akışını, uygulamayı çevrimdışı açmayı ve bildirim izni/aboneliğini iOS Safari ile Android Chrome'da sınayın. Yönetimden test bildirimi gönderin. | Manifest ve service worker yüklenir; çevrimdışıyken bilgilendirme sayfası görünür; izin verilen abonelere tıklanabilir bildirim gider; sepet, ödeme ve hesap gibi kişisel sayfalar önbelleğe alınmaz. | HTTPS, WordPress kök yönlendirmesi, OpenSSL, tarayıcı desteği ve service worker kayıt/hata günlüklerini kontrol edin; cache'i temizleyip iki platformda tekrarlayın. |

## 4. Üyelik, hesap ve sipariş akışları

| ID | Adımlar | Beklenen sonuç | Kalırsa düzeltme |
|---|---|---|---|
| ACC-01 | Mobil ve masaüstü header'daki müşteri simgesini açın. Hesap oluşturma sayfasında yeni, benzersiz e-posta ile kayıt yapın; parola belirleme e-postasını tamamlayıp e-posta adresiyle giriş yapın. | Her görünümde bağlantı görünür ve Hesabım sayfasına gider; hesap tek kez oluşur; parola belirleme e-postası teslim edilir; e-posta ile oturum açılır; parola düz metin gösterilmez. | WooCommerce Hesabım sayfası/kayıt ayarları, tema cache'i, zorunlu alanlar, e-posta teslimatı ve spam klasörünü inceleyin; tekrar kayıt/çift kullanıcıyı önleyin. |
| ACC-02 | Var olan e-posta ile tekrar kayıt ve hatalı e-posta/parola gönderin. | Kayıt reddedilir veya güvenli hata iletilir; hesap verisi açığa çıkmaz. | WordPress/WooCommerce doğrulama ve hata metinlerini düzeltin; kullanıcı varlığını gereksiz ifşa etmeyen davranışı koruyun. |
| ACC-03 | Oturum açın, çıkış yapın, yanlış parola deneyin; parola sıfırlama isteği gönderip bağlantıyı kullanın. | Yetkili oturum açar; yanlış parola erişim vermez; tek kullanımlık/süreli sıfırlama bağlantısı çalışır, parola değişince eski parola geçersiz olur. | Site URL/e-posta, HTTPS, parola sıfırlama ve önbellek/oturum ayarlarını kontrol edin; kimlik doğrulama eklenti çakışmasını giderin. |
| ACC-04 | Hesap adreslerini değiştirin; sipariş geçmişi ve sipariş detayını görün. Başka kullanıcı hesabı URL/ID'sini deneyin. | Kullanıcı yalnızca kendi bilgisi ve siparişlerini görür; adresler sipariş/kargo hesaplamasına doğru yansır. | Yetki/nesne sahipliği kontrolünü ve WooCommerce hesap sayfalarını düzeltin; cache'in özel hesap sayfalarını saklamadığını doğrulayın. |
| ACC-05 | Personel, müşteri ve oturumsuz kullanıcılarla mağaza yönetim URL'sini ve doğrudan ürün/sipariş yönetim ekranlarını deneyin. | Seçilen Shop Manager rolü kendi WooCommerce görevlerini yapabilir; müşteri/oturumsuz kullanıcı yönetim ekranlarını göremez; yöneticilik gerektiren ayarlar sadece yetkili kullanıcıya açıktır. | Rol/capability atamalarını geri alın veya daraltın; her doğrudan hedef URL'de WooCommerce yetki denetimini yeniden test edin. |
| ACC-06 | Ürün favorilerini ekleyin/kaldırın; giriş yapmış müşteriyle farklı cihazda Hesabım > Favorilerim'i açın. Destek talebi gönderin, yönetimden yanıtlayın ve başka müşteri hesabıyla deneyin. | Favoriler hesaba kaydedilir; talep sahibi kendi durumu/yanıtını görür, başka hesap görmez; yanıt e-postası gönderilir. | Hesap sahipliği, endpoint/cache kuralları ve SMTP günlüklerini inceleyin. |
| ACC-07 | Hesap detaylarından sepet hatırlatma iznini açın, ürünü sepete ekleyin, sonra izni kapatın; test/staging'de WP-Cron'u bekleyin. | Açık izinle en geç bir gün sonra tek hatırlatma gönderilir; izni kapatınca planlı e-posta iptal olur; başarılı sipariş sonrası hatırlatma gönderilmez. | WP-Cron zamanlamasını, hesap izin meta verisini ve e-posta kayıtlarını inceleyin; izinsiz gönderimi durdurun. |
| ACC-08 | Staging ürün sayfasında stok/fiyat bildirimi talep edin; açık e-posta kullanım onayını işaretleyin. Ardından onaysız, honeypot alanı dolu ve formu açar açmaz gönderilmiş varyantları deneyin. | Geçerli istek e-posta doğrulamasına gider; onaysız veya bot işaretli istek kaydedilmez. | Sunucu tarafı onay, token doğrulama ve bot kontrollerini test edin; canlı müşteriye test e-postası göndermeyin. |
| ACC-09 | Ürün soru-cevap formundan bir soru gönderin; e-posta kullanım onayını işaretleyin. Ardından onaysız, honeypot alanı dolu gönderimleri deneyin. | Geçerli soru moderasyona düşer; onaysız veya bot işaretli gönderim kaydedilmez. | Sunucu tarafı onay/honeypot kontrolünü ve moderasyon kuyruğunu doğrulayın. |
| ORD-01 | Oturum açmış müşteriyle basit ve değişken ürün siparişi oluşturun. Sipariş detayındaki varyasyonları kontrol edin. | Sipariş ürün, beden, renk, miktar, adres ve tutarı doğru kaydeder; WooCommerce sipariş numarası üretir. | Ürün varyasyonları, checkout alan eşlemesi, para birimi/vergi ve sipariş kalemlerini inceleyin; yanlış siparişi test verisi olarak iptal edin. |
| ORD-02 | Misafir checkout'u tamamlayın; sonra aynı e-postayla hesap açma seçeneğini sınayın (etkinse). | Misafir siparişi hesap oluşturmadan tamamlanır; onay e-postası doğru adrese gider; isteğe bağlı hesap akışı ayara uygun çalışır. | WooCommerce misafir ödeme ve hesap oluşturma ayarlarını, checkout blok uyumunu ve e-posta alıcısını düzeltin. |
| ORD-03 | Ödeme formunda eksik/hatalı adres, posta kodu/telefon ve desteklenmeyen gönderim bölgesi deneyin. | İlgili alan hata mesajı verir veya uygun kargo seçeneği sunulmaz; geçersiz sipariş/kargo ücretiyle ödeme alınmaz. | Zorunlu alanlar, ülke/bölge listeleri, kargo bölgeleri ve doğrulama eklentisini düzeltin; istemci doğrulamasına ek sunucu doğrulamasını doğrulayın. |
| ORD-04 | Sipariş tamamlanınca sipariş numarası/durumu, ürün satırları, tutar ve müşteri/satıcı sipariş detaylarını kontrol edin. | WooCommerce kaydı ile müşteri ve yönetici görünümleri tutarlıdır; kart verisi siparişe/loga yazılmaz. | Sipariş meta alanı ekleyen eklentileri ve logları inceleyin; kart verisi tutuluyorsa canlıyı durdurup güvenli biçimde kaldırın ve sağlayıcı akışına geçin. |
| ORD-05 | Misafir olarak sipariş verip onay e-postasındaki sipariş numarasını, ödeme sırasında kullanılan fatura e-postasıyla `/siparis-takibi/` sayfasında sorgulayın. Yanlış e-posta ve yanlış sipariş numarası kombinasyonlarını da deneyin. | Doğru kombinasyon sipariş durumunu gösterir; eşleşmeyen bilgiler sipariş ayrıntısını göstermez. Sayfa yalnızca sipariş numarasıyla sorgu yapmaz. | WooCommerce sipariş takibi shortcode/formunu ve mağaza cache ayarlarını kontrol edin; kimlik doğrulama gereksinimini kaldırmayın. |
| ORD-06 | Üye hesap oluşturup giriş yapın; Hesabım > Siparişler bölümünde hesaba bağlı siparişi açın. Checkout'ta üye olmadan devam etme seçeneğini de doğrulayın. | Üyeye kendi siparişleri ve durumları gösterilir; başka kullanıcı siparişlerine erişemez; misafir checkout kayıt zorunluluğu olmadan çalışır. | WooCommerce hesap/kayıt ayarlarını, kullanıcı-sipariş sahipliğini ve checkout ayarlarını düzeltin; müşteri rolüne sipariş yönetim yetkisi vermeyin. |
| ORD-07 | Yönetimden test siparişine kargo takip numarası ve HTTPS bağlantısı ekleyin; müşteri hesabında sipariş detayını açın. | Yalnızca ilgili müşterinin sipariş detayında takip numarası ve güvenli takip bağlantısı görünür. | HPOS sipariş alanı kaydetme kancasını, sipariş yetkisini ve bağlantı şemasını kontrol edin. |

## 5. Ürün, varyasyon, stok ve kupon

| ID | Adımlar | Beklenen sonuç | Kalırsa düzeltme |
|---|---|---|---|
| CAT-01 | Ürün detayında her beden/renk varyasyonunu seçin; fiyat, SKU ve görseli kontrol edin. | Seçim ürüne ait etkin varyasyonu belirler; fiyat/stok bilgisi o varyasyona göre değişir. | Global nitelik, varyasyon eşlemesi, ürün görünürlüğü ve tema/WooCommerce JS uyumunu düzeltin. |
| CAT-02 | Varyasyonun stok adedini 1 yapın; stokta kalmayan bedeni seçip satın almaya çalışın. | Stokta olan varyasyon sepete alınır; sıfır stoklu varyasyon satın alınamaz (backorder bilinçli açılmadıysa). | Stok yönetimi, backorder, katalog görünürlüğü ve varyasyon purchasable/stok durumunu kontrol edin; eklenti önbelleğini temizleyin. |
| CAT-03 | Aynı varyasyondan aynı anda iki ayrı tarayıcı/oturumda satın alma başlatın ve siparişleri sırayla tamamlayın. | Stoktan fazla sipariş onaylanmaz; ikinci checkout stok yetersizliğiyle durdurulur veya WooCommerce’in yapılandırılmış stok tutma kuralına uyar. | Stok tutma süresi, HPOS/eklenti uyumu, ödeme sağlayıcısı ve eşzamanlı stok akışını staging'de inceleyin; stok aşımında yayını durdurun. |
| CAT-04 | Ürün, beden/renk ve stok filtrelerini; fiyat aralığını ve sıralamayı (fiyat/popülerlik) birleştirerek deneyin. | Sonuçlar seçili ölçütlerin kesişimini doğru verir; sayfalama ve sıralama devam eder; stok dışı görünürlük WooCommerce ayarına uygundur. | Global özellik/taksonomi adları, URL parametreleri, sorgu ve katalog görünürlük ayarını düzeltin; farklı filtre kombinasyonlarını tekrar deneyin. |
| CAT-05 | Yönetimden CSV ile küçük bir ürün ve mevcut SKU eşleşen test ürünü/varyasyonu içe aktarın. | Yeni kayıt eklenir; eşleşen kayıt beklenen sütunlarla güncellenir; eşleşmeyen ürün yanlışlıkla ezilmez; hata raporu alınır. | Dışa aktarılan şablonu, UTF-8 kodlamayı, sütun eşlemesini, SKU/ID ve parent ilişkisini düzeltin; staging yedeğinden geri alın. |
| CAT-06 | Moda Mağaza Araçları'na doğrulanmış beden ölçüleri ekleyin. Ürün sayfasında soru gönderip yönetimden yanıtlayın; geçerli/yanlış e-postayla bildirim isteği oluşturup doğrulayın; stok ve fiyat koşullarını staging'de değiştirin. | Tablo ürün sayfasında görünür; yanıt yalnızca moderasyon sonrası gösterilip e-postalanır; uyarı yalnızca e-posta doğrulamasından sonra ve koşul sağlanınca gönderilir. | Ürün ayarlarını, moderasyon, alert statülerini ve SMTP günlüklerini inceleyin; staging dışında toplu uyarı tetiklemeyin. |
| INV-01 | Yönetim stok miktarını değiştirin; ürün sayfası, arşiv, sepet ve checkout'ta doğrulayın. | Tüm ekranlarda WooCommerce'in aynı güncel stok durumu kullanılır; yönetim değerinin üstüne önbellek eski stok göstermez. | Object/page cache ve ürün stok senkronizasyonunu inceleyin; sepet/checkout hariç önbellek kurallarını düzeltin. |
| CUP-01 | Geçerli yüzde/sabit indirim kuponunu, kapsamındaki ürün ve gereken sepet koşuluyla uygulayın. | İndirim yalnızca tanımlı kapsamda hesaplanır; sipariş özeti sunucu hesaplamasıyla güncellenir. | Kupon türü, ürün/kategori kapsamı, minimum harcama, vergi ayarı ve tema checkout sunumunu düzeltin. |
| CUP-02 | Süresi geçmiş, kullanım limiti dolmuş, yanlış, dışlanan ürün veya minimum tutarı karşılamayan kupon girin. | Kupon reddedilir ve açık hata gösterilir; indirim uygulanmaz; toplam geçerli değerde kalır. | WooCommerce kupon kuralları ve checkout validasyonunu inceleyin; tarayıcıdan değiştirilen indirim/tutarın sunucuda kabul edilmediğini doğrulayın. |
| CUP-03 | Kuponu ekleyip çıkarın; sayfayı yenileyin ve başka ürüne geçin. | Ara toplam/indirim/genel toplam tutarlı değişir; kupon kapsamı dışındaki ürün indirime girmez. | WooCommerce sepet oturumu, kupon önbelleği ve tekrar hesaplamayı düzeltin; test kuponunu gerçek müşteriye açmayın. |

## 6. Ödeme, sipariş e-postası ve kargo

Başarılı/ret testinin sağlayıcı panelinde sandbox/test işlemi olarak göründüğünü
doğrulayın. Sandbox testinin canlı ödeme kabulünü kanıtlamadığını unutmayın.
Sağlayıcının test kartı/sonuç talimatları değişebileceğinden güncel resmî
belgelerini izleyin.

| ID | Adımlar | Beklenen sonuç | Kalırsa düzeltme |
|---|---|---|---|
| PAY-01 | Staging'de WooCommerce checkout sayfası/blok ve seçilen ağ geçidiyle ödeme ekranını açın. | Ağ geçidi doğru görünür; sağlayıcı checkout/blok uyumu vardır; HTTPS, toplam ve sipariş özeti nettir. | Sağlayıcı eklentisinin güncel sürüm/uyumluluk gereksinimini kontrol edin; staging'de desteklenen checkout tipini kullanın ve eklenti günlüklerini inceleyin. |
| PAY-02 | Sandbox'ın resmî başarılı ödeme test senaryosunu uygulayın. | Sağlayıcı paneli işlemi test/başarılı gösterir; doğrulanmış callback/webhook siparişi doğru duruma getirir; sipariş kalem/toplamı eşleşir; stok yalnızca WooCommerce kurallarına uygun güncellenir; onay e-postaları gönderilir. | Anahtarların test ortamına aitliğini, callback URL/secret, TLS, sunucu saatini ve sağlayıcı/WooCommerce loglarını inceleyin. Dönüş sayfasına bakıp ödemeyi elle başarılı işaretlemeyin; callback düzelince yeni sandbox siparişiyle baştan deneyin. |
| PAY-03 | Sandbox'ın ret/başarısız ödeme senaryosunu uygulayın. | Ödeme başarılı görünmez; sipariş `failed`/`pending` gibi sağlayıcı ve WooCommerce akışına uygun durumda kalır; ürün sevk edilmez; stok tutma/geri bırakma davranışı ayara uygun olur; müşteriye doğru bildirim verilir. | Sağlayıcı dönüş/callback ve sipariş durum eşlemesini inceleyin; başarısız siparişten stok geri yükleme ve yeniden ödeme akışını staging'de yeniden doğrulayın. |
| PAY-04 | Ödeme ekranını kapatın/iptal edin; sağlayıcıya yönlenmeden geri dönmeyi ve geç/tekrarlı callback durumunu sağlayıcı test araçlarıyla deneyin. | İptal/timeout ödeme kanıtı sayılmaz; sipariş yanlışlıkla tamamlanmaz; yinelenen bildirim çift stok düşümü/çift sipariş oluşturmaz. | Idempotency/callback imzası ve durum geçişini sağlayıcı eklentisiyle inceleyin; çözülemeyen callback tutarsızlığında canlı ödemeyi açmayın ve sağlayıcı desteğine başvurun. |
| PAY-05 | Tarayıcı geliştirici araçlarında gönderilen fiyat/kupon/kargo alanlarını değiştirip checkout gönderin. | WooCommerce sunucu tarafında doğrular/yeniden hesaplar; değiştirilmiş ucuz tutar veya yetkisiz indirimle sipariş/ödeme oluşmaz. | Fiyatı istemciden kabul eden özel kod/eklenti varsa kaldırın; WooCommerce sepet toplamlarının sunucu hesaplamasını doğrulayın. |
| MAIL-01 | Yeni sipariş, ödeme başarılı, başarısız/iptal, tamamlandı ve parola sıfırlama e-postalarını tetikleyin. | Beklenen müşteri/yönetici e-postaları doğru durum ve içerikle, doğru alıcıya teslim edilir; bağlantılar HTTPS/doğru alan adıdır. | WooCommerce e-posta ayarları, gönderen DNS/SPF/DKIM/DMARC, SMTP/işlemsel posta günlüğü ve spam klasörünü inceleyin; tekrar teslim testi yapın. |
| MAIL-02 | Sipariş e-postalarını farklı istemcide ve mobilde açın; test siparişinin kişisel/veri alanlarını kontrol edin. | Metin okunur, sipariş no/tutar/ürün doğru; gereksiz hassas bilgi (kart/CVV) yoktur; bağlantılar çalışır. | E-posta şablonu ve eklenti alanlarını düzeltin; hassas veriyi log/metadata'ya ekleyen kaynağı kaldırın. |
| SHIP-01 | Her tanımlı gönderim bölgesinde adres girin; bir bölge dışı adres de deneyin. | Yalnızca geçerli bölgeye tanımlı yöntem/ücret gösterilir; eşleşmeyen adrese bilinmeyen/yanlış kargo ücretiyle sipariş verilemez. | Ülke/il/posta kodu bölge eşlemesini ve önceliğini düzeltin; vergi ve kargo adresi temelini doğrulayın. |
| SHIP-02 | Ücretsiz kargo eşiğinin altında, tam eşikte ve üstünde sepet oluşturun. | İşletmece tanımlı eşik kuralı her üç sınırda doğru uygulanır; sepet/checkout toplamları aynı olur. | Kargo yöntemindeki eşik ve vergi dahil/hariç sepet tutarı koşulunu düzeltin; sınır değerlerinde tekrarlayın. |
| SHIP-03 | Sipariş yönetiminde kargo yöntemi, takip numarası ve müşteriye bildirim akışını deneyin. | Gerçek taşıyıcı entegrasyonu varsa geçerli takip numarası kaydolur, müşteriye iletilir ve takip bağlantısı çalışır. Entegrasyon yoksa bu özellik varmış gibi gösterilmez; sipariş manuel operasyonla yönetilir. | Kargo eklentisi/hesap ve API yapılandırmasını sağlayıcıyla doğrulayın; takip numarası elle giriliyorsa operatör prosedürü oluşturun. Kargo etiketi/gerçek gönderi staging'de üretilmez. |

## 7. İade, iptal ve hesap mutabakatı

| ID | Adımlar | Beklenen sonuç | Kalırsa düzeltme |
|---|---|---|---|
| REF-01 | Sandbox'ta ödenmiş sipariş için WooCommerce üzerinden kısmi/tam iade senaryosunu sağlayıcı test olanaklarıyla çalıştırın. | WooCommerce iade kaydı ve sağlayıcı test sonucu birbiriyle tutarlıdır; iade tutarı/sipariş durumu doğru; ürün iade stok kuralı işletme politikasına uygundur. | Sağlayıcı iade API/izinlerini, ağ geçidi eklentisini ve vergi/kalem iadesini inceleyin; WooCommerce sipariş notu ile sağlayıcı panelini mutabık kılın. Sandbox iadesi desteklenmiyorsa `Engellendi` yazıp sağlayıcı teyidi alın. |
| REF-02 | Ödeme öncesi iptal, ödeme sonrası iptal ve yeniden ödeme gereken başarısız siparişi ayrı deneyin. | Sipariş durumu, stok serbest bırakma ve para iadesi birbirine karıştırılmaz; ödeme sonrası iade gerektiğinde sağlayıcıda da işlem yapılır. | Durum geçişlerini ve stok geri yükleme ayarını düzeltin; finansal iade onayı olmadan siparişi yalnızca tamamlandı/iptal durumuna getirerek süreci kapatmayın. |
| REF-03 | Sipariş, sağlayıcı paneli ve varsa banka/ödeme raporundaki test kayıtlarını karşılaştırın. | Sandbox toplam, para birimi ve iade toplamları tutarlıdır; canlı hesapta test siparişi/gerçek tahsilat yoktur. | Sağlayıcı referansını ve WooCommerce sipariş notlarını uzlaştırın; canlı işlem görülürse sağlayıcıyla hemen iletişime geçin, canlı anahtar kullanımını kapatın. |

## 8. SEO, içerik ve performans

| ID | Adımlar | Beklenen sonuç | Kalırsa düzeltme |
|---|---|---|---|
| SEO-01 | Ana sayfa, ürün, kategori, sepet/hesap ve 404 URL'lerini açın; HTTPS ve canonical/redirect davranışını inceleyin. | SEO sayfaları tek ve temiz HTTPS URL'de; ürün/kategori slug'ları anlamlı; yanlış bağlantı kontrollü 404 verir. Sepet/hesap/checkout indekslenmez. | Kalıcı bağlantıları ve WooCommerce sayfa atamalarını düzeltin; yönlendirme döngülerini giderin; özel/kişisel sayfalara noindex uygulayın. |
| SEO-02 | Google Search Console/SEO aracı veya kaynak kodla title, meta description, canonical, robots, site haritasını kontrol edin. | Arama sayfaları ve gerçek ürün/kategoriler doğru indeks talimatına sahip; staging `noindex`; sitemap canlı alan adını ve doğru canonical'ları içerir. | SEO eklentisi/WordPress görünürlük ayarını staging/live için ayırın; sitemap'i yeniden üretip hatalı/özel URL'leri çıkarın. |
| SEO-03 | Bir ürünün yapılandırılmış verisini test aracıyla doğrulayın; fiyat, para birimi, stok, varyasyon ve değerlendirme verisini karşılaştırın. | Product/Offer verisi sayfada görünen güncel WooCommerce verisiyle tutarlı; mevcut olmayan puan/değerlendirme uydurulmaz. | Tema, WooCommerce ve SEO eklentisinin çift/çakışan schema çıktısını düzeltin; yapılandırılmış veriyi gerçek katalogla tekrar test edin. |
| SEO-04 | Ana sayfa kampanyaları, ürün/kategori görselleri, mobil menü, arama ve footer bağlantılarını tarayın; görsel alt metinlerini gözden geçirin. | Hedefler çalışır; 404/kırık görsel yok; anlamlı görseller uygun alternatif metne sahip; dekoratif görseller anlamsız metin taşımaz. | Hatalı hedefi güncelleyin, görseli yeniden yükleyin/alt metni düzenleyin; kampanya linklerini staging'de tekrar tıklayın. |
| PERF-01 | Lighthouse/WebPageTest veya benzeri ölçümle mobil ve masaüstü ana sayfa, kategori, ürün sayfası ölçün; soğuk/sıcak cache sonuçlarını kaydedin. | Sayfalar kullanılabilir hızda; görsel boyutları makul; ağır kaynak/uzun ana iş parçacığı yok. İşletmenin belirlediği CWV hedeflerini geçer. | Büyük görselleri uygun ölçü/formatta sıkıştırın, gereksiz eklentiyi kaldırın; cache/CDN'i kontrollü yapılandırıp sepet/checkout/hesap/cookie/ajax uçlarını cache dışında bırakın ve yeniden ölçün. |
| PERF-02 | Cache açıkken ürün stok/fiyat/kupon değiştirin; iki farklı kullanıcıda sepet/checkout/hesap sayfalarını test edin. | Kişiye özel sepet/checkout/hesap içeriği kullanıcılar arasında sızmaz; değişen fiyat/stok eski cache'te kalmaz. | Cache eklentisi/CDN dışlama kurallarını düzeltin; ilgili sayfaları/endpoint'leri cache dışı bırakın ve oturumlar arası tekrar test edin. |

## 9. Yedekleme, geri yükleme ve güvenlik

| ID | Adımlar | Beklenen sonuç | Kalırsa düzeltme |
|---|---|---|---|
| BAK-01 | Canlıya çıkış öncesi dosya ve veritabanı yedeği oluşturun; tarih, saklama yeri, şifreleme ve saklama süresini kaydedin. | Yedek her iki bileşeni içerir; üretim sunucusundan ayrı erişim kontrollü yerde tutulur; yedek işi başarılı rapor verir. | Yedek kapsamı/izinini, depolama kotasını ve otomasyonu düzeltin; başarılı çıktı ve geri dönüş prosedürü oluşmadan yayına çıkmayın. |
| BAK-02 | Yedeği izole staging/kurtarma ortamına geri yükleyin; giriş yapın, ürün, varyasyon, sipariş, medya ve tema sayfalarını kontrol edin. | Veritabanı-dosya ilişkisi tutarlı, medya ve siparişler açılır, alan adı/URL güvenle staging'e çevrilir; geri yükleme süresi kayıtlıdır. | Eksik dosya/DB/secret ve arama-değiştirme adımlarını düzeltin; yeni yedek alıp tam geri yükleme tatbikatını tekrarlayın. |
| SEC-01 | Siteyi HTTPS ile açın; HTTP'yi HTTPS'ye yönlendirin; sertifika ve karma içerik uyarılarını kontrol edin. | Geçerli sertifika, tüm mağaza/hesap/checkout bağlantılarında HTTPS; mixed content yok. | Sertifika/hosting proxy ve WordPress site URL'sini düzeltin; HTTP kaynaklarını güncelleyin ve yeniden tarayın. |
| SEC-02 | WordPress/WooCommerce/tema/eklenti sürümlerini, kullanılmayan eklentileri, yönetici hesaplarını ve personel rollerini gözden geçirin. | Desteklenen güncel sürümler; kullanılmayan bileşen kapalı/kaldırılmış; her kullanıcı en az gerekli yetkiye sahip; MFA güçlü yönetici hesaplarında etkin (destekleniyorsa). | Staging yedeği alın, güncellemeleri staging'de doğrulayıp uygulayın; hesapları devre dışı bırakın/rolü daraltın; eklenti kaynağı ve uyumluluğunu inceleyin. |
| SEC-03 | Parola sıfırlama, yetkisiz doğrudan admin URL, müşteri sipariş erişimi ve form girdilerinde temel yetki/girdi doğrulaması deneyin. | Yetkisiz erişim reddedilir; müşteri yalnızca kendi siparişine erişir; form verisi güvenli doğrulanıp ekrana kaçışlanır. | Rol/capability, sahiplik, nonce ve giriş/çıkış doğrulama sorununu düzeltin; güvenlik açığı şüphesinde yayını durdurup kod incelemesi yapın. |
| SEC-04 | Test anahtarlarının staging'de olduğunu; canlı anahtarların kod deposu, tema dosyası, log, yedek dışa aktarımı ve ekran görüntüsünde bulunmadığını kontrol edin. | Gizli anahtarlar erişimi kısıtlı sağlayıcı/WooCommerce yapılandırmasında; test ve canlı hesaplar karışmamış; kart numarası/CVV saklanmıyor. | Açığa çıkan anahtarı derhal sağlayıcıdan iptal edip döndürün; log/yedek erişimini sınırlandırın; anahtarı kaynak dosyasından kaldırıp güvenli yapılandırmaya alın. |
| SEC-05 | Güvenlik/uyumluluk taraması ve sunucu/WP günlüklerinde PHP fatal, şüpheli admin girişi, ödeme callback hatası ve hassas veri arayın. | Yayını engelleyen açıklık, fatal hata, hassas veri/log sızıntısı veya doğrulanmamış ödeme bulunmaz. | Günlükteki hatayı staging'de yeniden üretip kök nedeni düzeltin; sızıntıyı ve erişimi yönetin; sadece logu silip sorunu kapatmayın. |
| SEC-06 | Staging'in indekslenmediğini, canlı robots/noindex ayarının ise yayın anında doğru değişeceğini ve staging webhooklarının canlı URL olmadığını doğrulayın. | Test sitesi arama sonuçlarına girmez; canlı site yanlışlıkla noindex kalmaz; webhook/return URL ortamları ayrıdır. | Ortam URL ve SEO görünürlük ayarlarını düzeltin; canlıya geçiş kontrol listesinde indeks ve callback doğrulaması ekleyin. |

## 10. Yayın günü duman testi ve geri dönüş

1. Yayından hemen önce dosya + veritabanı yedeği alın ve geri yükleme kanıtının
   mevcut olduğunu doğrulayın.
2. Test edilen sürüm ve ayarları canlıya aktarın; DNS/TLS, WordPress site
   adresleri, kalıcı bağlantılar, para birimi, mağaza sayfaları ve stok
   ayarlarını kontrol edin.
3. Canlı ödeme anahtarlarını sadece başvuru/onay ve sağlayıcı talimatı
   tamamlandıktan sonra girin. Sandbox anahtarlarını canlıda kullanmayın. Test
   kartıyla canlı tahsilat başlatmayın.
4. Gerçek ürün satın almadan ana sayfa, kategori, ürün, arama, sepet ve checkout
   sayfalarını açın; yöneticiye güvenli bir sipariş e-postası testi yapın.
   Canlıda ödeme doğrulaması gerekiyorsa sağlayıcıyla önceden kararlaştırılmış,
   tutarı ve iade yöntemi onaylı kontrollü prosedür olmadan sipariş oluşturmayın.
5. Ödeme callback/webhook, sipariş durumları ve e-posta teslimini gerçek
   sağlayıcı panelinden ve WooCommerce'den izleyecek sorumlu kişiyi belirleyin.
   Canlı işlemi izinsiz test ederek karttan tahsilat yapmayın.
6. Kritik hata, yanlış fiyat/tahsilat, stok aşımı, kişisel veri sızıntısı,
   sipariş e-postası/ödeme callback arızası görülürse yeni sipariş alımını
   güvenli biçimde durdurun, sağlayıcıyla iletişime geçin ve kayıtlı geri dönüş
   prosedürünü çalıştırın. Yedeğe dönmek yeni sipariş/ödeme kayıtlarını
   etkileyebileceğinden, önce işletme ve sağlayıcı kayıtlarıyla mutabakat yapın.
7. Yayın sonrası aynı temel akışları masaüstü ve telefonda tekrar kontrol edin;
   hata oranı ve sipariş durumu düzenli gözden geçirilene kadar operasyon
   sorumlusunu hazır bulundurun.

## Projeye özel sınırlar

Tema; ürün, sepet, hesap ve checkout görünümlerini WooCommerce'e bağlar; gerçek
ödeme sağlayıcısı, kargo firması entegrasyonu, gönderi takibi, SMTP, vergi
oranları, yasal metinler ve işletme bilgileri bu çalışma alanında yapılandırılmış
değildir. Bu nedenle ilgili testler gerçek staging ortamı ve işletme hesapları
sağlanana kadar **Engellendi** kalmalıdır; tema içinde varmış gibi kabul
edilmemelidir. Görsel favorileri tarayıcıda saklanır, hesaplar/cihazlar arasında
eşitlenmez. Gerçek e-posta bülteni bağlantısı ayrıca yapılandırılmalıdır.

Ödeme ayarları ve sağlayıcı değerlendirmesi için
[WooCommerce sepet ve ödeme kurulum rehberi](./woocommerce-checkout.md);
personel yetkileri ve CSV akışı için [mağaza yönetim rehberi](./store-admin.md)
kullanılmalıdır.
