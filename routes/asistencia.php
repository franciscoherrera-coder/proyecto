<?php

use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
Route::post('/asistencia/admin/profesor', [AsistenciaController::class, 'asignarProfesor'])->name('asistencia.admin.profesor');
Route::post('/asistencia/admin/alumno', [AsistenciaController::class, 'asignarAlumno'])->name('asistencia.admin.alumno');
Route::delete('/asistencia/admin/alumno', [AsistenciaController::class, 'quitarAlumno'])->name('asistencia.admin.alumno.quitar');
