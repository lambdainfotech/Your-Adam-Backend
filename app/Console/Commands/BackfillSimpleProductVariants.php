<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Console\Command;

class BackfillSimpleProductVariants extends Command
{
    protected $signature = 'products:backfill-variants 
                            {--dry-run : Show what would be created without making changes}';

    protected $description = 'Create default variants for simple products that do not have any variants.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $simpleProducts = Product::where('product_type', 'simple')
            ->whereDoesntHave('variants')
            ->get();

        if ($simpleProducts->isEmpty()) {
            $this->info('No simple products missing variants. All good!');
            return self::SUCCESS;
        }

        $this->info("Found {$simpleProducts->count()} simple product(s) without variants.");

        if ($dryRun) {
            $this->warn('Running in dry-run mode. No changes will be made.');
        }

        $created = 0;
        $errors = 0;

        foreach ($simpleProducts as $product) {
            $this->info("Processing: #{$product->id} - {$product->name}");

            if ($dryRun) {
                $created++;
                continue;
            }

            try {
                Variant::create([
                    'product_id' => $product->id,
                    'variant_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => $product->base_price,
                    'compare_price' => $product->compare_price,
                    'discount_type' => $product->discount_type,
                    'discount_value' => $product->discount_value,
                    'sale_price' => $product->sale_price,
                    'wholesale_price' => $product->wholesale_price,
                    'wholesale_percentage' => $product->wholesale_percentage,
                    'cost_price' => $product->cost_price,
                    'stock_quantity' => $product->stock_quantity,
                    'stock_status' => $product->stock_status,
                    'low_stock_threshold' => $product->low_stock_threshold,
                    'manage_stock' => $product->manage_stock,
                    'weight' => $product->weight,
                    'is_active' => true,
                    'position' => 0,
                ]);
                $created++;
            } catch (\Exception $e) {
                $this->error("Failed to create variant for product #{$product->id}: {$e->getMessage()}");
                $errors++;
            }
        }

        if ($dryRun) {
            $this->info("Would create {$created} variant(s).");
        } else {
            $this->info("Created {$created} variant(s).");
            if ($errors > 0) {
                $this->error("{$errors} error(s) occurred.");
            }
        }

        return self::SUCCESS;
    }
}
