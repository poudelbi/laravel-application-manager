<?php

namespace App\Interfaces;

interface PageCategoryRepositoryInterface
{
    public function getAllCategories($perPage = 10);
    public function getCategoryById($id);
    public function createCategory(array $data);
    public function updateCategory($id, array $data);
    public function deleteCategory($id);
    public function getActiveCategories();
}