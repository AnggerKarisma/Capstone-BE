<?php

namespace App\Http\Controllers;

use App\Models\chat;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
   public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_type' => 'required|string|in:user,admin', // Tentukan 'user' atau 'admin'
            'receiver_id' => 'required|integer',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $sender = Auth::user();
        $receiverType = $request->receiver_type === 'admin' ? Admin::class : User::class;
        $receiverId = $request->receiver_id;

        // Cek apakah penerima ada
        $receiver = $receiverType::find($receiverId);
        if (!$receiver) {
            return response()->json(['success' => false, 'message' => 'Penerima tidak ditemukan'], 404);
        }

        $chat = Chat::create([
            'senderable_id' => $sender->getKey(),
            'senderable_type' => get_class($sender),
            'receiverable_id' => $receiverId,
            'receiverable_type' => $receiverType,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesan terkirim',
            'data' => $chat
        ], 201);
    }

    public function getConversation($receiverType, $receiverId)
    {
        $user = Auth::user();
        $receiverModel = $receiverType === 'admin' ? Admin::class : User::class;

        $messages = Chat::where(function ($query) use ($user, $receiverModel, $receiverId) {
            // 1. Pesan DARI kita UNTUK mereka
            $query->where('senderable_id', $user->getKey())
                  ->where('senderable_type', get_class($user))
                  ->where('receiverable_id', $receiverId)
                  ->where('receiverable_type', $receiverModel);
        })->orWhere(function ($query) use ($user, $receiverModel, $receiverId) {
            // 2. Pesan DARI mereka UNTUK kita
            $query->where('senderable_id', $receiverId)
                  ->where('senderable_type', $receiverModel)
                  ->where('receiverable_id', $user->getKey())
                  ->where('receiverable_type', get_class($user));
        })
        ->orderBy('created_at', 'asc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }
    
    public function getContacts(Request $request)
    {
        $user = $request->user();
        
        // Logika ini bisa kompleks, ini adalah contoh sederhana:
        // Ambil semua ID unik dan tipe yang pernah berkomunikasi dengan kita
        
        $sentTo = Chat::where('senderable_id', $user->getKey())
                      ->where('senderable_type', get_class($user))
                      ->select('receiverable_id', 'receiverable_type')
                      ->distinct();
                      
        $receivedFrom = Chat::where('receiverable_id', $user->getKey())
                            ->where('receiverable_type', get_class($user))
                            ->select('senderable_id', 'senderable_type')
                            ->distinct()
                            ->union($sentTo) // Gabungkan keduanya
                            ->get();

        // Anda perlu me-load data (nama, foto) dari hasil 'receivedFrom' ini di frontend
        return response()->json([
            'success' => true,
            'data' => $receivedFrom
        ]);
    }
}
