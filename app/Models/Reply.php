<?php

namespace App\Models;

class Reply extends Model
{
    protected $fillable = ['topic_id', 'user_id', 'content'];

    protected $casts = [
      'created_at' => "datetime:Y-m-d H:i:s",
      'updated_at' => "datetime:Y-m-d H:i:s",
    ];


    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
