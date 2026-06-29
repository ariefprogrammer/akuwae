<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table = 'ratings';
    
    protected $fillable = [
        'order_id',
        'reviewer_id',
        'reviewee_id',
        'rating',
        'comment'
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id', 'id');
    }
    
    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id', 'id');
    }
}