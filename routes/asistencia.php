<?php

use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
Route::post('/asistencia/login', [AsistenciaController::class, 'login'])->name('asistencia.login');
Route::post('/asistencia/registro', [AsistenciaController::class, 'registrar'])->name('asistencia.registro');
Route::post('/asistencia/logout', [AsistenciaController::class, 'logout'])->name('asistencia.logout');
Route::post('/asistencia/admin/usuarios', [AsistenciaController::class, 'registrarDesdeAdmin'])->name('asistencia.admin.usuarios.crear');
Route::post('/asistencia/admin/alumnos', [AsistenciaController::class, 'crearAlumno'])->name('asistencia.admin.alumnos.crear');
Route::put('/asistencia/admin/alumnos/{registro}', [AsistenciaController::class, 'actualizarAlumno'])->name('asistencia.admin.alumnos.actualizar');
Route::delete('/asistencia/admin/alumnos/{registro}', [AsistenciaController::class, 'eliminarAlumno'])->name('asistencia.admin.alumnos.eliminar');
Route::post('/asistencia/admin/usuarios/profesor', [AsistenciaController::class, 'validarProfesor'])->name('asistencia.admin.usuarios.profesor');
Route::post('/asistencia/admin/profesores/materias', [AsistenciaController::class, 'asignarMateriasProfesor'])->name('asistencia.admin.profesores.materias');
Route::post('/asistencia/admin/profesor', [AsistenciaController::class, 'asignarProfesor'])->name('asistencia.admin.profesor');
Route::post('/asistencia/admin/alumno', [AsistenciaController::class, 'asignarAlumno'])->name('asistencia.admin.alumno');
Route::delete('/asistencia/admin/alumno', [AsistenciaController::class, 'quitarAlumno'])->name('asistencia.admin.alumno.quitar');
Route::get('/asistencia/alumno/materias/{materia}', [AsistenciaController::class, 'verMateriaAlumno'])->name('asistencia.alumno.materia');
Route::get('/asistencia/profesor/materias/{materia}', [AsistenciaController::class, 'verMateriaProfesor'])->name('asistencia.profesor.materia');
Route::get('/asistencia/profesor/materias/{materia}/listado', [AsistenciaController::class, 'listadoMateriaProfesor'])->name('asistencia.profesor.materia.listado');
Route::get('/asistencia/profesor/materias/{materia}/planilla', [AsistenciaController::class, 'planillaDiariaProfesor'])->name('asistencia.profesor.materia.planilla');
Route::post('/asistencia/profesor/materias/{materia}/planilla', [AsistenciaController::class, 'guardarPlanillaDiariaProfesor'])->name('asistencia.profesor.materia.planilla.guardar');
Route::delete('/asistencia/profesor/materias/{materia}/alumnos/{registro}', [AsistenciaController::class, 'quitarAlumnoProfesor'])->name('asistencia.profesor.materia.alumno.quitar');
Route::get('/asistencia/profesor/alumnos/{registro}', [AsistenciaController::class, 'perfilAlumnoProfesor'])->name('asistencia.profesor.alumno.perfil');
Route::post('/asistencia/profesor/alumnos', [AsistenciaController::class, 'asignarAlumnosProfesor'])->name('asistencia.profesor.alumnos');
Route::put('/asistencia/admin/carreras/{carrera}', [AsistenciaController::class, 'actualizarCarrera'])->name('asistencia.admin.carreras.actualizar');
Route::put('/asistencia/admin/materias/{materia}', [AsistenciaController::class, 'actualizarMateria'])->name('asistencia.admin.materias.actualizar');
