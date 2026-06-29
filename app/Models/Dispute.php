<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $table = 'disputes';
    
    protected $fillable = [
        'order_id',
        'reporter_id',
        'issue_description',
        'proof_photo',
        'status',
        'resolution',
        'resolved_by'
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id', 'id');
    }
    
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by', 'id');
    }
    
    public function resolve($adminId, $resolution)
    {
        $this->status = 'resolved';
        $this->resolution = $resolution;
        $this->resolved_by = $adminId;
        $this->save();
    }
}