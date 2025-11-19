<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryTransaction;
use App\Models\LowStockAlert;
use App\Events\LowStockDetected;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Update product stock
     */
    public function updateStock(
        Product $product,
        int $quantity,
        string $type = 'adjustment',
        ?string $notes = null,
        ?int $variantId = null
    ): void {
        DB::beginTransaction();
        try {
            if ($variantId) {
                $variant = $product->variants()->findOrFail($variantId);
                $this->updateVariantStock($variant, $quantity, $type, $notes);
            } else {
                $quantityBefore = $product->stock_quantity;
                $quantityAfter = $this->calculateNewQuantity($quantityBefore, $quantity, $type);

                // Update product stock
                $product->update(['stock_quantity' => $quantityAfter]);

                // Log transaction
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'notes' => $notes,
                    'created_by' => auth()->id(),
                ]);

                // Check for low stock
                if ($product->isLowStock()) {
                    $this->createLowStockAlert($product);
                    event(new LowStockDetected($product));
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update variant stock
     */
    protected function updateVariantStock(
        ProductVariant $variant,
        int $quantity,
        string $type,
        ?string $notes = null
    ): void {
        $quantityBefore = $variant->stock_quantity;
        $quantityAfter = $this->calculateNewQuantity($quantityBefore, $quantity, $type);

        // Update variant stock
        $variant->update(['stock_quantity' => $quantityAfter]);

        // Log transaction
        InventoryTransaction::create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);

        // Check for low stock
        if ($quantityAfter <= 10) {
            $this->createLowStockAlert($variant->product, $variant);
        }
    }

    /**
     * Calculate new quantity based on transaction type
     */
    protected function calculateNewQuantity(int $current, int $quantity, string $type): int
    {
        return match($type) {
            'purchase', 'return' => $current + $quantity,
            'sale', 'damage' => $current - $quantity,
            'adjustment' => $quantity,
            default => $current,
        };
    }

    /**
     * Create low stock alert
     */
    protected function createLowStockAlert(Product $product, ?ProductVariant $variant = null): void
    {
        // Check if alert already exists
        $existingAlert = LowStockAlert::where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->unresolved()
            ->first();

        if (!$existingAlert) {
            LowStockAlert::create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'current_stock' => $variant ? $variant->stock_quantity : $product->stock_quantity,
                'threshold' => $product->low_stock_threshold,
            ]);
        }
    }

    /**
     * Deduct stock (for orders)
     */
    public function deductStock(Product $product, int $quantity, ?int $variantId = null): bool
    {
        if ($variantId) {
            $variant = $product->variants()->find($variantId);
            if (!$variant || $variant->stock_quantity < $quantity) {
                return false;
            }
            $this->updateStock($product, $quantity, 'sale', 'Order placement', $variantId);
        } else {
            if ($product->stock_quantity < $quantity) {
                return false;
            }
            $this->updateStock($product, $quantity, 'sale', 'Order placement');
        }

        return true;
    }

    /**
     * Restore stock (for cancelled orders)
     */
    public function restoreStock(Product $product, int $quantity, ?int $variantId = null): void
    {
        $this->updateStock($product, $quantity, 'return', 'Order cancellation', $variantId);
    }

    /**
     * Get inventory history
     */
    public function getInventoryHistory(Product $product, int $limit = 50)
    {
        return $product->inventoryTransactions()
            ->with('creator')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts(bool $unresolvedOnly = true)
    {
        $query = LowStockAlert::with(['product', 'variant']);

        if ($unresolvedOnly) {
            $query->unresolved();
        }

        return $query->latest()->get();
    }

    /**
     * Resolve low stock alert
     */
    public function resolveLowStockAlert(int $alertId): void
    {
        $alert = LowStockAlert::findOrFail($alertId);
        $alert->resolve();
    }
}