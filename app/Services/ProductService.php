<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Events\LowStockDetected;

class ProductService
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected InventoryService $inventoryService
    ) {
    }

    /**
     * Create product
     */
    public function createProduct(array $data, ?int $vendorId = null): Product
    {
        DB::beginTransaction();
        try {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Set vendor ID
            if ($vendorId) {
                $data['vendor_id'] = $vendorId;
            }

            // Create product
            $product = $this->productRepository->create($data);

            // Create variants if provided
            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $product->variants()->create($variantData);
                }
            }

            // Check for low stock
            if ($product->isLowStock()) {
                event(new LowStockDetected($product));
            }

            DB::commit();
            return $product->load(['category', 'vendor', 'variants']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update product
     */
    public function updateProduct(Product $product, array $data): Product
    {
        DB::beginTransaction();
        try {
            // Update slug if name changed
            if (isset($data['name']) && $data['name'] !== $product->name) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Update product
            $product = $this->productRepository->update($product, $data);

            // Update variants if provided
            if (isset($data['variants'])) {
                // Delete existing variants not in the update
                $variantIds = collect($data['variants'])->pluck('id')->filter();
                $product->variants()->whereNotIn('id', $variantIds)->delete();

                // Update or create variants
                foreach ($data['variants'] as $variantData) {
                    if (isset($variantData['id'])) {
                        $product->variants()->where('id', $variantData['id'])->update($variantData);
                    } else {
                        $product->variants()->create($variantData);
                    }
                }
            }

            DB::commit();
            return $product->fresh(['category', 'vendor', 'variants']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete product
     */
    public function deleteProduct(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }

    /**
     * Update inventory
     */
    public function updateInventory(Product $product, int $quantity, string $type = 'adjustment', ?string $notes = null): void
    {
        $this->inventoryService->updateStock($product, $quantity, $type, $notes);
    }

    /**
     * Bulk import products from CSV
     */
    public function bulkImport(array $products): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($products as $index => $productData) {
                try {
                    // Check if product exists by SKU
                    $existingProduct = $this->productRepository->findBySku($productData['sku']);

                    if ($existingProduct) {
                        // Update existing product
                        $this->updateProduct($existingProduct, $productData);
                    } else {
                        // Create new product
                        $this->createProduct($productData);
                    }

                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'sku' => $productData['sku'] ?? 'N/A',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(int $limit = 10)
    {
        return Product::featured()
            ->active()
            ->inStock()
            ->with(['category', 'vendor'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get related products
     */
    public function getRelatedProducts(Product $product, int $limit = 5)
    {
        return Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->inStock()
            ->limit($limit)
            ->get();
    }
}