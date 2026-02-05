<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'cat_id' => 1,
                'cat_name' => 'لابتوب',
            ],
            [
                'cat_id' => 2,
                'cat_name' => 'هواتف',
            ],
            [
                'cat_id' => 3,
                'cat_name' => 'كماليات هواتف',
            ],
            [
                'cat_id' => 4,
                'cat_name' => 'ساعات',
            ],
        ];
		Category::insert($categories);
		
		$products = [
            [
                'pro_name' => 'لاب توب ديل إكس بي إس 13',
                'pro_description' => 'لاب توب قوي بمعالج i7 وذاكرة 16 جيجابايت',
                'pro_price' => 4500.00,
                'category_id' => 1,
                'pro_stock' => 15,
                'pro_sku' => 'DL-XPS-13-2024',
                'barcode' => '5901234567890',
                'is_active' => true,
            ],
            [
                'pro_name' => 'هاتف سامسونج جالكسي S24',
                'pro_description' => 'هاتف ذكي بكاميرة 200 ميجابكسل',
                'pro_price' => 3800.00,
                'category_id' => 2,
                'pro_stock' => 25,
                'pro_sku' => 'SS-S24-ULTRA',
                'barcode' => '5909876543210',
                'is_active' => true,
            ],
            [
                'pro_name' => 'سماعات رأس سوني WH-1000XM5',
                'pro_description' => 'سماعات لاسلكية مع إلغاء الضوضاء',
                'pro_price' => 1200.00,
                'category_id' => 3,
                'pro_stock' => 30,
                'pro_sku' => 'SN-WH1000XM5',
                'barcode' => '5904567891230',
                'is_active' => true,
            ],
            [
                'pro_name' => 'ساعة أبل واتش Series 9',
                'pro_description' => 'ساعة ذكية بميزات صحية متقدمة',
                'pro_price' => 2200.00,
                'category_id' => 4,
                'pro_stock' => 0,
                'pro_sku' => 'AP-WATCH-9',
                'barcode' => '5907891234560',
                'is_active' => true,
            ],
        ];

        Product::insert($products);
        
    }
}