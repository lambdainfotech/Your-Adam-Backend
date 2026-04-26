<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;
    protected FileUploadService $fileUploadService;

    public function __construct(CategoryService $categoryService, FileUploadService $fileUploadService)
    {
        $this->categoryService = $categoryService;
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        $categories = Category::withCount(['products', 'subCategoryProducts'])
            ->with(['parent', 'children'])
            ->orderByRaw('COALESCE(parent_id, id)')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        return view('admin.categories.index', compact('categories'));
    }

    public function create(Request $request)
    {
        $parentCategories = $this->categoryService->getHierarchicalCategories();
        $preselectedParent = $request->get('parent_id');
        return view('admin.categories.create', compact('parentCategories', 'preselectedParent'));
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
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Supported formats: JPEG, PNG, JPG, WebP.',
            'image.max' => 'Maximum file size is 2MB.',
            'hero_image.image' => 'The file must be an image.',
            'hero_image.mimes' => 'Supported formats: JPEG, PNG, JPG, WebP.',
            'hero_image.max' => 'Maximum file size is 2MB.',
        ]);
        
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        
        if ($request->hasFile('image')) {
            $validated['image'] = $this->fileUploadService->upload($request->file('image'), 'categories');
        }

        if ($request->hasFile('hero_image')) {
            $validated['hero_image'] = $this->fileUploadService->upload($request->file('hero_image'), 'categories');
        }
        
        Category::create($validated);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $excludeIds = $this->categoryService->getDescendantIds($category);
        $excludeIds[] = $category->id;

        $parentCategories = $this->categoryService->getHierarchicalCategories($excludeIds);
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
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Supported formats: JPEG, PNG, JPG, WebP.',
            'image.max' => 'Maximum file size is 2MB.',
            'hero_image.image' => 'The file must be an image.',
            'hero_image.mimes' => 'Supported formats: JPEG, PNG, JPG, WebP.',
            'hero_image.max' => 'Maximum file size is 2MB.',
        ]);
        
        $validated['is_active'] = $request->boolean('is_active', true);
        
        if ($request->hasFile('image')) {
            $this->fileUploadService->deleteByUrl($category->image);
            $validated['image'] = $this->fileUploadService->upload($request->file('image'), 'categories');
        }

        if ($request->hasFile('hero_image')) {
            $this->fileUploadService->deleteByUrl($category->hero_image);
            $validated['hero_image'] = $this->fileUploadService->upload($request->file('hero_image'), 'categories');
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
        
        $this->fileUploadService->deleteByUrl($category->image);
        $this->fileUploadService->deleteByUrl($category->hero_image);
        
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


}
