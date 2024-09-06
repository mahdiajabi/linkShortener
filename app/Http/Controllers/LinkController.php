<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Click; 

class LinkController extends Controller
{
    public function shorten(Request $request)
    {
        
        $request->validate([
            'url' => 'required|url'
        ]);

        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $code = Str::random(6);

        $link = auth()->user()->links()->create([
            'url' => $request->input('url'),
            'code' => $code,
            'click_count' => 0, 
        ]);

        return response()->json(['short_url' => url('/l/'.$link->code)]);
       
    }

    public function redirect($code)
    {
        $link = Link::where('code', $code)->firstOrFail();

        $link->increment('click_count');

        if (auth()->check()) {
            auth()->user()->clicks()->create([
                'code' => $code,
                'clicked_at' => now()
            ]);
        }

        return response()->json(['url' => $link->url]);
    }

    public function getClickCount($code)
    {
        $link = Link::where('code', $code)->firstOrFail();

        return response()->json(['click_count' => $link->click_count]);
    }
}
