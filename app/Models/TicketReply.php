<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\SupportTicket;
use App\Models\Admin;
use App\Models\User;

class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'message',
        'is_internal',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($reply) {
            $reply->ticket->update(['last_reply_at' => now()]);
        });
    }

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function author()
    {
        return $this->morphTo();
    }

    public function getIsAdminReplyAttribute()
    {
        return $this->author_type === Admin::class;
    }

    public function getIsCustomerReplyAttribute()
    {
        return $this->author_type === User::class;
    }
}
