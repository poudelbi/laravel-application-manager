<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Interfaces\PageCategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageCategoryController extends Controller
{
    protected PageCategoryRepositoryInterface $pageCategoryRepository;

    public function __construct(PageCategoryRepositoryInterface $pageCategoryRepository)
    {
        $this->pageCategoryRepository = $pageCategoryRepository;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categories = $this->pageCategoryRepository->getAllCategories(10);
        return view('page-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('page-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);

        $this->pageCategoryRepository->createCategory($validated);

        return redirect()->route('page-categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $category = $this->pageCategoryRepository->getCategoryById($id);
        return view('page-categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $category = $this->pageCategoryRepository->getCategoryById($id);
        return view('page-categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $this->pageCategoryRepository->updateCategory($id, $validated);

        return redirect()->route('page-categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->pageCategoryRepository->deleteCategory($id);

        return redirect()->route('page-categories.index')->with('success', 'Category deleted successfully.');
    }
}