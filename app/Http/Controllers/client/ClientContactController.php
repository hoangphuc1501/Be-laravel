<?php

namespace App\Http\Controllers\client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;

class ClientContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'content' => 'nullable|string',
        ]);

        $maxPosition = Contact::max('position') ?? 0;

        $contact = Contact::create([
            'fullName' => $validated['fullName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'content' => $validated['content'],
            'position' => $maxPosition + 1,
            'status' => 1,
        ]);

        // Gửi email
        Mail::raw("Họ tên: {$contact->fullName}\nEmail: {$contact->email}\nSĐT: {$contact->phone}\nNội dung: {$contact->content}", function ($message) use ($contact) {
            $message->to('katakuri10000@gmail.com') 
            ->subject('Thông tin liên hệ mới từ người dùng');
        });

        return response()->json([
            'code' => 'success',
            'message' => 'Gửi thông tin liên hệ thành công!',
            'contact' => $contact,
        ], 201);
    }
}
