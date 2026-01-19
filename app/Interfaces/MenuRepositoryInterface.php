<?php

namespace App\Interfaces;

interface MenuRepositoryInterface
{
    public function getAllMenus($perPage = 10);
    public function getMenuById($id);
    public function createMenu(array $data);
    public function updateMenu($id, array $data);
    public function deleteMenu($id);
    public function getActiveMenus();
    public function getMenuWithItems($id);
}