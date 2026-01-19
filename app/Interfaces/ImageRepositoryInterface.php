<?php

namespace App\Interfaces;

interface ImageRepositoryInterface
{
    public function getAllImages($perPage = 10);
    public function getImageById($id);
    public function createImage(array $data);
    public function updateImage($id, array $data);
    public function deleteImage($id);
    public function getImagesByCollection($collectionName, $perPage = 10);
    public function getPublicImages($perPage = 10);
}