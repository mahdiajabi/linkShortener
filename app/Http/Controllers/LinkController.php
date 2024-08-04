<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\ClickLog; 

class LinkController extends Controller
{
    public function shorten(Request $request)
    {
        try {
            $request->validate([
                'url' => 'required|url'
            ]);

            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $code = Str::random(6);

            Link::create([
                'url' => $request->input('url'),
                'code' => $code,
                'user_id' => Auth::id()
            ]);

            return response()->json(['short_url' => url('/l/'.$code)]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong: '.$e->getMessage()], 500);
        }
    }

    public function redirect($code)
    {
        $link = Link::where('code', $code)->first();

        if (!$link) {
            return response()->json(['error' => 'Link not found'], 404);
        }

        $link->increment('click_count');
        $userId = Auth::id() ? Auth::id() : null;
        ClickLog::create([
            'code' => $code,
            'user_id' => $userId,
            'clicked_at' => now()
        ]);

        return redirect($link->url);
    }

    public function getClickCount(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $link = Link::where('code', $request->code)->first();

        if (!$link) {
            return response()->json(['message' => 'Link not found'], 404);
        }

        return response()->json(['click_count' => $link->click_count]);
    }
}
