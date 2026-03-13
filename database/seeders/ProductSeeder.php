<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductVariant;
use App\Models\VariantPricing;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private array $mediaFiles = [
        'ph-11134207-7ra0p-mbyt9pf1cl933a@resize_w900_nl.webp',
        'ph-11134207-81zte-mkhw82ixq41yda@resize_w900_nl.webp',
        'ph-11134207-81zte-mkhw82j6i5tubd@resize_w900_nl.webp',
        'ph-11134207-81ztg-mkhw82j27nd13f.webp',
        'ph-11134207-81ztj-mkhw8ak3c6q0e6@resize_w900_nl.webp',
        'ph-11134207-81ztk-mdzj0gpue7ls0e@resize_w900_nl.webp',
        'ph-11134207-81ztk-mkhsew862akl2a@resize_w900_nl.webp',
        'ph-11134207-81ztl-mdzj2034s83k67@resize_w900_nl.webp',
        'ph-11134207-81zto-mkhsshvjv30lb8@resize_w900_nl.webp',
        'ph-11134207-81ztq-mkhw82is6netfd@resize_w900_nl.webp',
    ];

    private string $mediaBase = '/products/';

    private array $sizeDefs = [
        [
            'label'           => '10ml',
            'sort_order'      => 1,
            'retail'          => 99,
            'reseller'        => 85,
            'distributor'     => 75,
            'wholesale'       => 65,
            'stock'           => 200,
            'reorder_point'   => 30,
            'reorder_quantity' => 50,
        ],
        [
            'label'           => '30ml',
            'sort_order'      => 2,
            'retail'          => 350,
            'reseller'        => 299,
            'distributor'     => 270,
            'wholesale'       => 245,
            'stock'           => 150,
            'reorder_point'   => 20,
            'reorder_quantity' => 40,
        ],
        [
            'label'           => '60ml',
            'sort_order'      => 3,
            'retail'          => 389,
            'reseller'        => 335,
            'distributor'     => 305,
            'wholesale'       => 275,
            'stock'           => 80,
            'reorder_point'   => 15,
            'reorder_quantity' => 30,
        ],
    ];

    public function run(): void
    {
        $warehouse = Warehouse::where('is_default', true)->first()
            ?? Warehouse::first();

        $perfumes = Category::where('slug', 'perfumes')->first();
        $getCatId = fn(string $slug) => Category::where('slug', $slug)->first()?->id ?? $perfumes?->id;

        $products = [
            // ── MEN'S — EXTRAIT DE PARFUM ───────────────────────────────────
            [
                'sku'               => 'FRL-SUP-001',
                'name'              => 'SUPREMACY - Feralde Perfume (Ultramale) Extrait de Parfum',
                'slug'              => 'supremacy-ultramale-extrait-de-parfum',
                'short_description' => 'A commanding lavender-vanilla powerhouse inspired by Ultra Male.',
                'description'       => 'SUPREMACY is Feralde\'s homage to the iconic Ultra Male DNA — an addictive opening of pear and bergamot crashing into a heart of lavender and rose, anchored by a sweet, woody vanilla-amber base. Long-lasting Extrait de Parfum concentration. Ideal for date nights and evening wear.',
                'category_id'       => $getCatId('men-fresh-aquatic'),
                'scent_notes'       => ['top' => ['Pear', 'Bergamot', 'Black Currant'], 'heart' => ['Lavender', 'Rose', 'Cardamom'], 'base' => ['Vanilla', 'Amber', 'Sandalwood']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Coumarin, Benzyl Benzoate, Citronellol, Geraniol.',
                'is_active'         => true,
                'is_featured'       => true,
                'is_best_seller'    => true,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-PRF-002',
                'name'              => 'PROFESSOR - Feralde Perfume (BDC) Extrait de Parfum',
                'slug'              => 'professor-bdc-extrait-de-parfum',
                'short_description' => 'Dapper and sophisticated — inspired by Bleu de Chanel.',
                'description'       => 'PROFESSOR channels the refined intelligence of Bleu de Chanel. A crisp citrus opening of grapefruit and lemon transitions into a aromatic-woody heart of ginger and nutmeg, finishing with a clean cedarwood and labdanum base. The perfect office-to-evening Extrait de Parfum.',
                'category_id'       => $getCatId('men-woody-spicy'),
                'scent_notes'       => ['top' => ['Grapefruit', 'Lemon', 'Mint'], 'heart' => ['Ginger', 'Nutmeg', 'Jasmine'], 'base' => ['Cedarwood', 'Labdanum', 'White Musk']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Limonene, Linalool, Citral, Coumarin.',
                'is_active'         => true,
                'is_featured'       => true,
                'is_best_seller'    => true,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-ELX-003',
                'name'              => 'ELIXIR - Feralde Perfume (Sauvage Elixir) Extrait de Parfum',
                'slug'              => 'elixir-sauvage-elixir-extrait-de-parfum',
                'short_description' => 'Raw, magnetic spice inspired by Sauvage Elixir.',
                'description'       => 'ELIXIR is structured around the magnetic rawness of Sauvage Elixir. An explosive head of spicy cardamom and nutmeg leads into a rich heart of ambroxan and licorice, grounded by a deep sandalwood and hawthorn base. An Extrait de Parfum for those who command attention.',
                'category_id'       => $getCatId('men-woody-spicy'),
                'scent_notes'       => ['top' => ['Cardamom', 'Nutmeg', 'Grapefruit'], 'heart' => ['Ambroxan', 'Licorice', 'Hawthorn'], 'base' => ['Sandalwood', 'Vetiver', 'Cedarwood']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Ambroxan, Linalool, Coumarin, Eugenol.',
                'is_active'         => true,
                'is_featured'       => true,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-MGF-004',
                'name'              => 'MANGO FLAME - Feralde Perfume (God of Fire) Extrait de Parfum',
                'slug'              => 'mango-flame-god-of-fire-extrait-de-parfum',
                'short_description' => 'Tropical fire — a bold fruity-spicy Extrait de Parfum.',
                'description'       => 'MANGO FLAME ignites with a volcanic burst of tropical mango and citrus, blazing into a spicy-smoky heart of pepper and oud, leaving a trail of warm amber and musk. A fearless Extrait de Parfum inspired by God of Fire.',
                'category_id'       => $getCatId('men-sweet-gourmand'),
                'scent_notes'       => ['top' => ['Mango', 'Tangerine', 'Bergamot'], 'heart' => ['Black Pepper', 'Cinnamon', 'Oud'], 'base' => ['Amber', 'Musk', 'Patchouli']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Limonene, Citral, Linalool, Benzyl Benzoate.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => true,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-DDG-005',
                'name'              => 'DODGE - Feralde Perfume (Acqua DG Profumo) Extrait de Parfum',
                'slug'              => 'dodge-acqua-dg-profumo-extrait-de-parfum',
                'short_description' => 'Fresh aquatic depth inspired by Acqua di Gio Profumo.',
                'description'       => 'DODGE captures the deep aquatic soul of Acqua di Gio Profumo. A clean opening of bergamot and marine accords dives into a heart of incense and geranium, anchored by a patchouli and musk dry-down. A sophisticated Extrait de Parfum for the modern man.',
                'category_id'       => $getCatId('men-fresh-aquatic'),
                'scent_notes'       => ['top' => ['Bergamot', 'Marine Accord', 'Lemon'], 'heart' => ['Incense', 'Geranium', 'Rosemary'], 'base' => ['Patchouli', 'Musk', 'Labdanum']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Limonene, Linalool, Geraniol, Citronellol.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-YVS-006',
                'name'              => 'YVES - Feralde Perfume (Y EDP) Extrait de Parfum',
                'slug'              => 'yves-y-edp-extrait-de-parfum',
                'short_description' => 'Fresh and sporty elegance inspired by YSL Y EDP.',
                'description'       => 'YVES captures the energetic freshness of YSL Y Eau de Parfum. Apple and bergamot burst on the top, a sage-geranium heart adds character, and a warm base of cedar and ambergris rounds it out. An Extrait de Parfum built for the dynamic, modern gentleman.',
                'category_id'       => $getCatId('men-fresh-aquatic'),
                'scent_notes'       => ['top' => ['Apple', 'Bergamot', 'Ginger'], 'heart' => ['Sage', 'Geranium', 'Juniper'], 'base' => ['Cedarwood', 'Ambergris', 'Vetiver']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Limonene, Geraniol, Linalool, Citronellol.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-HRS-007',
                'name'              => 'HORUS - Feralde Perfume (Eros) Extrait de Parfum',
                'slug'              => 'horus-eros-extrait-de-parfum',
                'short_description' => 'Passionate and intense, inspired by Versace Eros.',
                'description'       => 'HORUS channels the passionate intensity of Versace Eros. A fresh, minty top of lemon and apple leads into a seductive heart of geranium and tonka bean, resting on a sensual base of vanilla and oakmoss. A magnetic Extrait de Parfum for bold personalities.',
                'category_id'       => $getCatId('men-sweet-gourmand'),
                'scent_notes'       => ['top' => ['Mint', 'Lemon', 'Apple'], 'heart' => ['Geranium', 'Tonka Bean', 'Ambroxan'], 'base' => ['Vanilla', 'Vetiver', 'Oakmoss']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Coumarin, Citronellol, Eugenol.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => true,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-EMP-008',
                'name'              => 'EMPEROR - Feralde Perfume (Invictus Aqua) Extrait de Parfum',
                'slug'              => 'emperor-invictus-aqua-extrait-de-parfum',
                'short_description' => 'Fresh ocean power inspired by Paco Rabanne Invictus Aqua.',
                'description'       => 'EMPEROR is a triumphant aquatic Extrait de Parfum inspired by Invictus Aqua. It opens with a cool frosted peppermint and grapefruit explosion, evolves into a watery jasmine heart, and closes with a clean woody-musk base. An unstoppable, refreshing fragrance.',
                'category_id'       => $getCatId('men-fresh-aquatic'),
                'scent_notes'       => ['top' => ['Grapefruit', 'Peppermint', 'Marine'], 'heart' => ['Jasmine', 'Sea Notes', 'Laurel'], 'base' => ['Oakmoss', 'Sandalwood', 'Musk']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Limonene, Linalool, Citral, Geraniol.',
                'is_active'         => true,
                'is_featured'       => true,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-EPR-009',
                'name'              => 'ERBA PURA - Feralde Perfume (Erba Pura) Extrait de Parfum',
                'slug'              => 'erba-pura-extrait-de-parfum',
                'short_description' => 'Sweet citrus paradise inspired by Sospiro Erba Pura.',
                'description'       => 'ERBA PURA is Feralde\'s ode to Sospiro\'s Erba Pura — a joyful, sun-drenched fragrance. It opens with fresh bergamot and mandarin, blooms into a heart of fruity florals, and closes with a warm, sensual musk-amber base. A crowd-pleasing Extrait de Parfum with incredible longevity.',
                'category_id'       => $getCatId('men-sweet-gourmand'),
                'scent_notes'       => ['top' => ['Bergamot', 'Mandarin', 'Sicilian Lemon'], 'heart' => ['White Flowers', 'Fruity Accord', 'Heliotrope'], 'base' => ['White Musk', 'Amber', 'Sandalwood']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Limonene, Linalool, Citral, Benzyl Benzoate.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-BRL-010',
                'name'              => 'BERLIN - Feralde Perfume (Eclat Men) Extrait de Parfum',
                'slug'              => 'berlin-eclat-men-extrait-de-parfum',
                'short_description' => 'Crisp and sophisticated — inspired by Lanvin Eclat d\'Arpege pour Homme.',
                'description'       => 'BERLIN is a pristine, light-fresh Extrait de Parfum inspired by Eclat Men. Black currant and apple set a crisp opening; a floral-woody heart of iris and gardenia blooms through; musk and cedar provide a clean, sophisticated finish. Perfect for everyday wear.',
                'category_id'       => $getCatId('men-strong-intense'),
                'scent_notes'       => ['top' => ['Black Currant', 'Apple', 'Cassis'], 'heart' => ['Iris', 'Gardenia', 'Magnolia'], 'base' => ['Cedarwood', 'White Musk', 'Vetiver']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Limonene, Linalool, Citronellol, Alpha-Isomethyl Ionone.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => true,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-LME-011',
                'name'              => 'LME - Feralde Perfume (Le Male Elixir) Extrait de Parfum',
                'slug'              => 'lme-le-male-elixir-extrait-de-parfum',
                'short_description' => 'Sweet, seductive lavender elixir inspired by Le Male Elixir.',
                'description'       => 'LME distills the seductive essence of Jean Paul Gaultier\'s Le Male Elixir. An addictive opening of lavender and honey leads into a rich heart of vanilla and musk, settling on a warm amber-tonka base. High concentration Extrait de Parfum — a little goes a long way.',
                'category_id'       => $getCatId('men-sweet-gourmand'),
                'scent_notes'       => ['top' => ['Lavender', 'Honey', 'Cinnamon'], 'heart' => ['Vanilla', 'Musk', 'Cardamom'], 'base' => ['Amber', 'Tonka Bean', 'Sandalwood']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Coumarin, Eugenol, Benzyl Benzoate.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-INT-012',
                'name'              => 'INTENSELY - Feralde Perfume (Stronger With You Intensely) Extrait de Parfum',
                'slug'              => 'intensely-stronger-with-you-intensely-extrait-de-parfum',
                'short_description' => 'Warm chestnut sweetness inspired by Stronger With You Intensely.',
                'description'       => 'INTENSELY captures the cozy sweetness of Armani\'s Stronger With You Intensely. Cinnamon and pink pepper ignite a spicy opening; warm chestnut and vanilla weave a gourmand heart; a base of musk and amberwood seals in the intensity. An Extrait de Parfum for cool weather moments.',
                'category_id'       => $getCatId('men-strong-intense'),
                'scent_notes'       => ['top' => ['Pink Pepper', 'Cinnamon', 'Cardamom'], 'heart' => ['Chestnut', 'Vanilla', 'Amber'], 'base' => ['Amberwood', 'White Musk', 'Benzoin']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Coumarin, Benzyl Benzoate, Cinnamal.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-OPL-013',
                'name'              => 'OPULENT - Feralde Perfume (Cool Water) Extrait de Parfum',
                'slug'              => 'opulent-cool-water-extrait-de-parfum',
                'short_description' => 'Cool and refreshing sea breeze inspired by Davidoff Cool Water.',
                'description'       => 'OPULENT refreshes with the legendary aquatic freshness of Cool Water. A wave of cool mint, sea salt, and lavender opens brightly; a woody-aromatic heart of sandalwood and jasmine floats through; a base of musk and cedar completes this timeless marine Extrait de Parfum.',
                'category_id'       => $getCatId('men-fresh-aquatic'),
                'scent_notes'       => ['top' => ['Mint', 'Sea Salt', 'Lavender'], 'heart' => ['Sandalwood', 'Jasmine', 'Oakmoss'], 'base' => ['Musk', 'Cedarwood', 'Tonka Bean']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Coumarin, Limonene, Citronellol.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => true,
            ],
            [
                'sku'               => 'FRL-SEM-014',
                'name'              => 'SEMPITERNAL - Feralde Perfume (Happy Men) Extrait de Parfum',
                'slug'              => 'sempiternal-happy-men-extrait-de-parfum',
                'short_description' => 'Bright and joyful citrus inspired by Clinique Happy for Men.',
                'description'       => 'SEMPITERNAL radiates with the uplifting brightness of Clinique Happy for Men. Grapefruit and mandarin open with a burst of sunshine; a fresh floral-aromatic heart of violet and basil lifts the spirits; a clean musk and cedar base ensures all-day freshness.',
                'category_id'       => $getCatId('men-fresh-aquatic'),
                'scent_notes'       => ['top' => ['Grapefruit', 'Mandarin', 'Bergamot'], 'heart' => ['Violet', 'Basil', 'Freesia'], 'base' => ['Musk', 'Cedarwood', 'Amber']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Limonene, Linalool, Citral, Geraniol.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-ICN-015',
                'name'              => 'ICONIC - Feralde Perfume (Le Male) Extrait de Parfum',
                'slug'              => 'iconic-le-male-extrait-de-parfum',
                'short_description' => 'The classic lavender-vanilla icon, reborn as Extrait de Parfum.',
                'description'       => 'ICONIC is Feralde\'s take on the legendary Jean Paul Gaultier Le Male — a timeless pairing of cool lavender and warm vanilla wrapped in a sailor\'s embrace. Mint and bergamot open brightly; lavender and vanilla form the iconic heart; amber and sandalwood form the base.',
                'category_id'       => $getCatId('men-sweet-gourmand'),
                'scent_notes'       => ['top' => ['Bergamot', 'Mint', 'Tarragon'], 'heart' => ['Lavender', 'Cumin', 'Cinnamon'], 'base' => ['Vanilla', 'Sandalwood', 'Amber', 'Musk']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Coumarin, Limonene, Cinnamal.',
                'is_active'         => true,
                'is_featured'       => true,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-VLR-016',
                'name'              => 'VALOR - Feralde Perfume (Sound of the Brave) Eau de Parfum',
                'slug'              => 'valor-sound-of-the-brave-eau-de-parfum',
                'short_description' => 'Courageous fresh citrus inspired by Paco Rabanne Sound of the Brave.',
                'description'       => 'VALOR summons the courageous spirit of Paco Rabanne\'s Sound of the Brave. A vibrant opening of lemon and bergamot transitions into a warm spicy heart of pepper and grapefruit, settling on a dry woody base. An invigorated Eau de Parfum for those who lead.',
                'category_id'       => $getCatId('men-strong-intense'),
                'scent_notes'       => ['top' => ['Bergamot', 'Lemon', 'Grapefruit'], 'heart' => ['Black Pepper', 'Lavender', 'Geranium'], 'base' => ['Cedarwood', 'Vetiver', 'Amberwood']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Limonene, Linalool, Citral, Eugenol.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => true,
            ],
            // ── SIGNATURE BLENDS ────────────────────────────────────────────
            [
                'sku'               => 'FRL-CHV-017',
                'name'              => 'CHIVALRY (Aventus Hybrid) - Feralde Perfume Signature Blend',
                'slug'              => 'chivalry-aventus-hybrid-signature-blend',
                'short_description' => 'An Aventus-inspired hybrid — smoky pineapple meets birch.',
                'description'       => 'CHIVALRY is Feralde\'s Signature Blend inspired by the legendary Creed Aventus — with a hybrid twist. Smoky birch and pineapple open with confidence; a rose-jasmine heart adds lush depth; a base of musk, oakmoss, and ambergris seals its legendary status. 10K+ sold.',
                'category_id'       => $getCatId('men-woody-spicy'),
                'scent_notes'       => ['top' => ['Pineapple', 'Bergamot', 'Apple', 'Blackcurrant'], 'heart' => ['Rose', 'Jasmine', 'Birch'], 'base' => ['Musk', 'Oakmoss', 'Ambergris', 'Vanillin']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Geraniol, Citronellol, Benzyl Benzoate, Coumarin.',
                'is_active'         => true,
                'is_featured'       => true,
                'is_best_seller'    => true,
                'is_new_arrival'    => false,
            ],
            // ── TPC COLLABORATION ────────────────────────────────────────────
            [
                'sku'               => 'FRL-VRF-018',
                'name'              => 'VERIFIED - TPC Signature Scent by Feralde Perfume',
                'slug'              => 'verified-tpc-signature-scent',
                'short_description' => 'The official scent of TPC — striking and memorable.',
                'description'       => 'VERIFIED is the official TPC x Feralde collaboration — a signature scent that embodies performance and credibility. A sharp citrus-herbal opening gives way to a clean aromatic heart, finishing on a confident woody-musky base. Wear it as your badge of merit.',
                'category_id'       => $getCatId('men-woody-spicy'),
                'scent_notes'       => ['top' => ['Bergamot', 'Sage', 'Lemon'], 'heart' => ['Lavender', 'Iris', 'Cedar Leaf'], 'base' => ['Sandalwood', 'Musk', 'Amberwood']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Limonene, Citral, Geraniol.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-NRC-019',
                'name'              => 'NARCISSUS - TPC Casual Scent by Feralde Perfume',
                'slug'              => 'narcissus-tpc-casual-scent',
                'short_description' => 'Your everyday crowd-pleaser — light and naturally alluring.',
                'description'       => 'NARCISSUS is the TPC x Feralde casual everyday scent. Light green notes and fresh citrus create an approachable opening; a soft floral-herbal heart keeps it effortless; a clean base of musk and light woods makes it wearable morning to night. The scent of natural confidence.',
                'category_id'       => $getCatId('men-fresh-aquatic'),
                'scent_notes'       => ['top' => ['Citrus', 'Green Notes', 'Bergamot'], 'heart' => ['Lily of the Valley', 'Jasmine', 'Basil'], 'base' => ['White Musk', 'Light Cedar', 'Vetiver']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Limonene, Citral, Citronellol.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-HRC-020',
                'name'              => 'HERACLES - TPC Gym Scent by Feralde Perfume',
                'slug'              => 'heracles-tpc-gym-scent',
                'short_description' => 'Fresh, energizing and sweat-proof — built for the gym.',
                'description'       => 'HERACLES is the world\'s first gym-optimized TPC x Feralde scent. Invigorating eucalyptus and peppermint open with a burst of energy; a clean aquatic-ozonic heart keeps you smelling fresh mid-workout; a dry woody base adds post-gym sophistication. Train hard. Smell great.',
                'category_id'       => $getCatId('men-strong-intense'),
                'scent_notes'       => ['top' => ['Eucalyptus', 'Peppermint', 'Citrus'], 'heart' => ['Ozonic Accord', 'Aquatic Notes', 'Lavender'], 'base' => ['Cedarwood', 'Musk', 'Vetiver']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Limonene, Menthol, Citral.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            // ── WOMEN'S ──────────────────────────────────────────────────────
            [
                'sku'               => 'FRL-RAD-021',
                'name'              => 'RADIANCE (Women) - Feralde Perfume (Katy P. Meow) Extrait de Parfum',
                'slug'              => 'radiance-katy-p-meow-extrait-de-parfum',
                'short_description' => 'Playful and sweet — inspired by Katy Perry Meow.',
                'description'       => 'RADIANCE is the fun, flirty Women\'s Extrait de Parfum inspired by Katy Perry\'s Meow. A sparkling opening of peach and mandarin leads into a sweet floral heart of jasmine, rose, and ylang-ylang; a soft sandalwood and musk base keeps it feminine and warm. Perfect for daytime charm.',
                'category_id'       => $getCatId('women-fruity-fresh'),
                'scent_notes'       => ['top' => ['Peach', 'Mandarin', 'Apple'], 'heart' => ['Jasmine', 'Rose', 'Ylang-Ylang'], 'base' => ['Sandalwood', 'White Musk', 'Vanilla']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Geraniol, Citronellol, Benzyl Benzoate.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-JAM-022',
                'name'              => 'JAMAI (Women) - Feralde Perfume (Bare Vanilla) Extrait de Parfum',
                'slug'              => 'jamai-bare-vanilla-extrait-de-parfum',
                'short_description' => 'Cozy vanilla warmth inspired by Victoria\'s Secret Bare Vanilla.',
                'description'       => 'JAMAI wraps you in the comforting sweetness of Bare Vanilla. A soft, warm opening of vanilla bean and caramel melts into a creamy sandalwood heart; skin-close musks and benzoin anchor the base in an enveloping, skin-like warmth. The ultimate cozy-night-in Women\'s Extrait de Parfum.',
                'category_id'       => $getCatId('women-sweet-vanilla'),
                'scent_notes'       => ['top' => ['Vanilla Bean', 'Caramel', 'Bergamot'], 'heart' => ['Sandalwood', 'Cream Accord', 'Jasmine'], 'base' => ['White Musk', 'Benzoin', 'Ambrette']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Coumarin, Benzyl Benzoate, Eugenol.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-GGW-023',
                'name'              => 'GOOD GIRL (Women) - Feralde Perfume (Good Girl Carolina H.) Extrait de Parfum',
                'slug'              => 'good-girl-carolina-h-extrait-de-parfum',
                'short_description' => 'Dark floral seduction inspired by Good Girl by Carolina Herrera.',
                'description'       => 'GOOD GIRL captures the duality of Carolina Herrera\'s icon — light and dark, innocent and seductive. A sparkling opening of almond and coffee ignites the senses; a luscious tuberose and jasmine heart blooms seductively; tonka bean and cacao create an irresistible base.',
                'category_id'       => $getCatId('women-sexy-night-out'),
                'scent_notes'       => ['top' => ['Almond', 'Coffee', 'Bergamot'], 'heart' => ['Tuberose', 'Jasmine', 'Rose'], 'base' => ['Tonka Bean', 'Cacao', 'Sandalwood', 'Musk']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Coumarin, Benzyl Benzoate, Cinnamal.',
                'is_active'         => true,
                'is_featured'       => true,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-IMP-024',
                'name'              => 'IMPERIAL (Women) - Feralde Perfume (Paris Hilton) Extrait de Parfum',
                'slug'              => 'imperial-paris-hilton-extrait-de-parfum',
                'short_description' => 'Glam floral-musk inspired by Paris Hilton\'s original fragrance.',
                'description'       => 'IMPERIAL embodies the glamour of Paris Hilton\'s classic fragrance. Peach and jasmine open with a radiant, feminine sparkle; a warm sandalwood-lily heart deepens the allure; a rich base of musk, amber, and patchouli rounds it into a lasting, opulent Women\'s Extrait de Parfum.',
                'category_id'       => $getCatId('women-floral-powdery'),
                'scent_notes'       => ['top' => ['Peach', 'Jasmine', 'Pink Grapefruit'], 'heart' => ['Gardenia', 'Lily', 'Sandalwood'], 'base' => ['Musk', 'Amber', 'Patchouli']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance), Linalool, Geraniol, Citronellol, Benzyl Benzoate.',
                'is_active'         => true,
                'is_featured'       => false,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
            // ── COLLECTIONS ─────────────────────────────────────────────────
            [
                'sku'               => 'FRL-10ML-025',
                'name'              => '10 ML Collection - Feralde Perfume (Extrait de Parfum)',
                'slug'              => '10ml-collection-extrait-de-parfum',
                'short_description' => 'Try any Feralde scent in a pocket-sized 10ml Extrait de Parfum.',
                'description'       => 'The perfect way to try Feralde Perfume — our 10 ML Collection of Extrait de Parfum miniatures. Choose from our full lineup in this convenient, travel-friendly size. Each bottle is a full Extrait de Parfum concentration — intense and long-lasting. 3K+ sold.',
                'category_id'       => $getCatId('starter-discovery-set'),
                'scent_notes'       => ['note' => ['Varies by chosen scent — full Extrait de Parfum concentration']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance). Specific notes vary by variant.',
                'is_active'         => true,
                'is_featured'       => true,
                'is_best_seller'    => true,
                'is_new_arrival'    => false,
            ],
            [
                'sku'               => 'FRL-3IN1-026',
                'name'              => '[3 in 1] TPC Collaboration Bundle with Feralde Perfume Exclusive',
                'slug'              => 'tpc-collaboration-bundle-3-in-1-exclusive',
                'short_description' => 'The ultimate TPC x Feralde trio — VERIFIED, NARCISSUS, HERACLES bundle.',
                'description'       => 'The [3 in 1] TPC x Feralde Exclusive Bundle contains all three TPC collaboration scents: VERIFIED (Signature), NARCISSUS (Casual), and HERACLES (Gym). Get the complete TPC fragrance wardrobe at a bundle-exclusive price. 1K+ sold.',
                'category_id'       => $getCatId('top-3-best-sellers'),
                'scent_notes'       => ['included' => ['VERIFIED (Signature)', 'NARCISSUS (Casual)', 'HERACLES (Gym)']],
                'ingredients'       => 'Alcohol Denat., Aqua, Parfum (Fragrance). Refer to individual products for full ingredient lists.',
                'is_active'         => true,
                'is_featured'       => true,
                'is_best_seller'    => false,
                'is_new_arrival'    => false,
            ],
        ];

        $mediaCount = count($this->mediaFiles);
        $mediaIndex = 0;

        foreach ($products as $productData) {
            if (Product::where('sku', $productData['sku'])->exists()) {
                $this->command->line("  Skipping {$productData['sku']} — already exists.");
                continue;
            }

            $product = Product::create($productData);

            $primaryFile = $this->mediaFiles[$mediaIndex % $mediaCount];
            ProductMedia::create([
                'product_id' => $product->id,
                'type'       => 'image',
                'url'        => $this->mediaBase . $primaryFile,
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            $secondaryFile = $this->mediaFiles[($mediaIndex + 1) % $mediaCount];
            ProductMedia::create([
                'product_id' => $product->id,
                'type'       => 'image',
                'url'        => $this->mediaBase . $secondaryFile,
                'is_primary' => false,
                'sort_order' => 1,
            ]);

            $tertiaryFile = $this->mediaFiles[($mediaIndex + 2) % $mediaCount];
            ProductMedia::create([
                'product_id' => $product->id,
                'type'       => 'image',
                'url'        => $this->mediaBase . $tertiaryFile,
                'is_primary' => false,
                'sort_order' => 2,
            ]);

            $mediaIndex++;

            $sizesToCreate = $this->getSizesForProduct($productData['sku']);

            foreach ($sizesToCreate as $sortIdx => $sizeDef) {
                $variantSku = $productData['sku'] . '-' . str_replace('ml', 'ML', $sizeDef['label']);

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku'        => $variantSku,
                    'name'       => $sizeDef['label'],
                    'size'       => $sizeDef['label'],
                    'is_active'  => true,
                    'sort_order' => $sortIdx,
                ]);

                foreach ([
                    ['tier' => 'RETAIL',      'price' => $sizeDef['retail']],
                    ['tier' => 'RESELLER',    'price' => $sizeDef['reseller']],
                    ['tier' => 'DISTRIBUTOR', 'price' => $sizeDef['distributor']],
                    ['tier' => 'WHOLESALE',   'price' => $sizeDef['wholesale']],
                ] as $pricingTier) {
                    VariantPricing::create([
                        'variant_id' => $variant->id,
                        'tier'       => $pricingTier['tier'],
                        'price'      => $pricingTier['price'],
                        'is_active'  => true,
                    ]);
                }

                Inventory::create([
                    'variant_id'       => $variant->id,
                    'warehouse_id'     => $warehouse?->id,
                    'quantity_on_hand' => $sizeDef['stock'],
                    'reorder_point'    => $sizeDef['reorder_point'],
                    'reorder_quantity' => $sizeDef['reorder_quantity'],
                ]);
            }

            $this->command->line("  Seeded: {$product->sku} — {$product->name}");
        }

        $this->command->info('ProductSeeder completed.');
    }

    private function getSizesForProduct(string $sku): array
    {
        if ($sku === 'FRL-10ML-025') {
            return [[
                'label'            => '10ml',
                'sort_order'       => 0,
                'retail'           => 99,
                'reseller'         => 89,
                'distributor'      => 79,
                'wholesale'        => 69,
                'stock'            => 500,
                'reorder_point'    => 50,
                'reorder_quantity' => 100,
            ]];
        }

        if ($sku === 'FRL-3IN1-026') {
            return [[
                'label'            => '3-in-1 Bundle (30ml each)',
                'sort_order'       => 0,
                'retail'           => 974,
                'reseller'         => 865,
                'distributor'      => 790,
                'wholesale'        => 720,
                'stock'            => 50,
                'reorder_point'    => 10,
                'reorder_quantity' => 20,
            ]];
        }

        return $this->sizeDefs;
    }
}
