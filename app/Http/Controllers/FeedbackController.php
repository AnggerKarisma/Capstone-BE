<?php

namespace App\Http\Controllers;

use App\Models\feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Feedback::with('user:userid,name,email');
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }
        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        $feedbacks = $query->latest()->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Feedback retrieved successfully',
            'data' => $feedbacks
        ]);
    }

    

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'kategori' => 'required|in:umum,bug,pelayanan,lainnya',
            'isi_feedback' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $user = Auth::user();

        $feedback = Feedback::create([
            'user_id' => null, 
            'rating' => $request->rating,
            'kategori' => $request->kategori,
            'isi_feedback' => $request->isi_feedback,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback Anda telah terkirim secara anonim.',
            'data' => $feedback
        ], 201);
    }

}
