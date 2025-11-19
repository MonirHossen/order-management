<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * Get all products with filters
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::with(['category', 'vendor', 'variants']);

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by vendor
        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        // Filter by stock status
        if (!empty($filters['stock_status'])) {
            $query->where('stock_status', $filters['stock_status']);
        }

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Filter by featured
        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        // Search
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Price range
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Find product by ID
     */
    public function findById(int $id): ?Product
    {
        return Product::with(['category', 'vendor', 'variants'])->find($id);
    }

    /**
     * Find product by slug
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::with(['category', 'vendor', 'variants'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Find product by SKU
     */
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    /**
     * Create product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update product
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    /**
     * Delete product
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(): Collection
    {
        return Product::lowStock()
            ->with(['category', 'vendor'])
            ->get();
    }

    /**
     * Get products by vendor
     */
    public function getByVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return Product::where('vendor_id', $vendorId)
            ->with(['category', 'variants'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search products (full-text search)
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return Product::whereFullText(['name', 'description', 'sku'], $term)
            ->orWhere('name', 'like', "%{$term}%")
            ->orWhere('sku', 'like', "%{$term}%")
            ->with(['category', 'vendor'])
            ->active()
            ->paginate($perPage);
    }
}