<?php

namespace Database\Seeders;

use App\Enums\CurrencyEnum;
use App\Enums\DemoType;
use App\Enums\ProductStatus;
use App\Enums\SectionType;
use App\Models\LandingPage;
use App\Models\Product;
use App\Models\User;
use App\Services\Pages\PageBuilder;
use App\Services\Pages\PagePublisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Builds the complete "Sleep Light" demo: product, a published landing page
 * with every section filled, three offers, testimonials, trust items and FAQ.
 * Placeholder images are generated locally with GD (no external URLs).
 */
class DemoLandingPageSeeder extends Seeder
{
    public function run(PageBuilder $builder, PagePublisher $publisher): void
    {
        if (! Schema::hasTable('landing_pages') || ! Schema::hasTable('products')) {
            return;
        }

        if (Product::where('slug', 'sleep-light')->exists()) {
            return; // already seeded
        }

        $admin = User::orderBy('id')->first();

        $mainImage = $this->placeholder('demo/sleep-light-main.png', 640, 640, [79, 70, 229], 'Sleep Light');
        $problemImg = $this->placeholder('demo/problem.png', 640, 400, [15, 23, 42], 'الأرق');
        $beforeImg = $this->placeholder('demo/before.png', 600, 600, [71, 85, 105], 'قبل');
        $afterImg = $this->placeholder('demo/after.png', 600, 600, [99, 102, 241], 'بعد');

        $product = Product::create([
            'name' => 'Sleep Light — ضوء النوم',
            'slug' => 'sleep-light',
            'sku' => 'SL-001',
            'description' => 'ضوء ليلي ذكي يساعدك على النوم بسرعة عبر إضاءة هادئة تحاكي غروب الشمس.',
            'base_price' => 89.00,
            'compare_at_price' => 149.00,
            'currency' => CurrencyEnum::ILS,
            'status' => ProductStatus::Active,
            'main_image' => $mainImage,
        ]);

        $page = $builder->createForProduct($product, 'ضوء النوم — حملة إطلاق', $admin?->id);
        $page->update([
            'slug' => 'sleep-light',
            'title' => 'ضوء النوم — نم خلال دقائق',
            'meta_description' => 'ضوء ليلي ذكي يساعدك على النوم بسرعة. الدفع عند الاستلام وتوصيل سريع.',
            'og_title' => 'ضوء النوم',
            'og_description' => 'نم بسرعة مع إضاءة تحاكي غروب الشمس.',
            'options' => [
                ['name' => 'צבע', 'choices' => ['לבן', 'שחור', 'זהב']],
            ],
        ]);

        $this->fillSections($page, compact('mainImage', 'problemImg', 'beforeImg', 'afterImg'));
        $this->fillOffers($page);
        $this->fillTestimonials($page);
        $this->fillFaqs($page);

        $publisher->publish($page->fresh());
    }

    private function fillSections(LandingPage $page, array $img): void
    {
        $map = [
            SectionType::Hero->value => [
                'badge' => 'الأكثر مبيعًا هذا الأسبوع',
                'headline' => 'نم خلال دقائق مع',
                'highlight' => 'ضوء النوم',
                'subtitle' => 'إضاءة هادئة تحاكي غروب الشمس لتهدئة عقلك وجسمك.',
                'cta_text' => 'اطلب الآن',
                'delivery_text' => 'الدفع عند الاستلام · توصيل خلال 48 ساعة',
                'show_price' => true,
                'main_image' => $img['mainImage'],
            ],
            SectionType::Problem->value => [
                'title' => 'هل تعاني من هذا كل ليلة؟',
                'subtitle' => 'الأرق يسرق طاقتك وصحتك.',
                'image' => $img['problemImg'],
                'items' => [
                    ['icon' => '😖', 'title' => 'تقلّب لساعات', 'description' => 'تبقى مستيقظًا رغم التعب.'],
                    ['icon' => '📱', 'title' => 'ضوء الشاشة', 'description' => 'يبقي عقلك متيقظًا.'],
                    ['icon' => '😴', 'title' => 'استيقاظ متعب', 'description' => 'تبدأ يومك دون طاقة.'],
                ],
            ],
            SectionType::Demo->value => [
                'title' => 'الفرق واضح',
                'subtitle' => 'اسحب لترى الفرق قبل وبعد.',
                'demo_type' => DemoType::BeforeAfter->value,
                'before_image' => $img['beforeImg'],
                'after_image' => $img['afterImg'],
            ],
            SectionType::Benefits->value => [
                'title' => 'لماذا ضوء النوم؟',
                'items' => [
                    ['icon' => '🌅', 'title' => 'محاكاة الغروب', 'description' => 'إضاءة دافئة تهدّئ الأعصاب.'],
                    ['icon' => '⏱️', 'title' => 'مؤقت ذكي', 'description' => 'ينطفئ تلقائيًا بعد نومك.'],
                    ['icon' => '🔋', 'title' => 'بطارية تدوم', 'description' => 'حتى 20 ساعة بشحنة واحدة.'],
                    ['icon' => '🤫', 'title' => 'صامت تمامًا', 'description' => 'بلا أي إزعاج.'],
                ],
            ],
            SectionType::Testimonials->value => ['title' => 'عملاء سعداء', 'subtitle' => 'آراء حقيقية من مستخدمين.'],
            SectionType::Offers->value => ['title' => 'اختر عرضك', 'subtitle' => 'كلما زادت الكمية زاد التوفير.'],
            SectionType::Trust->value => [
                'title' => 'اطمئن، نحن معك',
                'faq_title' => 'الأسئلة الشائعة',
                'items' => [
                    ['icon' => '💵', 'title' => 'الدفع عند الاستلام', 'description' => ''],
                    ['icon' => '🚚', 'title' => 'توصيل سريع', 'description' => ''],
                    ['icon' => '🔄', 'title' => 'استبدال سهل', 'description' => ''],
                    ['icon' => '📞', 'title' => 'دعم متواصل', 'description' => ''],
                ],
            ],
            SectionType::FinalCta->value => [
                'headline' => 'انتهت الـ45 ثانية. هل تريده؟',
                'subtitle' => 'اطلب الآن وادفع عند الاستلام.',
                'cta_text' => 'اطلب ضوء النوم',
            ],
        ];

        foreach ($page->sections as $section) {
            $type = $section->type->value;
            if (isset($map[$type])) {
                $section->update(['settings' => array_merge($section->settings ?? [], $map[$type])]);
            }
        }
    }

