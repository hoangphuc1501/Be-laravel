<?php

namespace App\Http\Controllers\admin;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $sort = $request->input('sort', 'position-desc');

        $query = Contact::select('id', 'fullName', 'email', 'position', 'phone', 'content', 'status', 'createdAt')
            ->where('deleted', false);

        // Lọc theo từ khóa
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullName', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        // Sắp xếp
        switch ($sort) {
            case 'position-asc':
                $query->orderBy('position', 'asc');
                break;
            case 'position-desc':
                $query->orderBy('position', 'desc');
                break;
            case 'title-asc':
                $query->orderBy('fullName', 'asc');
                break;
            case 'title-desc':
                $query->orderBy('fullName', 'desc');
                break;
            default:
                $query->orderBy('position', 'desc');
        }

        $contacts = $query->paginate($perPage);

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách liên hệ.',
            'data' => $contacts
        ]);
    }


    public function destroy($id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'code' => 'error',
                'message' => 'Liên hệ không tồn tại.'
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'code' => 'success',
            'message' => 'Xoá liên hệ thành công .'
        ]);
    }

}
