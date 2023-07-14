<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Message;  
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

class HomeController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
        
    }

    // show all groups that User is Follow
    public function index()
    {
    // select all Users + count how many message are unread from the selected user
        $users = DB::select("select users.id, users.name, users.email, count(is_read) as unread 
        from users LEFT  JOIN  messages ON users.id = messages.from and is_read = 0 and messages.to = " . Auth::id() . "
        where users.id != " . Auth::id() . " 
        group by users.id, users.name, users.email");
        return view('home', ['users' => $users]);
    }
    // get all Messages
    public function getMessage($user_id)
    {
        $my_id = Auth::id();

        // Make read all unread message sent
        Message::where(['from' => $user_id, 'to' => $my_id])->update(['is_read' => 1]);

        // Get all message from selected user
        $messages = Message::where(function ($query) use ($user_id, $my_id) {
        $query->where('from', $user_id)->where('to', $my_id);
        })->oRwhere(function ($query) use ($user_id, $my_id) {
        $query->where('from', $my_id)->where('to', $user_id);
        })->get();
        
        return view('messages.index', ['messages' => $messages]);
    }
   
   // send new message
    public function sendMessage(Request $request)
    {
        $from = Auth::id();
        $to = $request->receiver_id;
        $message = $request->message;

        $data = new Message();
        $data->from = $from;
        $data->to = $to;
        $data->message = $message;
        $data->is_read = 0; // message will be unread when sending message
        $data->save();

        return $this->sendRequest($from, $message, $to);
    }
    public function sendRequest($from, $message, $to){	
        $users = DB::select("SELECT * FROM messages WHERE messages.to = " . Auth::id() . " ");
        if(isset($users)){
            foreach ($users as $p) {
                $Data = $p->to;
            }}
        $options = array(
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'encrypted' => true
            );
        $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'), 
                $options
            );
        // notification
        $data = ['from' => $from, 'to' => $to]; 
        $notify = 'notify-channel';
        $pusher->trigger($notify, 'App\\Events\\Notify', $data);
    }
  }