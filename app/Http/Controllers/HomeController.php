<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Menu;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Get recent pages
        $recentPages = Page::with('category')->latest()->take(5)->get();
        
        // Get active categories
        $categories = PageCategory::where('is_active', true)->get();
        
        // Get active menus
        $menus = Menu::where('is_active', true)->get();
        
        return view('welcome', compact('recentPages', 'categories', 'menus'));
    }
}