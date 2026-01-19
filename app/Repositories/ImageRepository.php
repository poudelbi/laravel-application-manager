<?php

namespace App\Repositories;

use App\Interfaces\ImageRepositoryInterface;
use App\Models\Image;

class ImageRepository implements ImageRepositoryInterface
{
    public function getAllImages($perPage = 10)
    {
        return Image::paginate($perPage);
    }

    public function getImageById($id)
    {
        return Image::findOrFail($id);
    }

    public function createImage(array $data)
    {
        return Image::create($data);
    }

    public function updateImage($id, array $data)
    {
        $image = Image::findOrFail($id);
        $image->update($data);
        return $image;
    }

    public function deleteImage($id)
    {
        $image = Image::findOrFail($id);
        return $image->delete();
    }

    public function getImagesByCollection($collectionName, $perPage = 10)
    {
        return Image::where('collection_name', $collectionName)->paginate($perPage);
    }

    public function getPublicImages($perPage = 10)
    {
        return Image::where('is_public', true)->paginate($perPage);
    }
}