<?php

namespace App\Interfaces;

interface PageRepositoryInterface
{
    public function getAllPages($perPage = 10);
    public function getPageById($id);
    public function createPage(array $data);
    public function updatePage($id, array $data);
    public function deletePage($id);
    public function getPublishedPages($perPage = 10);
    public function getPagesByCategory($categoryId, $perPage = 10);
}