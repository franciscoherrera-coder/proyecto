<?php

use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
Route::post('/asistencia/login', [AsistenciaController::class, 'login'])->name('asistencia.login');
Route::post('/asistencia/registro', [AsistenciaController::class, 'registrar'])->name('asistencia.registro');
Route::post('/asistencia/logout', [AsistenciaController::class, 'logout'])->name('asistencia.logout');
Route::post('/asistencia/admin/usuarios', [AsistenciaController::class, 'registrarDesdeAdmin'])->name('asistencia.admin.usuarios.crear');
Route::post('/asistencia/admin/usuarios/profesor', [AsistenciaController::class, 'validarProfesor'])->name('asistencia.admin.usuarios.profesor');
Route::post('/asistencia/admin/profesores/materias', [AsistenciaController::class, 'asignarMateriasProfesor'])->name('asistencia.admin.profesores.materias');
Route::post('/asistencia/admin/profesor', [AsistenciaController::class, 'asignarProfesor'])->name('asistencia.admin.profesor');
Route::post('/asistencia/admin/alumno', [AsistenciaController::class, 'asignarAlumno'])->name('asistencia.admin.alumno');
Route::delete('/asistencia/admin/alumno', [AsistenciaController::class, 'quitarAlumno'])->name('asistencia.admin.alumno.quitar');
Route::post('/asistencia/profesor/alumnos', [AsistenciaController::class, 'asignarAlumnosProfesor'])->name('asistencia.profesor.alumnos');
Route::put('/asistencia/admin/carreras/{carrera}', [AsistenciaController::class, 'actualizarCarrera'])->name('asistencia.admin.carreras.actualizar');
Route::put('/asistencia/admin/materias/{materia}', [AsistenciaController::class, 'actualizarMateria'])->name('asistencia.admin.materias.actualizar');
