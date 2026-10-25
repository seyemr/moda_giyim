# WooCommerce sepet ve ödeme kurulumu

Bu tema sepet ve ödeme için WooCommerce’in kendi sayfalarını, sipariş modelini,
stok işlemlerini ve ödeme ağ geçitlerini kullanır. Tutarları tarayıcıdan kabul
eden özel bir ödeme kodu eklenmemiştir. Fiyat, kupon, kargo, vergi, stok,
zorunlu alanlar ve sipariş durumları sunucuda WooCommerce ve etkin sağlayıcı
tarafından hesaplanıp doğrulanmalıdır.

Canlı mağazada aşağıdaki ayarları staging ortamında hazırlayıp test etmeden
ödeme almayın. Hesap bilgileri, vergi oranları, yasal sayfalar veya ödeme
anahtarları bu projede tanımlı değildir.

## 1. WooCommerce temel ayarları

WordPress yönetiminde **WooCommerce > Ayarlar**:

1. **Genel**
   - Mağaza adresini, satış yapılacak ve gönderim yapılacak ülkeleri doğru girin.
   - Para birimini TRY olarak seçin (farklı para birimi ancak mağaza iş modeli ve
     sağlayıcı hesabı destekliyorsa).
   - Kuponları kullanacaksanız kuponları etkinleştirin.
   - Vergi hesaplamasını yalnızca muhasebeci/vergi uzmanı oranları, fiyatların
     vergi dahil/hariç girilmesi ve adres esasını onayladıktan sonra açın.
2. **Ürünler > Envanter**
   - Stok yönetimini etkinleştirin; stok miktarı ve stokta yok davranışını
     ürün/varyasyon akışınıza göre belirleyin.
   - Varyasyon bazlı stok kullanılacaksa her beden/renk varyasyonunda ayrı SKU ve
     stok miktarını yönetin. Ürün düzeyi stok yönetimini, varyasyonların stok
     sayımını yöneten seçenekle çelişecek biçimde kullanmayın.
   - Bekleyen siparişler için stok tutma süresini operasyon ekibiyle belirleyin.
