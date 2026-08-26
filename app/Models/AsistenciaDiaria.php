<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaDiaria extends Model
{
    use HasFactory;

    protected $table = 'asistencias_diarias';

    protected $fillable = [
        'materia_id',
        'registro_id',
        'fecha',
        'estado',
        'motivo_justificacion',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function alumno()
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }
}
