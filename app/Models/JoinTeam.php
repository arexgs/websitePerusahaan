<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoinTeam extends Model
{
    protected $table = 'join_teams';
    public $timestamps = false;

    protected $fillable = [
        'id_student',
        'id_team',
        'join_status',
        'join_at'
    ];
}