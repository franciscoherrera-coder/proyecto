<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoQrAsistencia extends Model
{
    use HasFactory;

    protected $table = 'codigos_qr_asistencia';

    protected $fillable = [
        'materia_id',
        'user_id',
        'fecha',
        'token',
        'tipo',
        'habilitado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'habilitado' => 'boolean',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }
}
