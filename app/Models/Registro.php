<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function usuarioAcceso()
    {
        return $this->hasOne(User::class, 'dni', 'dni');
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'materia_registro', 'registro_id', 'materia_id')->withTimestamps();
    }

    public function asistenciasDiarias()
    {
        return $this->hasMany(AsistenciaDiaria::class, 'registro_id');
    }
}