3. **Hesaplar ve gizlilik**
   - Misafir ödeme seçeneğini etkinleştirin.
   - İsteğe bağlı checkout hesabı, Hesabım sayfası kaydı ve parola oluşturma
     e-postasını etkin tutun. Bu mağazada bu seçenekler açıktır; müşteri ödeme
     sırasında kayıt olabilir veya bu seçeneği kullanmadan misafir sipariş
     verebilir.
   - Üyeler sipariş durumunu **Hesabım > Siparişler** bölümünde izler. Misafir
     siparişleri [Sipariş Takibi](https://www.modamchicago.com/siparis-takibi/)
     sayfasında sipariş numarası ve fatura e-posta adresiyle sorgulanır.
4. **Gönderim**
   - Türkiye, uluslararası bölgeler ve varsa yerel teslimat için ayrı gönderim
     bölgeleri oluşturun.
   - Ücretleri, ücretsiz gönderim eşiğini ve ödeme yöntemi/kargo eşleşmesini test
     edin. Tutar ve eşikler işletme kararıdır; burada varsayılmamıştır.
5. **Ödemeler**
   - Yalnızca onaylanmış ve test edilmiş sağlayıcıyı etkinleştirin.
   - Havale, kapıda ödeme veya diğer çevrimdışı yöntemleri işletme kabul ediyorsa
     ayrıca etkinleştirip açıklama ve koşullarını girin.
6. **E-postalar**
   - Gönderen adını ve alan adınızla uyumlu gönderen adresini yapılandırın.
   - SMTP/işlemsel e-posta sağlayıcısıyla sipariş e-postalarının gerçekten
     teslim edildiğini test edin; spam gelen kutusu ve teslimat günlüklerini
     kontrol edin.
7. **Sözleşme ve KVKK bilgilendirmesi**
   - Checkout'ta Mesafeli Satış Sözleşmesi ve Ön Bilgilendirme Formu için ayrı,
     zorunlu kabul kutusu; Gizlilik/KVKK Aydınlatma Metni için ise ayrı bir
     okudum/bilgilendirildim kutusu kullanın. İkinci kutu pazarlama izni veya
     kişisel veri işlemeye yönelik açık rıza olarak sunulmamalıdır.
   - Kutuların boş bırakılması hâlinde sipariş oluşturulmadığını ve onayların
     sipariş kaydında saklandığını staging checkout'ta doğrulayın.

WooCommerce sepet ve ödeme sayfalarının **WooCommerce > Ayarlar > Gelişmiş**
altında atanmış, erişilebilir ve doğru sayfa içeriklerine sahip olduğunu
doğrulayın. Checkout Block kullanılıyorsa seçilen ağ geçidinin blok ödeme
uyumluluğunu ayrıca teyit edin; sözleşme/KVKK alan eklentisinin Block desteği
için WooCommerce 8.9 veya üzeri gerekir. Daha eski sürümde güncelleyin ya da
uyumlu klasik checkout sayfasında staging testi yapın.

### Müşteri kaydı ve sipariş takibi

- Yeni müşteri üst menüdeki kişi simgesinden **Hesabım** sayfasına girer; kayıt
  formunda e-posta adresini kullanır. WooCommerce parola üretir ve etkin yeni
  hesap e-postasındaki güvenli parola oluşturma bağlantısını gönderir. Checkout
  sırasında da hesap açma seçeneği gösterilir. Hesap açmadan devam etmek
  isteyenler misafir checkout'u kullanır.
- Kayıt, parola sıfırlama, sipariş, destek yanıtı ve ürün uyarı e-postalarını
  SMTP üzerinden gerçek bir alıcıyla test edin. Teslimat başarısızsa canlı kayıt
  veya bildirim akışını çalışır kabul etmeyin.
- Üye müşteriler, giriş yaptıktan sonra **Hesabım > Siparişler** bölümünde
  sipariş geçmişini ve sipariş ayrıntı/durumunu görür.
- Misafirler `/siparis-takibi/` adresinde sipariş numarası ve siparişin fatura
  e-posta adresini girer. Sipariş numarası onay e-postasında yer alır. Yalnızca
  sipariş numarasıyla sorgulama yapılmaz; e-posta eşleşmesi başkasının sipariş
  ayrıntılarını ifşa etmemek için gerekir.
- Yönetici siparişleri **WooCommerce > Siparişler**, müşteri kayıtlarını
  **WooCommerce > Müşteriler** ekranında inceler. Erişim sadece gerekli yönetici
  ve mağaza yöneticisi rollerine verilmelidir; müşteri verisini statik demo
  panelinde veya herkese açık sayfalarda göstermeyin.

## 2. Sunucu tarafı tutarlılık kuralları

- İstemciden gelen ürün fiyatı, indirim, vergi, kargo tutarı veya ara toplamı
  sipariş tutarı olarak kabul etmeyin. WooCommerce ürün/varyasyon fiyatı ve etkin
  kupon kurallarından sepeti sunucuda yeniden hesaplar.
- Ürün varyasyonları seçilmeden sipariş oluşturulmamalıdır. WooCommerce seçilen
  varyasyonun ürüne ait olduğunu, satın alınabilirliğini, durumunu ve stok
  uygunluğunu kontrol eder.
- Adres zorunlulukları mağazanın satış ülkeleri ve checkout alan ayarlarına göre
  belirlenir; kargo bölgesi, vergi adresi ve ücret checkout sırasında yeniden
  hesaplanır.
- Sipariş oluşturulup ödeme tamamlanana kadar gönderim yapmayın. Ödeme ağ
  geçidinin doğrulanmış callback/webhook sonucu ve WooCommerce sipariş durumu
  esas alınmalıdır; müşterinin dönüş sayfası tek başına ödeme kanıtı değildir.
- Başarısız/iptal edilen ödeme siparişi başarısız/beklemede bırakmalı, stok ve
  yeniden ödeme davranışı WooCommerce'in sipariş/ödeme ağ geçidi akışına göre
  doğrulanmalıdır.
- Kart numarası, CVV veya tam kart verisini WooCommerce sipariş meta alanlarına,
  loglara ya da sunucuya kaydetmeyin. Sağlayıcının barındırdığı ödeme sayfası,
  tokenizasyon veya desteklediği güvenli checkout bileşenini kullanın.

Temada özel fiyat/kupon/kargo/vergi hesabı veya sahte bir ödeme ağ geçidi
bulunmaz; bunun nedeni hesaplama ve ödeme otoritesini WooCommerce/sağlayıcıda
tutmaktır.

## 3. Türkiye ödeme sağlayıcısı değerlendirmesi

Seçimden önce işletme türünüz, başvuru sonucu, komisyon/valör teklifi, iade
kuralları, taksit koşulları, 3-D Secure, blok checkout uyumu ve test ortamı
sağlayıcıyla yazılı olarak doğrulanmalıdır. Ücret ve kabul garantisi verilmez.

| Seçenek | WooCommerce kanıtı ve artılar | Kontrol edilmesi gerekenler |
|---|---|---|
| **iyzico** | [iyzico WooCommerce eklentisi](https://wordpress.org/plugins/iyzico-woocommerce/) sağlayıcı ekibince yayımlanır; eklenti listesi sandbox ve taksit desteği belirtiyor. Bu nedenle ilk staging değerlendirmesi için uygun adaydır. | Liste gereksinimleri (PHP/WordPress/WooCommerce), Checkout Form yönlendirme/iframe davranışı, taksit uygunluğu, komisyon, iade ve ödeme aktarım vadelerini başvuru öncesi ve güncel sürümle doğrulayın. |
| **PayTR iFrame API** | [PayTR WooCommerce eklentisi](https://wordpress.org/plugins/paytr-sanal-pos-woocommerce-iframe-api/) PayTR adına yayımlanmıştır; sağlayıcının [SSS sayfası](https://www.paytr.com/sss) taksit ayarının mağaza panelinden yönetilebildiğini, iptal/iade seçeneklerini açıklar. | WordPress.org eklenti sayfası güncel üç ana WordPress sürümüyle test edilmediğini belirtiyor. Güncel PHP/WP/WooCommerce ve Checkout Block uyumunu staging'de yazılı destek teyidiyle doğrulayın. Mağaza no, parola ve gizli anahtar gerekir; bunları kod deposuna koymayın. |
| **ParamPOS** | WooCommerce entegrasyonu sunan eklentiler bulunabilir; entegrasyonun varlığı tek başına sağlayıcının ticari uygunluğunu göstermez. | Param'ın [resmî duyurusu](https://param.com.tr/) yönetim/kayyım süreci ve hak ediş ödemelerine ilişkin kısıtlama bildiriyor. Bu duyuru geçerli olduğu sürece yeni canlı mağaza için önermiyoruz; hukuki/ticari durum ve ödemelerin aktarılması doğrudan sağlayıcıdan doğrulanmadan başvuru yapmayın. |

Bu tema seviyesinde hiçbir sağlayıcı etkin değildir. Test/canlı anahtarları sadece
sağlayıcının resmî WooCommerce eklentisinin ayarlarında, ortamı açıkça ayrılmış
olarak tutulmalıdır. Erişim yalnızca güvenilir yöneticilerle sınırlandırılmalı;
kimlik bilgileri kaynak dosyasına, ekran görüntüsüne veya loga yazılmamalıdır.

## 4. Canlı üyelik/işletme doğrulaması

Sağlayıcıların kabul ölçütü değişebilir. Başvuru öncesi ilgili sağlayıcının
güncel üye işyeri listesi ve sözleşmesi esas alınmalıdır. Genellikle istenebilecek
bilgi ve belgeler (tam liste değildir):

- Gerçek kişi işletmesi veya şirketin yasal adı, vergi/kimlik bilgileri ve
  kayıtlı adresi.
- Yetkili temsilci/yetkililerin kimlik ve imza yetkisi belgeleri.
- Üye işyeri hesabıyla eşleşen banka/IBAN ve hak ediş hesabı bilgileri.
- Alan adı ve mağaza üzerinde sahiplik/yönetim erişimi kanıtı.
- Satılacak ürünler, fiyatlandırma, teslimat süresi ve müşteri iletişim bilgileri.
- Sağlayıcının istediği şirket kuruluş/vergi levhası, ticaret sicili/imza
  sirküleri veya esnaf/serbest meslek belgeleri; belge adları işletme türüne göre
  değişir.
- Yayında ve doğru işletme bilgileriyle hazırlanmış iletişim, teslimat, iade ve
  iptal, gizlilik/KVKK, çerez, mesafeli satış ve ön bilgilendirme sayfaları.

Bu maddeler hukuki yeterlilik kararı değildir. Vergi/şirket türünü mali müşavirle,
mesafeli satış ve tüketici metinlerini hukuk uzmanıyla; sağlayıcı kabul
belgelerini de ödeme kuruluşuyla teyit edin. Gerçek işletme bilgileri olmadan
örnek metinleri tamamlanmış yasal sayfa olarak yayımlamayın.

## 5. Staging kabul/test planı

Test ortamında gerçek kart veya gerçek müşteri verisi kullanmayın; sağlayıcının
resmî sandbox test bilgilerini kullanın. Her testin sipariş numarasını, test
gateway yanıtını ve beklenen/gerçek sipariş durumunu kaydedin.

| Senaryo | Beklenen sonuç |
|---|---|
| Değişken ürün: beden ve renk seçimi | Varyasyon seçilmeden sepete ekleme engellenir; sipariş özeti ve e-posta doğru varyasyonu gösterir. |
| Stokta olmayan/son adet varyasyonu | Stoksuz varyasyon satın alınamaz; eşzamanlı son adet siparişlerinde stok taşması oluşmaz. |
| Sepet miktarını değiştirme | Sunucu stok ve satın alma sınırını yeniden kontrol eder; satır ve toplam güncellenir. |
| Yüzde/tutar/ürün kuponu, minimum harcama ve kullanım sınırı | Yalnızca geçerli kupon uygulanır; sepet ve vergi/kargo toplamları yeniden hesaplanır. |
| Sepet veya URL üzerinden sahte fiyat gönderme | Ödenecek tutar katalog ve sunucu sepetinden hesaplanır; gönderilmiş keyfî fiyat kabul edilmez. |
| Farklı teslimat illeri/posta kodları | Doğru WooCommerce gönderim bölgesi, yöntem ve ücret seçilir; bölge bulunamazsa kargo yöntemi uydurulmaz. |
| Ücretsiz kargo eşiğinin altı/üstü | Mağaza koşuluna göre ücret veya ücretsiz yöntem gösterilir; kupon koşulu varsa ayrıca sınanır. |
| Vergi adresi ve ürün vergi sınıfı | Muhasebecinin onayladığı oran/yuvarlama/adres esası sepet ve sipariş satırında tutarlıdır. |
| Misafir checkout ve eksik/hatalı adres | Hesap zorunlu olmaz; required alan hataları açıklanır; geçersiz gönderim adresinde sipariş tamamlanmaz. |
| Sandbox başarılı ödeme | Yalnızca sağlayıcı doğrulamasıyla uygun sipariş durumu, stok güncellemesi ve müşteri/yönetici e-postaları oluşur. |
| Sandbox reddedilen, iptal edilen veya zaman aşımı ödemesi | Sipariş başarısız/beklemede kalır; başarılı sipariş/e-posta durumu verilmez; tekrar ödeme akışı kontrol edilir. |
| Sağlayıcı dönüş callback/webhook tekrarı veya yanlış imza | İmza doğrulaması geçmeyen bildirim siparişi ödenmiş yapmaz; yinelenen bildirim çift tahsil/çift stok işlemi üretmez. |
| Sipariş e-postası | Müşteri ve yönetici alıcısı, sipariş özeti, varyasyon, tutar ve ödeme durumu doğru; test siparişi açıkça sandbox/test olarak ayrıdır. |

Canlıya geçmeden önce sandbox anahtarlarını çıkarın, sağlayıcının canlı bilgilerini
ayrı ortamda girin, tek bir kontrollü düşük riskli canlı işlem yapıp iade edin ve
mutabakat, e-posta, stok ve sağlayıcı panelini doğrulayın. Bu kontrollü canlı
işlem de gerçek tahsilat olduğundan işletme onayı olmadan yapılmamalıdır.

## Kaynaklar

- [WooCommerce genel ayarlar ve ödeme, vergi, gönderim bölümleri](https://woocommerce.com/document/configuring-woocommerce-settings/)
- [WooCommerce vergi yapılandırma rehberi](https://woocommerce.com/document/setting-up-taxes-in-woocommerce/)
- [WooCommerce misafir ödeme ve hesap/gizlilik ayarları](https://woocommerce.com/document/configuring-woocommerce-settings/accounts-and-privacy/)
- [iyzico WooCommerce eklentisi](https://wordpress.org/plugins/iyzico-woocommerce/)
- [PayTR WooCommerce iFrame API eklentisi](https://wordpress.org/plugins/paytr-sanal-pos-woocommerce-iframe-api/)
- [PayTR SSS](https://www.paytr.com/sss)
- [Param resmî duyurusu](https://param.com.tr/)
