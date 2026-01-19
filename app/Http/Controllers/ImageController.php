<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Interfaces\ImageRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    protected ImageRepositoryInterface $imageRepository;

    public function __construct(ImageRepositoryInterface $imageRepository)
    {
        $this->imageRepository = $imageRepository;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $images = $this->imageRepository->getAllImages(10);
        return view('images.index', compact('images'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('images.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240', // 10MB max
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'collection_name' => 'nullable|string|max:255',
        ]);

        $imageFile = $request->file('image');
        
        // Store the image
        $path = $imageFile->store('images', 'public');
        
        $data = [
            'name' => $imageFile->getClientOriginalName(),
            'path' => $path,
            'alt_text' => $request->input('alt_text'),
            'caption' => $request->input('caption'),
            'description' => $request->input('description'),
            'mime_type' => $imageFile->getMimeType(),
            'size' => $imageFile->getSize(),
            'user_id' => auth()->id(),
            'collection_name' => $request->input('collection_name', 'default'),
            'is_public' => true,
        ];

        $this->imageRepository->createImage($data);

        return redirect()->route('images.index')->with('success', 'Image uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $image = $this->imageRepository->getImageById($id);
        return view('images.show', compact('image'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $image = $this->imageRepository->getImageById($id);
        return view('images.edit', compact('image'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'alt_text' => 'sometimes|nullable|string|max:255',
            'caption' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'collection_name' => 'sometimes|nullable|string|max:255',
            'is_public' => 'sometimes|boolean',
        ]);

        $this->imageRepository->updateImage($id, $validated);

        return redirect()->route('images.index')->with('success', 'Image updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $image = $this->imageRepository->getImageById($id);
        
        // Delete the physical file
        if (Storage::exists($image->path)) {
            Storage::delete($image->path);
        }
        
        $this->imageRepository->deleteImage($id);

        return redirect()->route('images.index')->with('success', 'Image deleted successfully.');
    }
}