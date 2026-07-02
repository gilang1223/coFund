<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;

class CategoryController extends ApiController
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = $this->categoryService->getAll();

        return $this->sendResponse('Categories retrieved successfully', $categories);
    }

    /**
     * Display the specified category.
     */
    public function show(int $id)
    {
        $category = $this->categoryService->getById($id);

        if (!$category) {
            return $this->sendNotFound('Category not found');
        }

        return $this->sendResponse('Category retrieved successfully', $category);
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request)
    {
        if (!auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Only admin can create categories');
        }

        $category = $this->categoryService->create($request->validated());

        return $this->sendCreated('Category created successfully', $category);
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryRequest $request, int $id)
    {
        if (!auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Only admin can update categories');
        }

        $category = $this->categoryService->update($id, $request->validated());

        if (!$category) {
            return $this->sendNotFound('Category not found');
        }

        return $this->sendResponse('Category updated successfully', $category);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(int $id)
    {
        if (!auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Only admin can delete categories');
        }

        $deleted = $this->categoryService->delete($id);

        if (!$deleted) {
            return $this->sendNotFound('Category not found');
        }

        return $this->sendResponse('Category deleted successfully');
    }
}
