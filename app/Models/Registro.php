<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'materia_registro', 'registro_id', 'materia_id')->withTimestamps();
    }
}
