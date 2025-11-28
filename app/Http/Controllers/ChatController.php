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

            $chat->load('senderable');

            broadcast(new MessageSent($chat))->toOthers();

            return response()->json([
                'success' => true,
                'data' => $chat
            ], 201);
        }catch (\Exception $e) {
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
            $messages->where(function ($q) use ($user){
                $q->where('senderable_id', $user->userid)
                  ->where('senderable_type', User::class)
                  ->where('receiverable_id', Admin::class);
            })->orWhere(function ($q) use ($user){
                $q->where('senderable_id', Admin::class)
                  ->where('receiverable_id', $user->userid)
                  ->where('receiverable_type', User::class);
            });
        }else{
            $targetUserId = $receiverId;
            
            if (!$targetUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiver ID is required for Admins'
                ], 400);
            }

        $messages -> where(function ($q) use ($targetUserId){
                $q->where('senderable_id', $targetUserId)
                  ->where('senderable_type', User::class)
                  ->where('receiverable_type', Admin::class);
            })->orWhere(function ($q) use ($targetUserId){
                $q->where('senderable_type', Admin::class)
                  ->where('receiverable_id', $targetUserId)
                  ->where('receiverable_type', User::class);
            });
        }
        $data = $messages->orderBy('created_at', 'desc')->paginate(20);

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
                        'contact_id' =>0,
                        'name' => 'Admin',
                        'type' => 'admin',
                        'last_message' => $lastMsg ? $lastMsg->message : "belum ada pesan",
                        'time' => $lastMsg ? $lastMsg->created_at : null,
                        'unread' => 0
                    ]
                ]
            ]);
        } else {
            $userFrom = DB::table('chats')
            ->where('senderable_type', User::class)
            ->where('receiverable_id', Admin::class)
            ->select('senderable_id', DB::raw('MAX(created_at) as last_time'))
            ->groupBy('senderable_id');
            $userTo = DB::table('chats')
            ->where('senderable_type', Admin::class)
            ->where('receiverable_type', User::class)
            ->select('receiverable_id as user_id', DB::raw('MAX(created_at) as last_time'))
            ->groupBy('receiverable_id');
            $contacts = DB::query()->fromSub(function ($query) use ($userFrom,$userTo){
                $query->select('*')->from($userFrom)->union($userTo);
            }, 'combined_chats')
            ->select('user_id', DB::raw('MAX(last_time) as last_time'))
            ->groupBy('user_id')
            ->orderBy('final_last_time','desc')
            ->get();
            $enriched = $contacts->map(function($c){
                $userData = User::find($c->user_id);
                $lastChat = Chat::where(function ($q) use ($c){
                    $q->where('senderable_id', $c->user_id)
                      ->where('senderable_type', User::class);
                })->orWhere(function ($q) use ($c){
                    $q->where('receiverable_id', $c->user_id)
                      ->where('receiverable_type', User::class);
                })->latest()->first();
                return [
                    'contact_id' => $c->user_id,
                    'name' => $userData ? $userData->name : 'Unknown',
                    'type' => 'user',
                    'last_message' => $lastChat ? $lastChat->message : '',
                    'time' => $c->final_last_time,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $enriched
            ]);
        }
    }
}
