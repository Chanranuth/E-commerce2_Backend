<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryImageController extends Controller
{
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        // Store the new image in public storage
        $path = $request->file('image')
            ->store('categories', 'public');

        // Keep the old path in case we need to delete the old image
        $oldPath = $category->image_path;

        $category->image_path = $path;
        $category->save();

        // Delete the old image after saving the new path
        if ($oldPath && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        return response()->json([
            'message' => 'Category image uploaded successfully.',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'image_path' => $category->image_path,
                'image_url' => url(Storage::url($category->image_path)),
            ],
        ]);
    }
}
