<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    /**
     * Resolve category_id and sub_category_id from the selected category.
     * If user selects a sub-category, set category_id to parent and sub_category_id to selected.
     * If user selects a leaf main category, keep category_id and set sub_category_id to null.
     */
    public function resolveCategoryIds(array $data): array
    {
        $selectedCategoryId = $data['category_id'];
        $category = Category::find($selectedCategoryId);

        if ($category && $category->parent_id !== null) {
            $data['category_id'] = $category->parent_id;
            $data['sub_category_id'] = $selectedCategoryId;
        } else {
            $data['sub_category_id'] = null;
        }

        return $data;
    }

    /**
     * Get hierarchical categories for dropdown
     *
     * @param array $excludeIds IDs to exclude (e.g., to prevent circular references)
     * @return array
     */
    public function getHierarchicalCategories(array $excludeIds = []): array
    {
        $query = Category::where('is_active', true)
            ->whereNull('parent_id');

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $rootCategories = $query->orderBy('name')->get();
        $result = [];

        foreach ($rootCategories as $root) {
            $result[] = $root;
            $this->addChildrenRecursively($root, $result, 1, $excludeIds);
        }

        return $result;
    }

    /**
     * Recursively add children to hierarchical list
     */
    protected function addChildrenRecursively(Category $category, array &$result, int $level, array $excludeIds = []): void
    {
        $children = Category::where('parent_id', $category->id)
            ->where('is_active', true);

        if (!empty($excludeIds)) {
            $children->whereNotIn('id', $excludeIds);
        }

        $children = $children->orderBy('name')->get();

        foreach ($children as $child) {
            $child->name = str_repeat('— ', $level) . $child->name;
            $child->hierarchical_level = $level;
            $result[] = $child;
            $this->addChildrenRecursively($child, $result, $level + 1, $excludeIds);
        }
    }

    /**
     * Get all descendant IDs of a category
     */
    public function getDescendantIds(Category $category): array
    {
        $ids = [];
        $children = Category::where('parent_id', $category->id)->get();

        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }

        return $ids;
    }
}
