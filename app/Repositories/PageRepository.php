<?php

namespace App\Repositories;

use App\Interfaces\PageRepositoryInterface;
use App\Models\Page;
use Illuminate\Support\Facades\DB;

class PageRepository implements PageRepositoryInterface
{
    public function getAllPages($perPage = 10)
    {
        return Page::with(['category', 'author'])->paginate($perPage);
    }

    public function getPageById($id)
    {
        return Page::with(['category', 'author'])->findOrFail($id);
    }

    public function createPage(array $data)
    {
        return Page::create($data);
    }

    public function updatePage($id, array $data)
    {
        $page = Page::findOrFail($id);
        $page->update($data);
        return $page;
    }

    public function deletePage($id)
    {
        $page = Page::findOrFail($id);
        return $page->delete();
    }

    public function getPublishedPages($perPage = 10)
    {
        return Page::where('is_published', true)
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function getPagesByCategory($categoryId, $perPage = 10)
    {
        return Page::where('category_id', $categoryId)
            ->where('is_published', true)
            ->with(['category', 'author'])
            ->paginate($perPage);
    }
}