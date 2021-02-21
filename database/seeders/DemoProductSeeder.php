<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Taxon;
use Illuminate\Database\Seeder;

class DemoProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subTaxons = Taxon::where('parent_id', '!=' , null)->get();

        $i = 0;

        foreach ($subTaxons as $taxon) {
            switch ($taxon->name) {
                case 'T-paidat':
                    $name = 'T-paita';
                    break;

                case 'Housut':
                    $name = 'Housut';
                    break;

                default:
                    $name = 'Kengät';
                    break;
            }

            $index = ++$i;

            /** @var \App\Models\Product */
            $product = Product::create([
                'name' => $name . ' ' . $index,
                'sku' => 'product_' . $index
            ]);

            $product->addTaxon($taxon);
        }
    }
}
