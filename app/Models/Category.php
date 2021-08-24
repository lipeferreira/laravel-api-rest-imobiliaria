<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name', 'description', 'slug'
    ];

    use HasFactory;

    public function realStates()
    {
        return $this->belongsToMany(RealState::class, 'real_state_categories'); // passando o nome da tabela porque ta invertido na criação
    }
}
