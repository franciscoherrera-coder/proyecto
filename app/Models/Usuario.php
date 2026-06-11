<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword; // <- agregar
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait; // <- agregar

class Usuario extends Authenticatable implements CanResetPassword
{
    use Notifiable, HasFactory, CanResetPasswordTrait; // <- agregar CanResetPasswordTrait

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'fecha_nacimiento',
        'dni',
        'genero',
        'nacionalidad',
        'ciudad_residencia',
        'pais_residencia',
        'foto_perfil',
        'descripcion',
        'sitio_web',
        'carrera_id',
        'rol',
        'password',
        'telefono'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function experienciasLaborales()
    {
        return $this->hasMany(ExperienciaLaboral::class, 'usuario_id')->orderBy('año_inicio', 'desc');
    }

    public function aptitudes()
    {
        return $this->hasMany(Aptitud::class);
    }

    public function estudios()
    {
        return $this->hasMany(Estudio::class);
    }

    public function cursos()
    {
        return $this->hasMany(Curso::class);
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class);
    }

    public function experiencias()
    {
        return $this->hasMany(Experiencia::class);
    }

    public function cvs()
    {
        return $this->hasMany(Cv::class);
    }

    public function idiomas()
    {
        return $this->hasMany(Idioma::class);
    }

    public function ofertasGuardadas()
    {
        return $this->belongsToMany(Oferta::class, 'ofertas_guardadas', 'usuario_id', 'oferta_id');
    }

    public function usuariosQueGuardaron()
    {
        return $this->belongsToMany(Usuario::class, 'ofertas_guardadas', 'oferta_id', 'usuario_id');
    }
}
