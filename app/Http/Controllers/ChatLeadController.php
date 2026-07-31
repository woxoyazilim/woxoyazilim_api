<?php
namespace App\Http\Controllers;

use App\Models\ChatLead;
use App\Services\LeadMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatLeadController extends Controller
{
    public function __construct(
        protected LeadMailService $leadMailService,
    ) {}

    public function index() { return response()->json(['chatLeads' => ChatLead::orderByDesc('created_at')->get()]); }

    public function store(Request $request)
    {
        $lead = ChatLead::create(['id' => $request->id ?: 'lead-' . time(), 'name' => $request->name, 'email' => $request->email, 'phone' => $request->phone, 'messages' => $request->messages, 'source' => $request->source ?: 'chatbot']);

        // E-posta bildirimi (hata olsa bile kayıt korunur)
        $this->leadMailService->sendChatLead($lead);
        return response()->json(['success' => true, 'chatLead' => $lead]);
    }

    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
Sen WOXO Yazılım'ın AI danışmanısın. Adın "WOXO AI". Türkçe konuşuyorsun. Profesyonel, samimi ve yardımsever bir üslupla yanıt ver. Kısa ve öz cevaplar ver — uzun paragraflar yazma.

## WOXO YAZILIM HAKKINDA
- Kuruluş: 2014, 10+ yıl deneyim
- Ofisler: İstanbul, Hatay (Antakya), Dubai
- Ekip: 20+ yazılım mühendisi
- Tamamlanan proje: 150+, Aktif müşteri: 110+
- Web: woxoyazilim.com
- Telefon: 0531 254 71 51 / 0535 813 14 63
- WhatsApp: wa.me/905312547151

## HİZMETLER VE FİYATLAR

### 1. Yazılım Programlama
- **ERP Yazılımı**: ₺50.000'den başlayan — Finans, stok, üretim planlama, satın alma modülleri
- **CRM Sistemi**: ₺30.000'den başlayan — Müşteri yönetimi, satış takibi, raporlama
- **MRP Sistemleri**: ₺40.000'den başlayan — Malzeme planlama, üretim çizelgeleme
- **İnsan Kaynakları (HRM)**: ₺25.000'den başlayan — Bordro, izin takibi, performans
- **Özel Yazılım Geliştirme**: ₺75.000'den başlayan — Sıfırdan, işinize %100 uyumlu

### 2. Web Sitesi Çözümleri
- **Kurumsal Web Sitesi**: ₺15.000'den başlayan
- **E-Ticaret Platformu**: ₺35.000'den başlayan — Trendyol, N11, Hepsiburada entegrasyonu
- **B2B E-Ticaret**: ₺45.000'den başlayan
- **SaaS Platformu**: ₺60.000'den başlayan
- **Pazaryeri Entegrasyonu**: ₺20.000'den başlayan
- **Ödeme Entegrasyonu** (iyzico, PayTR, Stripe): ₺10.000'den başlayan

### 3. Mobil Uygulama
- **Flutter / React Native Uygulama**: ₺40.000'den başlayan
- **E-Ticaret Mobil**: ₺35.000'den başlayan
- **B2B Mobil**: ₺45.000'den başlayan
- **Kurumsal Mobil**: ₺55.000'den başlayan

### 4. Hosting & Sunucu
- **VDS Sunucu**: ₺500/ay
- **VPS Sunucu**: ₺200/ay
- **Cloud Hosting**: ₺1.000/ay

### 5. Kurumsal Mail
- **Google Workspace**: ₺150/kullanıcı/ay
- **Microsoft 365**: ₺200/kullanıcı/ay

## REFERANSLAR (Öne Çıkanlar)
- Morgans Realty (Dubai) — Lüks gayrimenkul portalı
- Sobha Realty (Dubai) — Uluslararası property portal
- Artem Office — Ofis mobilyaları web + SEO
- Alamen Taşımacılık — Kurumsal web + SEO
- Temmer Marble — Mermer sektörü web + SEO
- VASS Yetkili Servis — Otomotiv (VW, Porsche, Seat) web
- Marskim — İnşaat malzemeleri e-ticaret
- Yaşampark Rehabilitasyon — Sağlık web + randevu sistemi
- ANC Yönetim — Danışmanlık kurumsal web

## TEKNOLOJİLER
Frontend: React, Next.js, Flutter, React Native
Backend: Node.js, Laravel, Go
Veritabanı: MySQL, PostgreSQL, MongoDB
Cloud: AWS, Google Cloud, Vercel
Entegrasyon: REST API, GraphQL, WebSocket

## İŞ AKIŞI (Müşteriyi bu sırayla yönlendir)
1. **İhtiyaç Analizi**: Müşterinin ne istediğini anla, sektörünü öğren
2. **Çözüm Önerisi**: Uygun hizmeti ve tahmini fiyat aralığını söyle
3. **Demo / Referans**: Benzer projeleri göster, demo teklif et
4. **İletişim**: Detaylı görüşme için iletişim bilgilerini iste veya WhatsApp yönlendir
5. **Ücretsiz Analiz**: "Ücretsiz proje analizi" teklif et

## KURALLAR
- Fiyatları "...den başlayan fiyatlarla" şeklinde ver, kesin fiyat verme
- Rakip firmaları kötüleme, sadece WOXO'nun güçlü yönlerini vurgula
- Müşteri iletişim bilgisi paylaşırsa teşekkür et ve "ekibimiz en kısa sürede dönüş yapacak" de
- Bilmediğin konularda "Bu konuda detaylı bilgi için ekibimizle doğrudan görüşmenizi öneririm" de
- Her zaman bir sonraki adımı öner (demo, arama, WhatsApp)
- Emoji kullan ama abartma — profesyonel kal
- Cevapları kısa tut, 2-3 paragrafı geçme
PROMPT;
    }

    public function message(Request $request)
    {
        $userMessage = $request->input('message', '');
        $conversationHistory = $request->input('history', []);
        $messageCount = $request->input('messageCount', 1);

        // Önce Gemini API dene, başarısızsa akıllı fallback
        $aiResponse = $this->tryGemini($userMessage, $conversationHistory);

        if (!$aiResponse) {
            $aiResponse = $this->smartFallback($userMessage, $messageCount);
        }

        $quickReplies = $this->getQuickReplies($userMessage, $messageCount);
        $showContactForm = $messageCount >= 3;

        return response()->json([
            'response' => $aiResponse,
            'quickReplies' => $quickReplies,
            'showContactForm' => $showContactForm,
        ]);
    }

    private function tryGemini(string $userMessage, array $conversationHistory): ?string
    {
        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if (!$apiKey) return null;

        $contents = [];
        if (is_array($conversationHistory)) {
            foreach ($conversationHistory as $msg) {
                $role = ($msg['role'] ?? '') === 'user' ? 'user' : 'model';
                $text = $msg['content'] ?? $msg['text'] ?? '';
                if ($text) {
                    $contents[] = ['role' => $role, 'parts' => [['text' => $text]]];
                }
            }
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

        try {
            $response = Http::timeout(15)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key={$apiKey}",
                [
                    'system_instruction' => ['parts' => [['text' => $this->getSystemPrompt()]]],
                    'contents' => $contents,
                    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 500, 'topP' => 0.9],
                ]
            );

            if (!$response->successful()) {
                Log::warning('Gemini API failed, using fallback', ['status' => $response->status()]);
                return null;
            }

            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            Log::warning('Gemini exception, using fallback', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * WOXO'yu tanıyan akıllı fallback sistemi
     * Gemini çalışmasa bile müşteriyi profesyonelce yönlendirir
     */
    private function smartFallback(string $message, int $messageCount): string
    {
        $msg = mb_strtolower(trim($message));

        // === SELAMLAŞMA ===
        if ($this->matchesWord($msg, ['merhaba', 'selam', 'meraba', 'selamlar', 'hey', 'hi', 'hello', 'slm', 'mrb']) || $this->matches($msg, ['günaydın', 'iyi günler', 'iyi akşamlar'])) {
            return "Merhaba! 👋 WOXO Yazılım'a hoş geldiniz.\n\nBiz **10+ yıldır** İstanbul, Hatay ve Dubai ofislerimizle **ERP, CRM, web sitesi, mobil uygulama** ve **özel yazılım** projeleri geliştiriyoruz. 150'den fazla tamamlanmış projemiz var.\n\nSize nasıl yardımcı olabilirim? Projenizi kısaca anlatırsanız hemen yönlendirebilirim! 💡";
        }

        // === ERP ===
        if ($this->matches($msg, ['erp', 'kurumsal kaynak', 'kaynak planlama', 'enterprise'])) {
            return "🏢 **ERP Yazılımı** konusunda 10+ yıllık deneyimimiz var!\n\nERP çözümümüz şu modülleri kapsar:\n• **Finans & Muhasebe** — ₺15.000'den başlayan\n• **Stok & Depo Yönetimi** — ₺12.000'den başlayan\n• **Üretim Planlama** — ₺18.000'den başlayan\n• **Satın Alma & Tedarik** — ₺10.000'den başlayan\n\nKomple ERP paketi **₺50.000'den başlayan** fiyatlarla. İşletmenize özel modüller eklenebilir.\n\nÜcretsiz demo ve ihtiyaç analizi için bize ulaşın! 📱 wa.me/905312547151";
        }

        // === CRM ===
        if ($this->matches($msg, ['crm', 'müşteri yönetim', 'müşteri ilişki', 'musteri yonetim', 'satış takip'])) {
            return "📊 **CRM Sistemi** ile müşteri ilişkilerinizi profesyonelce yönetin!\n\n• Müşteri kartları ve iletişim geçmişi\n• Satış pipeline ve fırsat takibi\n• Otomatik hatırlatma ve görev yönetimi\n• Detaylı raporlama ve analitik\n\n**₺30.000'den başlayan** fiyatlarla, tamamen size özel.\n\nDemo görmek ister misiniz? 📱 wa.me/905312547151";
        }

        // === WEB SİTESİ ===
        if ($this->matches($msg, ['web site', 'website', 'web tasarım', 'kurumsal site', 'internet sitesi', 'sayfa', 'landing'])) {
            return "🌐 **Web Sitesi Çözümlerimiz:**\n\n• **Kurumsal Web Sitesi** — ₺15.000'den başlayan\n• **E-Ticaret Platformu** — ₺35.000'den başlayan\n• **B2B E-Ticaret** — ₺45.000'den başlayan\n• **SaaS Platformu** — ₺60.000'den başlayan\n\nReact, Next.js gibi modern teknolojilerle, SEO uyumlu ve mobil responsive tasarımlar yapıyoruz.\n\nReferanslarımızı görmek için: woxoyazilim.com/referanslar\n\nProjenizi konuşalım! 📱 wa.me/905312547151";
        }

        // === E-TİCARET ===
        if ($this->matches($msg, ['e-ticaret', 'eticaret', 'e ticaret', 'online satış', 'online mağaza', 'trendyol', 'n11', 'hepsiburada', 'pazaryeri', 'marketplace'])) {
            return "🛒 **E-Ticaret Çözümlerimiz:**\n\n• **E-Ticaret Platformu** — ₺35.000'den başlayan\n• **B2B E-Ticaret** — ₺45.000'den başlayan\n• **Pazaryeri Entegrasyonu** (Trendyol, N11, Hepsiburada) — ₺20.000'den başlayan\n• **Ödeme Entegrasyonu** (iyzico, PayTR, Stripe) — ₺10.000'den başlayan\n\nSıfırdan veya mevcut sisteminize entegre çözümler üretiyoruz.\n\nÜcretsiz analiz için: 📱 wa.me/905312547151";
        }

        // === MOBİL UYGULAMA ===
        if ($this->matches($msg, ['mobil', 'uygulama', 'app', 'flutter', 'react native', 'ios', 'android', 'telefon uygulama'])) {
            return "📱 **Mobil Uygulama Geliştirme:**\n\n• **Flutter Uygulama** — ₺40.000'den başlayan (iOS + Android tek kodla)\n• **React Native** — ₺40.000'den başlayan\n• **E-Ticaret Mobil** — ₺35.000'den başlayan\n• **B2B Mobil** — ₺45.000'den başlayan\n• **Kurumsal Mobil** — ₺55.000'den başlayan\n\nHem iOS hem Android'de çalışan, performanslı uygulamalar geliştiriyoruz.\n\nProjenizi anlatalım! 📱 wa.me/905312547151";
        }

        // === SÜRE === (fiyattan önce kontrol — "ne kadar süre" fiyata düşmesin)
        if ($this->matches($msg, ['süre', 'sure', 'surec', 'süreç', 'teslim', 'ne zaman', 'deadline', 'kaç hafta', 'kaç ay', 'kaç gün']) || ($this->matches($msg, ['ne kadar']) && $this->matches($msg, ['süre', 'sure', 'teslim', 'zaman']))) {
            return "⏱️ **Tahmini proje süreleri:**\n\n• Kurumsal Web Sitesi: **2-4 hafta**\n• E-Ticaret: **4-8 hafta**\n• Mobil Uygulama: **6-12 hafta**\n• CRM Sistemi: **4-8 hafta**\n• ERP Yazılımı: **8-16 hafta**\n• Özel Yazılım: **Kapsama göre değişir**\n\nSüre, projenin kapsamına ve özellik sayısına göre netleşir. Ücretsiz analiz sonrası kesin timeline veririz.\n\n📱 wa.me/905312547151";
        }

        // === FİYAT ===
        if ($this->matches($msg, ['fiyat', 'ücret', 'maliyet', 'kaç para', 'ne kadar', 'bütçe', 'teklif', 'fiyatlandırma', 'paket'])) {
            return "💰 **Fiyat bilgisi** proje kapsamına göre değişmektedir. Genel fiyat aralıkları:\n\n• Kurumsal Web Sitesi: **₺15.000**'den başlayan\n• E-Ticaret: **₺35.000**'den başlayan\n• Mobil Uygulama: **₺40.000**'den başlayan\n• CRM Sistemi: **₺30.000**'den başlayan\n• ERP Yazılımı: **₺50.000**'den başlayan\n• Özel Yazılım: **₺75.000**'den başlayan\n\nDetaylı ve projenize özel teklif almak için bize ulaşın — **ücretsiz analiz** yapıyoruz! 📱 wa.me/905312547151";
        }

        // === REFERANS ===
        if ($this->matches($msg, ['referans', 'portfolio', 'portföy', 'örnek', 'çalışma', 'yaptığınız'])) {
            return "🏆 **Öne çıkan referanslarımız:**\n\n🇦🇪 **Morgans Realty** (Dubai) — Lüks gayrimenkul portalı\n🇦🇪 **Sobha Realty** (Dubai) — Uluslararası property portal\n🏢 **Artem Office** — Ofis mobilyaları web + SEO\n🚚 **Alamen Taşımacılık** — Kurumsal web + SEO\n🪨 **Temmer Marble** — Mermer sektörü web + SEO\n🚗 **VASS Yetkili Servis** — Otomotiv web\n🏗️ **Marskim** — İnşaat malzemeleri e-ticaret\n🏥 **Yaşampark** — Sağlık web + randevu sistemi\n\nTüm referanslarımız: **woxoyazilim.com/referanslar**\n\n150+ tamamlanmış projemiz var! Sektörünüze özel örnekler gösterebiliriz.";
        }

        // === HAKKIMIZDA / KİMSİNİZ ===
        if ($this->matches($msg, ['hakkınızda', 'kimsiniz', 'siz kimsiniz', 'firma', 'şirket', 'woxo ne', 'ne yapıyorsunuz', 'tanıt', 'hakkında'])) {
            return "🚀 **WOXO Yazılım** — 2014'ten beri dijital çözümler üretiyoruz.\n\n• 📍 **Ofisler:** İstanbul, Hatay (Antakya), Dubai\n• 👥 **Ekip:** 20+ yazılım mühendisi\n• ✅ **150+** tamamlanmış proje\n• 🤝 **110+** aktif müşteri\n• 🌍 **10+** yıl deneyim\n\nERP, CRM, web sitesi, mobil uygulama ve özel yazılım geliştirme konularında uzmanız. Dubai'den Türkiye'ye uzanan global bir portföyümüz var.\n\nDetaylı bilgi: **woxoyazilim.com**";
        }

        // === HOSTİNG / SUNUCU === (hizmetlerden önce — "hosting hizmetiniz" doğru bloğa düşsün)
        if ($this->matches($msg, ['hosting', 'sunucu', 'server', 'vds', 'vps', 'cloud hosting', 'barındırma'])) {
            return "☁️ **Hosting & Sunucu Çözümlerimiz:**\n\n• **VPS Sunucu** — ₺200/ay'dan başlayan\n• **VDS Sunucu** — ₺500/ay'dan başlayan\n• **Cloud Hosting** — ₺1.000/ay'dan başlayan\n\n7/24 teknik destek, günlük yedekleme ve yüksek uptime garantisi sunuyoruz.\n\nMevcut sitenizi taşımak veya yeni proje için: 📱 wa.me/905312547151";
        }

        // === HİZMETLER ===
        if ($this->matches($msg, ['hizmet', 'servis', 'ne yapıyor', 'neler yapıyor', 'çözüm', 'ürün'])) {
            return "⚡ **WOXO Yazılım Hizmetleri:**\n\n**1. Yazılım Programlama**\n→ ERP, CRM, MRP, HRM, Özel Yazılım\n\n**2. Web Sitesi Çözümleri**\n→ Kurumsal site, E-ticaret, B2B, SaaS\n\n**3. Mobil Uygulama**\n→ Flutter, React Native, iOS & Android\n\n**4. Hosting & Sunucu**\n→ VDS, VPS, Cloud Hosting\n\n**5. Kurumsal Mail**\n→ Google Workspace, Microsoft 365\n\nHangi hizmet ilginizi çekiyor? Size özel bilgi verebilirim! 💡";
        }

        // === İLETİŞİM ===
        if ($this->matches($msg, ['iletişim', 'telefon', 'numara', 'arayın', 'arayabilir', 'ulaş', 'whatsapp', 'adres', 'neredesiniz', 'ofis'])) {
            return "📞 **Bize hemen ulaşın:**\n\n📱 **WhatsApp:** wa.me/905312547151\n📞 **Telefon:** 0531 254 71 51 / 0535 813 14 63\n🌐 **Web:** woxoyazilim.com\n\n📍 **Ofislerimiz:**\n• İstanbul\n• Hatay (Antakya)\n• Dubai\n\nWhatsApp üzerinden anında dönüş yapıyoruz! Projenizi yazın, hemen değerlendirelim. 🤝";
        }

        // === DEMO ===
        if ($this->matches($msg, ['demo', 'deneme', 'göster', 'test', 'dene'])) {
            return "🎯 **Ücretsiz demo** için hemen randevu alabilirsiniz!\n\nSize özel canlı demo sunumunda:\n• İhtiyaçlarınızı birlikte analiz ederiz\n• Benzer projelerimizi gösteririz\n• Tahmini süre ve bütçe bilgisi veririz\n\nDemo randevusu için:\n📱 **WhatsApp:** wa.me/905312547151\n📞 **Telefon:** 0531 254 71 51\n\nEn kısa sürede dönüş yapıyoruz! ⚡";
        }

        // === SEO ===
        if ($this->matches($msg, ['seo', 'google', 'arama motoru', 'optimizasyon', 'dijital pazarlama'])) {
            return "🔍 **SEO & Dijital Pazarlama** hizmetlerimiz web projelerimizin bir parçasıdır.\n\n• Teknik SEO optimizasyonu\n• İçerik stratejisi\n• Google sıralama takibi\n• Performans raporlama\n\nWeb sitesi projelerinizde SEO altyapısını sıfırdan kuruyoruz. Alamen, Temmer Marble gibi referanslarımızda SEO başarısını görebilirsiniz.\n\nDetaylı bilgi: 📱 wa.me/905312547151";
        }

        // === MAIL ===
        if ($this->matches($msg, ['mail', 'e-posta', 'eposta', 'workspace', 'microsoft 365', 'outlook', 'gmail'])) {
            return "📧 **Kurumsal Mail Çözümleri:**\n\n• **Google Workspace** — ₺150/kullanıcı/ay\n• **Microsoft 365** — ₺200/kullanıcı/ay\n\nProfesyonel @sirketiniz.com uzantılı mail adresleri, takvim paylaşımı, dosya depolama ve ekip iş birliği araçları dahil.\n\nKurulum için: 📱 wa.me/905312547151";
        }

        // === TEKNOLOJİ ===
        if ($this->matches($msg, ['teknoloji', 'hangi dil', 'programlama dili', 'react', 'next', 'node', 'laravel', 'go', 'golang', 'altyapı'])) {
            return "⚙️ **Kullandığımız Teknolojiler:**\n\n**Frontend:** React, Next.js, Flutter, React Native\n**Backend:** Node.js, Laravel, Go\n**Veritabanı:** MySQL, PostgreSQL, MongoDB\n**Cloud:** AWS, Google Cloud, Vercel\n**Entegrasyon:** REST API, GraphQL, WebSocket\n\nProjenize en uygun teknolojiyi birlikte belirleriz. Her projede modern, ölçeklenebilir mimari kullanıyoruz. 🏗️";
        }

        // === TEŞEKKÜR ===
        if ($this->matches($msg, ['teşekkür', 'sağol', 'sağ ol', 'eyvallah', 'tşk', 'thanks'])) {
            return "Rica ederim! 😊 WOXO Yazılım olarak her zaman yanınızdayız.\n\nBaşka bir sorunuz olursa çekinmeden yazın. Projeniz için konuşmak isterseniz:\n\n📱 **WhatsApp:** wa.me/905312547151\n📞 **Telefon:** 0531 254 71 51\n\nGörüşmek üzere! 🤝";
        }

        // === EVET / OLUMLU ===
        if ($this->matches($msg, ['evet', 'olur', 'tamam', 'isterim', 'istiyorum', 'tabii', 'tabi'])) {
            return "Harika! 🎯 En iyi şekilde yardımcı olmak istiyoruz.\n\nProjeniz hakkında birkaç detay paylaşabilir misiniz?\n• Hangi sektördesiniz?\n• Ne tür bir çözüme ihtiyacınız var?\n• Tahmini bütçe ve zaman planınız var mı?\n\nYa da doğrudan ekibimizle konuşun:\n📱 **WhatsApp:** wa.me/905312547151";
        }

        // === GENEL FALLBACK ===
        $fallbacks = [
            "İlginiz için teşekkürler! 😊 Size en uygun çözümü sunabilmemiz için projenizi biraz daha detaylı anlatır mısınız?\n\n**Web sitesi, mobil uygulama, ERP, CRM** veya **özel yazılım** — hangi alanda ihtiyacınız var?\n\nDoğrudan konuşmak isterseniz: 📱 wa.me/905312547151",
            "WOXO Yazılım olarak size en iyi şekilde yardımcı olmak istiyoruz! 💡\n\nBize şunları söylerseniz hemen yönlendirebilirim:\n• Hangi sektördesiniz?\n• Ne tür bir yazılım/web/mobil çözüme ihtiyacınız var?\n\nYa da ekibimize doğrudan yazın: 📱 wa.me/905312547151",
            "Sorununuz için doğru adrestesiniz! 🚀 WOXO Yazılım olarak **150+ proje** deneyimimizle size özel çözüm üretebiliriz.\n\nHizmetlerimiz: ERP, CRM, Web Sitesi, E-Ticaret, Mobil Uygulama, Özel Yazılım\n\nHangi konuda bilgi almak istersiniz?\n📱 WhatsApp: wa.me/905312547151",
        ];

        return $fallbacks[$messageCount % count($fallbacks)];
    }

    /**
     * Mesajda belirli anahtar kelimelerin geçip geçmediğini kontrol eder (substring)
     */
    private function matches(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, mb_strtolower($keyword))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Tam kelime eşleşmesi (word boundary) — kısa keyword'ler için
     */
    private function matchesWord(string $message, array $keywords): bool
    {
        $words = preg_split('/[\s,.!?;:]+/u', $message);
        foreach ($keywords as $keyword) {
            if (in_array(mb_strtolower($keyword), $words, true)) {
                return true;
            }
        }
        return false;
    }

    private function getQuickReplies(string $message, int $count): array
    {
        $msg = mb_strtolower($message);

        if ($this->matches($msg, ['erp', 'crm', 'mrp', 'hrm', 'yazılım'])) {
            return ['ERP modülleri neler?', 'Demo görebilir miyim?', 'Fiyat bilgisi almak istiyorum'];
        }
        if ($this->matches($msg, ['web', 'site', 'e-ticaret', 'eticaret'])) {
            return ['Referanslarınızı görmek istiyorum', 'E-ticaret fiyatı nedir?', 'Demo talep etmek istiyorum'];
        }
        if ($this->matches($msg, ['mobil', 'uygulama', 'flutter', 'app'])) {
            return ['Flutter mı React Native mi?', 'Mobil uygulama fiyatı?', 'Demo görebilir miyim?'];
        }
        if ($this->matches($msg, ['fiyat', 'ücret', 'maliyet', 'kaç para', 'ne kadar', 'teklif'])) {
            return ['Detaylı teklif almak istiyorum', 'Demo talep etmek istiyorum', 'WhatsApp ile yazışmak istiyorum'];
        }
        if ($this->matches($msg, ['referans', 'örnek', 'portfolio', 'portföy'])) {
            return ['Web sitesi yaptırmak istiyorum', 'Mobil uygulama istiyorum', 'ERP / CRM istiyorum'];
        }
        if ($this->matches($msg, ['merhaba', 'selam', 'meraba', 'hey', 'hi', 'slm'])) {
            return ['Hizmetleriniz neler?', 'Fiyat bilgisi almak istiyorum', 'Referanslarınızı görmek istiyorum'];
        }

        if ($count <= 1) {
            return ['Hizmetleriniz neler?', 'Fiyat bilgisi almak istiyorum', 'Demo talep etmek istiyorum'];
        }
        if ($count <= 3) {
            return ['Detaylı teklif almak istiyorum', 'Referanslarınızı görmek istiyorum', 'WhatsApp ile yazışmak istiyorum'];
        }

        return ['Sizi arayabilir miyiz?', 'WhatsApp ile iletişime geçelim'];
    }
}
