<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Events\MessageSent;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'nullable|integer',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $sender = Auth::user();
        $isUser = $sender instanceof User;
    
        try {
            if ($isUser) {
                $receiverId = 0; 
                $receiverType = Admin::class;
            } else {
                $receiverId = $request->receiver_id;
                $receiverType = User::class;
                
                if (!User::find($receiverId)) {
                    return response()->json(['success' => false, 'message' => 'User penerima tidak ditemukan'], 404);
                }
            }

            $chat = Chat::create([
                'senderable_id' => $sender->getKey(),
                'senderable_type' => get_class($sender),
                'receiverable_id' => $receiverId,
                'receiverable_type' => $receiverType,
                'message' => $request->message,
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'data' => $chat
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getConversation($receiverType, $receiverId)
    {
        $user = Auth::user();
        $isUser = $user instanceof User;
        
        $messages = Chat::query();
        
        if ($isUser) {
            // Logika untuk User melihat chat dengan Admin
            $messages->where(function ($q) use ($user){
                $q->where('senderable_id', $user->userid)
                  ->where('senderable_type', User::class)
                  ->where('receiverable_type', Admin::class);
            })->orWhere(function ($q) use ($user){
                $q->where('senderable_type', Admin::class)
                  ->where('receiverable_id', $user->userid)
                  ->where('receiverable_type', User::class);
            });
        } else {
            // Logika untuk Admin melihat chat dengan User tertentu
            $targetUserId = $receiverId;
            
            if (!$targetUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiver ID is required for Admins'
                ], 400);
            }

            $messages->where(function ($q) use ($targetUserId){
                // Chat dari User ke Admin
                $q->where('senderable_id', $targetUserId)
                  ->where('senderable_type', User::class)
                  ->where('receiverable_type', Admin::class);
            })->orWhere(function ($q) use ($targetUserId){
                // Chat dari Admin ke User
                $q->where('senderable_type', Admin::class)
                  ->where('receiverable_id', $targetUserId)
                  ->where('receiverable_type', User::class);
            });
        }

        $data = $messages->orderBy('created_at', 'desc')->paginate(20);

        // Balik urutan agar yang terlama di atas (untuk tampilan chat UI)
        $reversed = $data->getCollection()->reverse()->values();
        $data->setCollection($reversed);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    
    public function getContacts(Request $request)
    {
        $user = Auth::user();
        if ($user instanceof User) {
            // User hanya punya 1 kontak yaitu "Admin"
            $lastMsg = Chat::where(function ($q) use ($user){
                $q->where('senderable_id', $user->userid)
                  ->where('senderable_type', User::class);
            })->orWhere(function ($q) use ($user){
                $q->where('receiverable_id', $user->userid)
                  ->where('receiverable_type', User::class);
            })->latest()->first();

            return response()->json([
                'success' =>true,
                'data' =>[
                    [
                        'contact_id' => 0, // ID 0 merepresentasikan Admin System
                        'name' => 'Admin RSPB',
                        'type' => 'admin',
                        'last_message' => $lastMsg ? $lastMsg->message : "Mulai percakapan...",
                        'time' => $lastMsg ? $lastMsg->created_at : null,
                        'unread' => 0
                    ]
                ]
            ]);
        } else {
            try {
                $incoming = DB::table('chats')
                    ->select('senderable_id as user_id', DB::raw('MAX(created_at) as last_time'))
                    ->where('senderable_type', User::class)
                    ->where('receiverable_type', Admin::class)
                    ->groupBy('senderable_id');
                $outgoing = DB::table('chats')
                    ->select('receiverable_id as user_id', DB::raw('MAX(created_at) as last_time'))
                    ->where('senderable_type', Admin::class)
                    ->where('receiverable_type', User::class)
                    ->groupBy('receiverable_id');
                $contactsQuery = $incoming->union($outgoing);
                $contacts = DB::query()->fromSub($contactsQuery, 'combined_chats')
                    ->select('user_id', DB::raw('MAX(last_time) as last_time'))
                    ->groupBy('user_id')
                    ->orderBy('last_time', 'desc') // Urutkan berdasarkan pesan terakhir
                    ->get();
                $enriched = $contacts->map(function($c){
                    $userData = User::find($c->user_id);
                    $lastChat = Chat::where(function ($q) use ($c){
                        $q->where('senderable_id', $c->user_id)
                          ->where('senderable_type', User::class)
                          ->where('receiverable_type', Admin::class);
                    })->orWhere(function ($q) use ($c){
                        $q->where('receiverable_id', $c->user_id)
                          ->where('receiverable_type', User::class)
                          ->where('senderable_type', Admin::class);
                    })->latest()->first();

                    return [
                        'contact_id' => $c->user_id,
                        'name' => $userData ? $userData->name : 'User Tidak Dikenal', // Handle jika user dihapus
                        'type' => 'user',
                        'last_message' => $lastChat ? $lastChat->message : '',
                        'time' => $c->last_time,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => $enriched
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil kontak',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }
}