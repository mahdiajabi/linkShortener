<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Click extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'user_id', 'clicked_at'];

    public $timestamps = false; 

   
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
   
    public function link()
    {
        return $this->belongsTo(Link::class);

    }
}
