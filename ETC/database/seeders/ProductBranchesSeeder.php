<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Models\Product;
use App\Models\Branch;
use Modules\Product\Models\ProductBranch;

class ProductBranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::all();
        $branches = Branch::all();

        foreach ($products as $product) {
            foreach ($branches as $branch) {
                ProductBranch::create([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                    'stock_qty' => $product->stock_qty ?? 0,
                    'is_available' => $product->status == 1
                ]);
            }
        }
    }
}
