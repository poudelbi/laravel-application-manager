<?php

namespace App\Repositories;

use App\Interfaces\MenuRepositoryInterface;
use App\Models\Menu;

class MenuRepository implements MenuRepositoryInterface
{
    public function getAllMenus($perPage = 10)
    {
        return Menu::paginate($perPage);
    }

    public function getMenuById($id)
    {
        return Menu::with('menuItems')->findOrFail($id);
    }

    public function createMenu(array $data)
    {
        return Menu::create($data);
    }

    public function updateMenu($id, array $data)
    {
        $menu = Menu::findOrFail($id);
        $menu->update($data);
        return $menu;
    }

    public function deleteMenu($id)
    {
        $menu = Menu::findOrFail($id);
        return $menu->delete();
    }

    public function getActiveMenus()
    {
        return Menu::where('is_active', true)->get();
    }

    public function getMenuWithItems($id)
    {
        return Menu::with(['menuItems' => function($query) {
            $query->where('is_active', true)->orderBy('order');
        }])->findOrFail($id);
    }
}