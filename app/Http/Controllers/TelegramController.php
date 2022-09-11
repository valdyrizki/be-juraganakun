<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    public function createNotif($text)
    {
        $request = Http::get('https://api.telegram.org/'.env("TELEGRAM_BOT_ID").'/sendMessage',
        [
            'chat_id'=>env("TELEGRAM_CHAT_ID"),
            'parse_mode' => 'HTML',
            'text' => $text
        ]);
        return $request;
    }
}
