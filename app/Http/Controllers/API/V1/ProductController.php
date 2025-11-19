<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\ProductService;
use App\Services\InventoryService;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected ProductRepository $productRepository,
        protected InventoryService $inventoryService
    ) {
    }

    /**
     * Get all products
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $filters = $request->all();

        // Vendors can only see their products
        if ($user->isVendor()) {
            $filters['vendor_id'] = $user->id;
        }

        $products = $this->productRepository->getAll($filters, $request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get single product
     */
    public function show($id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Check vendor ownership
        $user = auth()->user();
        if ($user->isVendor() && $product->vendor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Create product
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $user = auth()->user();
        $vendorId = $user->isVendor() ? $user->id : $request->vendor_id;

        $product = $this->productService->createProduct($request->validated(), $vendorId);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Update product
     */
    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Check vendor ownership
        $user = auth()->user();
        if ($user->isVendor() && $product->vendor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update your own products',
            ], 403);
        }

        $product = $this->productService->updateProduct($product, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    /**
     * Delete product
     */
    public function destroy($id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Check vendor ownership
        $user = auth()->user();
        if ($user->isVendor() && $product->vendor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own products',
            ], 403);
        }

        $this->productService->deleteProduct($product);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Update inventory
     */
    public function updateInventory(Request $request, $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|in:purchase,sale,return,adjustment,damage',
            'notes' => 'nullable|string',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $product = $this->productRepository->findById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $this->productService->updateInventory(
            $product,
            $request->quantity,
            $request->type,
            $request->notes
        );

        return response()->json([
            'success' => true,
            'message' => 'Inventory updated successfully',
            'data' => $product->fresh(),
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $products = $this->productRepository->search(
            $request->q,
            $request->per_page ?? 15
        );

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Bulk import from CSV
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $products = $this->parseCsvFile($file);

            $results = $this->productService->bulkImport($products);

            return response()->json([
                'success' => true,
                'message' => 'Bulk import completed',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse CSV file
     */
    protected function parseCsvFile($file): array
    {
        $products = [];
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $products[] = array_combine($header, $row);
        }

        fclose($handle);
        return $products;
    }

    /**
     * Get low stock products
     */
    public function lowStockProducts(): JsonResponse
    {
        $products = $this->productRepository->getLowStockProducts();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get inventory history
     */
    public function inventoryHistory($id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $history = $this->inventoryService->getInventoryHistory($product);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}