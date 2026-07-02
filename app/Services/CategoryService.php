<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryService extends BaseService
{
    /**
     * Get all categories.
     */
    public function getAll()
    {
        return Category::orderBy('name')->get();
    }

    /**
     * Get a category by ID.
     */
    public function getById(int $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return null;
        }

        return $category;
    }

    /**
     * Create a new category.
     */
    public function create(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);
    }

    /**
     * Update a category.
     */
    public function update(int $id, array $data): ?Category
    {
        $category = Category::find($id);

        if (!$category) {
            return null;
        }

        $category->update([
            'name' => $data['name'] ?? $category->name,
            'slug' => isset($data['name']) ? Str::slug($data['name']) : $category->slug,
        ]);

        return $category->fresh();
    }

    /**
     * Delete a category.
     */
    public function delete(int $id): bool
    {
        $category = Category::find($id);

        if (!$category) {
            return false;
        }

        return $category->delete();
    }
}
