<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = ['url', 'code', 'user_id', 'click_count']; 

    public $timestamps = true; 
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
