<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;
    protected $fillable=['place','event','description','event_start','category_id','location_id','image'];

    public function category(){
        return $this->hasMany(Category::class);
    }
    public function location(){
        return $this->belongsTo(Location::class);
    }
}

