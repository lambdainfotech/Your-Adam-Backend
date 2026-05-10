<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    protected ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display image management page
     */
    public function index(Product $product)
    {
        $images = $this->imageService->getProductImages($product->id);
        
        return view('admin.products.images', compact('product', 'images'));
    }

    /**
     * Store uploaded images
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'images.required' => 'Please select at least one image.',
            'images.*.image' => 'The file must be an image.',
            'images.*.mimes' => 'Supported formats: JPEG, PNG, JPG, WebP.',
            'images.*.max' => 'Maximum file size is 5MB.',
        ]);

        try {
            $uploadedImages = $this->imageService->uploadImages(
                $product->id, 
                $validated['images']
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => count($uploadedImages) . ' image(s) uploaded successfully.',
                    'images' => $uploadedImages,
                ]);
            }

            return redirect()
                ->route('admin.products.images', $product)
                ->with('success', count($uploadedImages) . ' image(s) uploaded successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload images: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to upload images: ' . $e->getMessage());
        }
    }

    /**
     * Update image (alt text)
     */
    public function update(Request $request, Product $product, ProductImage $image)
    {
        // Ensure image belongs to product
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
        ]);

        $this->imageService->updateAltText($image, $validated['alt_text'] ?? null);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully.',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Image updated successfully.');
    }

    /**
     * Delete image
     */
    public function destroy(Request $request, Product $product, ProductImage $image)
    {
        // Ensure image belongs to product
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        try {
            $this->imageService->deleteImage($image);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully.',
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Image deleted successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete image: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete image.');
        }
    }

    /**
     * Set image as main
     */
    public function setMain(Request $request, Product $product, ProductImage $image)
    {
        // Ensure image belongs to product
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        $this->imageService->setAsMain($product->id, $image->id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Main image updated successfully.',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Main image updated successfully.');
    }

    /**
     * Reorder images
     */
    public function reorder(Request $request, Product $product)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:product_images,id',
        ]);

        $this->imageService->reorderImages($product->id, $validated['order']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Images reordered successfully.',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Images reordered successfully.');
    }
}
