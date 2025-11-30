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

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [  
            'rating' => 'required|integer|min:1|max:5',
            'kategori' => 'required|in:umum,bug,pelayanan,lainnya',
            'isi_feedback' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        $user = Auth::user();
        $cekHariIni = Feedback::where('user_id', $user->userid)
            ->whereDate('created_at', now()->today())
            ->exists();

        if ($cekHariIni) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mengirim feedback hari ini'
            ], 403);
        }

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'kategori' => $request->kategori,
            'isi_feedback' => $request->isi_feedback,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully',
            'data' => $feedback
        ], 201);        
    }

}