    private function fillOffers(LandingPage $page): void
    {
        $page->offers()->createMany([
            ['name' => 'قطعة واحدة', 'quantity' => 1, 'price' => 89, 'compare_at_price' => 149, 'is_default' => false, 'is_active' => true, 'sort_order' => 1],
            ['name' => 'قطعتان', 'quantity' => 2, 'price' => 149, 'compare_at_price' => 298, 'badge_text' => 'الأكثر طلبًا', 'is_default' => true, 'is_active' => true, 'sort_order' => 2],
            ['name' => 'ثلاث قطع', 'quantity' => 3, 'price' => 199, 'compare_at_price' => 447, 'badge_text' => 'أفضل قيمة', 'is_default' => false, 'is_active' => true, 'sort_order' => 3],
        ]);
    }

    private function fillTestimonials(LandingPage $page): void
    {
        $page->testimonials()->createMany([
            ['customer_name' => 'سارة أحمد', 'rating' => 5, 'text' => 'أنام خلال دقائق الآن! الإضاءة مريحة جدًا.', 'is_verified' => true, 'is_active' => true, 'sort_order' => 1],
            ['customer_name' => 'محمد خالد', 'rating' => 5, 'text' => 'أفضل شراء هذا العام. أطفالي يحبونه.', 'is_verified' => true, 'is_active' => true, 'sort_order' => 2],
            ['customer_name' => 'ليان f.', 'rating' => 4, 'text' => 'جودة ممتازة ووصل بسرعة.', 'is_verified' => false, 'is_active' => true, 'sort_order' => 3],
        ]);
    }

    private function fillFaqs(LandingPage $page): void
    {
        $page->faqs()->createMany([
            ['question' => 'هل الدفع عند الاستلام؟', 'answer' => 'نعم، تدفع نقدًا عند وصول الطلب إليك.', 'is_active' => true, 'sort_order' => 1],
            ['question' => 'كم يستغرق التوصيل؟', 'answer' => 'من 24 إلى 48 ساعة داخل المدن الرئيسية.', 'is_active' => true, 'sort_order' => 2],
            ['question' => 'هل يمكن الاستبدال؟', 'answer' => 'نعم، خلال 7 أيام إذا كان هناك أي عيب.', 'is_active' => true, 'sort_order' => 3],
        ]);
    }

    /**
     * Generate a simple labeled placeholder PNG on the public disk (idempotent).
     */
    private function placeholder(string $path, int $w, int $h, array $rgb, string $label): string
    {
        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        if (! function_exists('imagecreatetruecolor')) {
            return $path; // GD unavailable — return path anyway (URL just 404s locally)
        }

        $im = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($im, 0, 0, $w, $h, $bg);
        $white = imagecolorallocate($im, 255, 255, 255);
        $fontSize = 5;
        $tw = imagefontwidth($fontSize) * strlen($label);
        imagestring($im, $fontSize, (int) (($w - $tw) / 2), (int) ($h / 2 - 8), $label, $white);

        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        imagedestroy($im);

        Storage::disk('public')->put($path, $data);

        return $path;
    }
}
