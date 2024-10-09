<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
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

        DB::beginTransaction();
        try {
            $code = Str::random(6);

            $link = auth()->user()->links()->create([
                'url' => $request->input('url'),
                'code' => $code,
                'click_count' => 0,
            ]);

            DB::commit();

            return response()->json(['short_url' => url('/l/'.$link->code)]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating link: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to create short link'], 500);
        }
    }


    public function redirect($code)
    {
        DB::beginTransaction();

        try {
            $link = Link::where('code', $code)->firstOrFail();

            $link->increment('click_count');

            if (auth()->check()) {
                auth()->user()->clicks()->create([
                    'code' => $code,
                    'clicked_at' => now()
                ]);
            }

            DB::commit();

            return response()->json(['url' => $link->url]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error during redirect: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to redirect'], 500);
        }
    }


    public function getClickCount($code)
    {
        DB::beginTransaction();

        try {
            $link = Link::where('code', $code)->firstOrFail();

            DB::commit();

            return response()->json(['click_count' => $link->click_count]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error fetching click count: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to get click count'], 500);
        }
    }
}
