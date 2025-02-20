<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
class CloudinaryController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();

        return response()->json([
            'message' => 'Upload thành công!',
            'url' => $uploadedFileUrl,
        ]);
    }
}
