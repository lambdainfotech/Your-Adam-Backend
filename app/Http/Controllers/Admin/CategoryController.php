<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    protected ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::where('is_active', true)->whereNull('parent_id')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Supported formats: JPEG, PNG, JPG, WebP.',
            'image.max' => 'Maximum file size is 2MB.',
        ]);
        
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadCategoryImage($request->file('image'));
        }
        
        Category::create($validated);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->get();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Supported formats: JPEG, PNG, JPG, WebP.',
            'image.max' => 'Maximum file size is 2MB.',
        ]);
        
        $validated['is_active'] = $request->boolean('is_active', true);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                $this->deleteCategoryImage($category->image);
            }
            $validated['image'] = $this->uploadCategoryImage($request->file('image'));
        }
        
        $category->update($validated);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with associated products.');
        }
        
        // Delete image if exists
        if ($category->image) {
            $this->deleteCategoryImage($category->image);
        }
        
        $category->delete();
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
    
    public function toggleStatus(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();
        
        $status = $category->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.categories.index')
            ->with('success', "Category {$status} successfully.");
    }

    /**
     * Upload category image
     */
    protected function uploadCategoryImage($file): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('categories', $filename, 'public');
        return Storage::url($path);
    }

    /**
     * Delete category image
     */
    protected function deleteCategoryImage(string $imageUrl): void
    {
        $path = str_replace(Storage::url(''), '', $imageUrl);
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
