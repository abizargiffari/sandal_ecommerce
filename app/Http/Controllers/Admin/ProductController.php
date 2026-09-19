<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'primaryImage'])
            ->withSum('variants as total_stock', 'stock')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('sku', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::active()->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        DB::transaction(function () use ($validated, $request) {
            $product = Product::create([
                'category_id' => $validated['category_id'],
                'sku' => $this->generateUniqueSku(),
                'name' => $validated['name'],
                'slug' => $this->generateUniqueSlug($validated['name']),
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'discount_price' => $validated['discount_price'] ?? null,
                'weight' => $validated['weight'],
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
                'meta_title' => $validated['name'],
                'meta_description' => Str::limit(strip_tags($validated['description'] ?? ''), 150),
            ]);

            $this->storeImages($product, $request);
            $this->storeVariants($product, $validated['variants']);
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $product->load(['images', 'variants' => fn ($q) => $q->orderBy('size')]);

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product->id);

        DB::transaction(function () use ($validated, $request, $product) {
            if ($validated['name'] !== $product->name) {
                $slug = $this->generateUniqueSlug($validated['name'], $product->id);
            } else {
                $slug = $product->slug;
            }

            $product->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'discount_price' => $validated['discount_price'] ?? null,
                'weight' => $validated['weight'],
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
                'meta_title' => $validated['name'],
                'meta_description' => Str::limit(strip_tags($validated['description'] ?? ''), 150),
            ]);

            $this->deleteRemovedImages($request);
            $this->storeImages($product, $request);
            $this->setPrimaryImage($request);
            $this->updateVariants($product, $validated['variants'] ?? []);
            $this->storeNewVariants($product, $request->input('new_variants', []));
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->variants()->whereHas('orderItems')->exists()) {
            return back()->with('error', 'Produk tidak bisa dihapus karena sudah pernah dipesan. Nonaktifkan produk ini saja daripada menghapusnya.');
        }

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $product->delete(); // soft delete

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    // ---------------------------------------------------------------------
    // Helper privat
    // ---------------------------------------------------------------------

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'weight' => ['required', 'integer', 'min:1'],
            'images.*' => ['nullable', 'image', 'max:2048'],
            'variants' => ['nullable', 'array'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'new_variants' => ['nullable', 'array'],
            'new_variants.*.size' => ['nullable', 'string', 'max:20'],
            'new_variants.*.color' => ['nullable', 'string', 'max:50'],
            'new_variants.*.stock' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function storeImages(Product $product, Request $request): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $sortOrder = $product->images()->max('sort_order') ?? 0;

        foreach ($request->file('images') as $file) {
            $sortOrder++;
            $product->images()->create([
                'image_path' => $file->store('products', 'public'),
                'is_primary' => ! $hasPrimary && $sortOrder === 1,
                'sort_order' => $sortOrder,
            ]);
            $hasPrimary = true;
        }
    }

    private function deleteRemovedImages(Request $request): void
    {
        $toDelete = $request->input('delete_images', []);

        if (empty($toDelete)) {
            return;
        }

        $images = \App\Models\ProductImage::whereIn('id', $toDelete)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }
    }

    private function setPrimaryImage(Request $request): void
    {
        $primaryId = $request->input('primary_image_id');

        if (! $primaryId) {
            return;
        }

        $image = \App\Models\ProductImage::find($primaryId);

        if (! $image) {
            return;
        }

        \App\Models\ProductImage::where('product_id', $image->product_id)->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
    }

    private function storeVariants(Product $product, array $variants): void
    {
        foreach ($variants as $variant) {
            if (empty($variant['size'])) {
                continue;
            }

            $stock = (int) ($variant['stock'] ?? 0);

            $productVariant = $product->variants()->create([
                'size' => $variant['size'],
                'color' => $variant['color'] ?? null,
                'sku_variant' => $product->sku . '-' . $variant['size'],
                'price_adjustment' => $variant['price_adjustment'] ?? 0,
                'stock' => $stock,
            ]);

            if ($stock > 0) {
                StockMovement::create([
                    'product_variant_id' => $productVariant->id,
                    'type' => 'in',
                    'quantity' => $stock,
                    'reference_type' => 'initial_stock',
                    'note' => 'Stok awal saat produk dibuat',
                    'created_by' => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Update stok varian yang sudah ada. Perubahan stok selalu dicatat ke
     * stock_movements sebagai 'adjustment' supaya ada audit trail —
     * jangan pernah update kolom stock langsung tanpa jejak.
     */
    private function updateVariants(Product $product, array $variants): void
    {
        foreach ($variants as $variantId => $data) {
            $variant = $product->variants()->find($variantId);

            if (! $variant) {
                continue;
            }

            // Hapus (soft delete) varian jika dicentang, stok histori tetap aman
            if (! empty($data['delete'])) {
                $variant->delete();
                continue;
            }

            $newStock = (int) ($data['stock'] ?? $variant->stock);
            $difference = $newStock - $variant->stock;

            if ($difference !== 0) {
                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'type' => 'adjustment',
                    'quantity' => $difference,
                    'reference_type' => 'manual_adjustment',
                    'note' => 'Penyesuaian stok manual oleh ' . (auth()->user()->name ?? 'admin'),
                    'created_by' => auth()->id(),
                ]);
            }

            $variant->update(['stock' => $newStock]);
        }
    }

    private function storeNewVariants(Product $product, array $newVariants): void
    {
        foreach ($newVariants as $variant) {
            if (empty($variant['size'])) {
                continue;
            }

            $stock = (int) ($variant['stock'] ?? 0);

            $productVariant = $product->variants()->create([
                'size' => $variant['size'],
                'color' => $variant['color'] ?? null,
                'sku_variant' => $product->sku . '-' . $variant['size'] . '-' . Str::random(3),
                'price_adjustment' => 0,
                'stock' => $stock,
            ]);

            if ($stock > 0) {
                StockMovement::create([
                    'product_variant_id' => $productVariant->id,
                    'type' => 'in',
                    'quantity' => $stock,
                    'reference_type' => 'new_variant',
                    'note' => 'Varian baru ditambahkan',
                    'created_by' => auth()->id(),
                ]);
            }
        }
    }

    private function generateUniqueSku(): string
    {
        do {
            $sku = 'SDL-' . strtoupper(Str::random(6));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
