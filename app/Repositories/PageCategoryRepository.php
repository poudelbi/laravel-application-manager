<?php

namespace App\Repositories;

use App\Interfaces\PageCategoryRepositoryInterface;
use App\Models\PageCategory;

class PageCategoryRepository implements PageCategoryRepositoryInterface
{
    public function getAllCategories($perPage = 10)
    {
        return PageCategory::paginate($perPage);
    }

    public function getCategoryById($id)
    {
        return PageCategory::findOrFail($id);
    }

    public function createCategory(array $data)
    {
        return PageCategory::create($data);
    }

    public function updateCategory($id, array $data)
    {
        $category = PageCategory::findOrFail($id);
        $category->update($data);
        return $category;
    }

    public function deleteCategory($id)
    {
        $category = PageCategory::findOrFail($id);
        return $category->delete();
    }

    public function getActiveCategories()
    {
        return PageCategory::where('is_active', true)->get();
    }
}