<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InteractionLimit extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'message_count', 'is_paid'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
