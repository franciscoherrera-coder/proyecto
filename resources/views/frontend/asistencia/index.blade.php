@extends('frontend.layout.main')

@section('content')
@php
    $rolActivo = auth()->check() ? ($rolUsuario ?? 'alumno') : null;
    $modoAcceso = old('auth_mode', request()->query('modo', 'login'));
    $adminTabActivo = request()->query('admin_tab', 'usuarios');
    $cantidadAsignaciones = 0;
    if ($tieneTablaAsignaciones ?? false) {
        foreach (($materias ?? collect()) as $materia) {
            $cantidadAsignaciones += $materia->alumnos->count();
        }
    }
@endphp
<style>
    .asistencia-page {
        background: #f4f6f8;
        color: #1f2933;
        min-height: 72vh;
        padding: 48px 0;
    }

    .asistencia-hero {
        background: #ffffff;
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        padding: 28px;
        box-shadow: 0 10px 30px rgba(31, 41, 51, 0.08);
    }

    .asistencia-kicker {
        color: #700101;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .asistencia-title {
        color: #111827;
        font-size: clamp(2rem, 5vw, 3.3rem);
        font-weight: 800;
        line-height: 1.05;
        margin: 8px 0 12px;
    }

    .asistencia-copy {
        color: #52606d;
        font-size: 1.05rem;
        max-width: 760px;
    }

    .role-selector {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 26px;
    }

    .role-button {
        background: #ffffff;
        border: 2px solid #d9e2ec;
        border-radius: 8px;
        color: #243b53;
        cursor: pointer;
        display: flex;
        gap: 12px;
        align-items: center;
        min-height: 86px;
        padding: 16px;
        text-align: left;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        width: 100%;
    }

    .role-button:hover,
    .role-button.active {
        border-color: #700101;
        box-shadow: 0 10px 24px rgba(112, 1, 1, 0.13);
        transform: translateY(-2px);
    }

    .role-icon {
        align-items: center;
        background: #fce8e6;
        border-radius: 8px;
        color: #700101;
        display: flex;
        flex: 0 0 46px;
        height: 46px;
        justify-content: center;
    }

    .role-button strong {
        display: block;
        font-size: 1.05rem;
        line-height: 1.1;
    }

    .role-button span:last-child {
        color: #627d98;
        display: block;
        font-size: 0.86rem;
        font-weight: 600;
        margin-top: 4px;
    }

    .dashboard-panel {
        display: none;
        margin-top: 24px;
    }

    .dashboard-panel.active {
        display: block;
    }

    .dashboard-shell {
        background: #ffffff;
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(31, 41, 51, 0.08);
        overflow: hidden;
    }

    .dashboard-header {
        align-items: center;
        background: #111827;
        color: #ffffff;
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        justify-content: space-between;
        padding: 20px 24px;
    }

    .dashboard-header h2 {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0;
    }

    .dashboard-header p {
        color: #cbd2d9;
        margin: 4px 0 0;
    }

    .status-pill {
        background: #e3f9e5;
        border-radius: 999px;
        color: #0b6b35;
        font-size: 0.82rem;
        font-weight: 800;
        padding: 8px 13px;
        white-space: nowrap;
    }

    .dashboard-body {
        padding: 24px;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .metric {
        background: #f8fafc;
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        padding: 16px;
    }

    .metric small {
        color: #627d98;
        display: block;
        font-weight: 800;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .metric strong {
        color: #111827;
        display: block;
        font-size: 1.7rem;
        line-height: 1;
    }

    .work-grid {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 18px;
    }

    .tool-panel {
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        padding: 18px;
    }

    .tool-panel h3 {
        color: #111827;
        font-size: 1.08rem;
        font-weight: 800;
        margin-bottom: 14px;
    }

    .class-row,
    .student-row,
    .report-row {
        align-items: center;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        padding: 12px 0;
    }

    .class-row:last-child,
    .student-row:last-child,
    .report-row:last-child {
        border-bottom: 0;
    }

    .row-title {
        color: #243b53;
        font-weight: 800;
    }

    .row-subtitle {
        color: #627d98;
        font-size: 0.88rem;
        font-weight: 600;
    }

    .asistencia-btn {
        background: #700101;
        border: 0;
        border-radius: 8px;
        color: #ffffff;
        font-weight: 800;
        padding: 9px 14px;
        white-space: nowrap;
    }

    .asistencia-btn.secondary {
        background: #e4e7eb;
        color: #243b53;
    }

    .qr-box {
        align-items: center;
        background:
            linear-gradient(90deg, #111827 10px, transparent 10px) 0 0 / 28px 28px,
            linear-gradient(#111827 10px, transparent 10px) 0 0 / 28px 28px,
            #ffffff;
        border: 10px solid #f8fafc;
        box-shadow: inset 0 0 0 1px #cbd2d9;
        display: flex;
        height: 180px;
        justify-content: center;
        margin: 0 auto 16px;
        max-width: 180px;
    }

    .qr-box span {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        color: #700101;
        font-weight: 900;
        padding: 8px 10px;
    }

    .progress {
        background-color: #e4e7eb;
        height: 10px;
    }

    .progress-bar {
        background-color: #700101;
    }

    .badge-soft {
        background: #fce8e6;
        border-radius: 999px;
        color: #700101;
        font-size: 0.78rem;
        font-weight: 800;
        padding: 6px 10px;
        white-space: nowrap;
    }

    .admin-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 22px;
    }

    .admin-nav-button {
        background: #f8fafc;
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        color: #243b53;
        font-weight: 900;
        padding: 10px 14px;
        text-transform: uppercase;
    }

    .admin-nav-button.active {
        background: #700101;
        border-color: #700101;
        color: #ffffff;
    }

    .admin-tab-panel {
        display: none;
    }

    .admin-tab-panel.active {
        display: block;
    }

    .admin-create-details summary {
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .admin-create-body {
        display: grid;
        grid-template-rows: 0fr;
        overflow: hidden;
        transition: grid-template-rows 0.28s ease;
    }

    .admin-create-details[open] .admin-create-body {
        grid-template-rows: 1fr;
    }

    .admin-create-body > div {
        min-height: 0;
        padding-top: 12px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr 0.7fr auto;
        gap: 12px;
        margin-bottom: 16px;
    }

    .editable-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .auth-shell {
        display: flex;
        justify-content: center;
        margin-top: 24px;
    }

    .auth-card {
        background: #ffffff;
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        box-shadow: 0 18px 38px rgba(31, 41, 51, 0.12);
        max-width: 520px;
        overflow: hidden;
        width: 100%;
    }

    .auth-switch {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .auth-switch a {
        background: #f8fafc;
        color: #243b53;
        font-weight: 900;
        padding: 14px;
        text-align: center;
        text-decoration: none;
        text-transform: uppercase;
    }

    .auth-switch a.active {
        background: #700101;
        color: #ffffff;
    }

    .auth-form {
        padding: 24px;
    }

    .auth-form h2 {
        color: #111827;
        font-size: 1.45rem;
        font-weight: 900;
        margin-bottom: 18px;
    }

    .auth-role-fields {
        display: none;
    }

    .auth-role-fields.active {
        display: block;
    }

    .session-bar {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: space-between;
        margin-top: 22px;
    }

    .asistencia-modal {
        align-items: center;
        background: rgba(17, 24, 39, 0.58);
        display: none;
        inset: 0;
        justify-content: center;
        padding: 18px;
        position: fixed;
        z-index: 1050;
    }

    .asistencia-modal.active {
        display: flex;
    }

    .asistencia-modal-dialog {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(17, 24, 39, 0.24);
        max-height: 88vh;
        max-width: 920px;
        overflow: hidden;
        width: 100%;
    }

    .asistencia-modal-header,
    .asistencia-modal-footer {
        align-items: center;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        padding: 16px 18px;
    }

    .asistencia-modal-header {
        border-bottom: 1px solid #e4e7eb;
    }

    .asistencia-modal-body {
        max-height: 62vh;
        overflow-y: auto;
        padding: 18px;
    }

    .asistencia-modal-footer {
        border-top: 1px solid #e4e7eb;
    }

    .materia-check-row {
        align-items: center;
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        display: flex;
        gap: 8px;
        min-height: 34px;
        padding: 4px 8px;
    }

    .edit-stack {
        display: grid;
        gap: 14px;
        max-height: 620px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .edit-item {
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        padding: 16px;
    }

    .edit-item.is-hidden {
        display: none;
    }

    .edit-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .edit-form-grid .wide {
        grid-column: 1 / -1;
    }

    .autocomplete-field {
        position: relative;
    }

    .autocomplete-results {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        box-shadow: 0 14px 30px rgba(31, 41, 51, 0.14);
        display: none;
        left: 0;
        max-height: 230px;
        overflow-y: auto;
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        z-index: 20;
    }

    .autocomplete-results.active {
        display: block;
    }

    .autocomplete-option {
        background: #ffffff;
        border: 0;
        border-bottom: 1px solid #edf2f7;
        color: #243b53;
        display: block;
        font-weight: 700;
        padding: 10px 12px;
        text-align: left;
        width: 100%;
    }

    .autocomplete-option:hover,
    .autocomplete-option:focus {
        background: #fce8e6;
        color: #700101;
        outline: none;
    }

    .autocomplete-empty {
        color: #627d98;
        font-weight: 700;
        padding: 10px 12px;
    }

    .table-responsive {
        border: 1px solid #e4e7eb;
        border-radius: 8px;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        color: #52606d;
        font-size: 0.78rem;
        text-transform: uppercase;
    }

    @media (max-width: 992px) {
        .metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .work-grid {
            grid-template-columns: 1fr;
        }

        .filter-grid,
        .editable-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .asistencia-page {
            padding: 24px 0;
        }

        .asistencia-hero,
        .dashboard-body {
            padding: 18px;
        }

        .role-selector,
        .metric-grid {
            grid-template-columns: 1fr;
        }

        .class-row,
        .student-row,
        .report-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .asistencia-btn {
            width: 100%;
        }

        .edit-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="asistencia-page">
    <div class="container">
        @unless (auth()->check() && $rolActivo === 'admin')
        <section class="asistencia-hero">
            <div class="asistencia-kicker">{{ auth()->check() ? 'Panel validado' : 'Acceso requerido' }}</div>
            <h1 class="asistencia-title">Sistema de asistencia</h1>
            <p class="asistencia-copy">
                @auth
                    Estás ingresando como {{ auth()->user()->name }}. El sistema muestra solamente la vista correspondiente a tu rol.
                @else
                    Iniciá sesión para entrar al panel de asistencia o registrate para pedir un acceso como alumno, profesor o admin.
                @endauth
            </p>

            @auth
                <div class="session-bar">
                    <span class="status-pill">{{ ucfirst($rolActivo) }}</span>
                    <form action="{{ route('asistencia.logout') }}" method="POST" class="m-0">
                        @csrf
                        <button class="asistencia-btn secondary" type="submit">Cerrar sesión</button>
                    </form>
                </div>
            @endauth
        </section>
        @endunless

        @if (session('status'))
            <div class="alert alert-success mt-4">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mt-4">
                <strong>Revisa los datos:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @guest
            <section class="auth-shell" aria-label="Acceso asistencia">
                <div class="auth-card">
                    <div class="auth-switch">
                        <a href="{{ route('asistencia.index', ['modo' => 'login']) }}" class="{{ $modoAcceso === 'login' ? 'active' : '' }}">
                            <i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión
                        </a>
                        <a href="{{ route('asistencia.index', ['modo' => 'registro']) }}" class="{{ $modoAcceso === 'registro' ? 'active' : '' }}">
                            <i class="fa-solid fa-user-plus"></i> Registrarse
                        </a>
                    </div>

                    @if ($modoAcceso === 'registro')
                        <form class="auth-form" action="{{ route('asistencia.registro') }}" method="POST">
                            @csrf
                            <input type="hidden" name="auth_mode" value="registro">
                            <input type="hidden" name="rol" value="alumno">
                            <h2>Crea tu acceso</h2>

                            <div class="edit-form-grid">
                                <div>
                                    <label class="form-label" for="registro_nombre">Nombre</label>
                                    <input id="registro_nombre" class="form-control" name="nombre" value="{{ old('nombre') }}" required>
                                </div>
                                <div class="js-apellido-field">
                                    <label class="form-label" for="registro_apellido">Apellido</label>
                                    <input id="registro_apellido" class="form-control" name="apellido" value="{{ old('apellido') }}" required>
                                </div>
                            </div>

                            <div class="auth-role-fields active mt-3" data-role-fields="alumno">
                                <div class="edit-form-grid">
                                    <div>
                                        <label class="form-label" for="registro_dni">DNI</label>
                                        <input id="registro_dni" class="form-control" type="number" name="dni" value="{{ old('dni') }}" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="registro_cuil">CUIL</label>
                                        <input id="registro_cuil" class="form-control" type="number" name="cuil" value="{{ old('cuil') }}" placeholder="Opcional">
                                    </div>
                                    <div class="wide">
                                        <label class="form-label" for="registro_carrera">Carrera</label>
                                        <select id="registro_carrera" class="form-select" name="carrera_id">
                                            <option value="">Sin carrera</option>
                                            @foreach (($carreras ?? collect()) as $carrera)
                                                <option value="{{ $carrera->id }}" {{ old('carrera_id') == $carrera->id ? 'selected' : '' }}>{{ $carrera->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <label class="form-label" for="registro_email">Correo electrónico</label>
                                <input id="registro_email" class="form-control" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                            </div>
                            <div class="edit-form-grid">
                                <div>
                                    <label class="form-label" for="registro_password">Contraseña</label>
                                    <input id="registro_password" class="form-control" type="password" name="password" autocomplete="new-password" required>
                                </div>
                                <div>
                                    <label class="form-label" for="registro_password_confirmation">Confirmar contraseña</label>
                                    <input id="registro_password_confirmation" class="form-control" type="password" name="password_confirmation" autocomplete="new-password" required>
                                </div>
                            </div>

                            <button class="asistencia-btn w-100 mt-3" type="submit">Registrarme</button>
                        </form>
                    @else
                        <form class="auth-form" action="{{ route('asistencia.login') }}" method="POST">
                            @csrf
                            <h2>Iniciá sesión</h2>
                            <div class="mb-3">
                                <label class="form-label" for="login_email">Correo electrónico</label>
                                <input id="login_email" class="form-control" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="login_password">Contraseña</label>
                                <input id="login_password" class="form-control" type="password" name="password" autocomplete="current-password" required>
                            </div>
                            <div class="form-check mb-3">
                                <input id="login_remember" class="form-check-input" type="checkbox" name="remember" value="1">
                                <label class="form-check-label" for="login_remember">Recordarme</label>
                            </div>
                            <button class="asistencia-btn w-100" type="submit">Entrar</button>
                        </form>
                    @endif
                </div>
            </section>
        @else

        @if ($rolActivo === 'alumno')
        <section id="panel-alumno" class="dashboard-panel active" role="tabpanel">
            <div class="dashboard-shell">
                <div class="dashboard-header">
                    <div>
                        <h2>Vista Alumno</h2>
                        <p>Hola, Martina Pérez. Estos son tus datos de asistencia de ejemplo.</p>
                    </div>
                    <span class="status-pill">Regular</span>
                </div>

                <div class="dashboard-body">
                    <div class="metric-grid">
                        <div class="metric">
                            <small>Asistencia total</small>
                            <strong>86%</strong>
                        </div>
                        <div class="metric">
                            <small>Presentes</small>
                            <strong>24</strong>
                        </div>
                        <div class="metric">
                            <small>Ausencias</small>
                            <strong>4</strong>
                        </div>
                        <div class="metric">
                            <small>Próxima clase</small>
                            <strong>18:00</strong>
                        </div>
                    </div>

                    <div class="work-grid">
                        <div class="tool-panel">
                            <h3>Mis materias</h3>
                            <div class="class-row">
                                <div>
                                    <div class="row-title">Programación II</div>
                                    <div class="row-subtitle">Lunes y miércoles - Aula 4</div>
                                </div>
                                <span class="badge-soft">92%</span>
                            </div>
                            <div class="class-row">
                                <div>
                                    <div class="row-title">Base de Datos</div>
                                    <div class="row-subtitle">Martes - Laboratorio</div>
                                </div>
                                <span class="badge-soft">84%</span>
                            </div>
                            <div class="class-row">
                                <div>
                                    <div class="row-title">Inglés Técnico</div>
                                    <div class="row-subtitle">Jueves - Aula 2</div>
                                </div>
                                <span class="badge-soft">78%</span>
                            </div>
                        </div>

                        <div class="tool-panel">
                            <h3>Credencial QR</h3>
                            <div class="qr-box"><span>ALU-238</span></div>
                            <p class="row-subtitle mb-3">Código de muestra para registrar ingreso en clase.</p>
                            <button class="asistencia-btn w-100" type="button">Mostrar QR</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if ($rolActivo === 'profesor')
        <section id="panel-profesor" class="dashboard-panel active" role="tabpanel">
            <div class="d-flex justify-content-end mb-3">
                <form action="{{ route('asistencia.logout') }}" method="POST" class="m-0">
                    @csrf
                    <button class="asistencia-btn secondary" type="submit">Cerrar sesión</button>
                </form>
            </div>
            <div class="dashboard-shell">
                <div class="dashboard-header">
                    <div>
                        <h2>Mis materias</h2>
                        <p>Seleccioná una materia asignada para sumar alumnos.</p>
                    </div>
                    <span class="status-pill">{{ ($materiasProfesor ?? collect())->count() }} materia(s)</span>
                </div>

                <div class="dashboard-body">
                    @if (($materiasProfesor ?? collect())->isEmpty())
                        <div class="tool-panel">
                            <p class="text-muted mb-0">Todavía no tenés materias asignadas por el admin.</p>
                        </div>
                    @else
                        @if (!($tieneTablaAsignaciones ?? false))
                            <div class="alert alert-warning">Falta ejecutar la migración de asignaciones para poder agregar alumnos. Mientras tanto, podés ver tus materias asignadas.</div>
                        @endif
                        <div class="edit-stack">
                            @foreach (($materiasProfesor ?? collect())->groupBy('carrera_id') as $materiasCarrera)
                                @php
                                    $carreraPanel = $materiasCarrera->first()->deCarrera ?? null;
                                    $cantidadAlumnosCarrera = 0;
                                    if ($tieneTablaAsignaciones ?? false) {
                                        foreach ($materiasCarrera as $materiaContador) {
                                            $cantidadAlumnosCarrera += $materiaContador->alumnos->count();
                                        }
                                    }
                                @endphp
                                <details class="tool-panel">
                                    <summary class="d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <h3 class="mb-1">{{ $carreraPanel->descripcion ?? 'Sin carrera' }}</h3>
                                            <p class="row-subtitle mb-0">{{ $materiasCarrera->count() }} materia(s) asignada(s)</p>
                                        </div>
                                        <span class="badge-soft">{{ $cantidadAlumnosCarrera }} alumno(s)</span>
                                    </summary>

                                    <div class="edit-stack mt-3" style="gap: 8px; max-height: none;">
                                        @foreach ($materiasCarrera as $materiaProfesor)
                                            <details class="edit-item">
                                                <summary class="d-flex align-items-center justify-content-between gap-2">
                                                    <span>
                                                        <strong>{{ $materiaProfesor->descripcion }}</strong>
                                                        <span class="row-subtitle ms-2">
                                                            @if ($materiaProfesor->deAnio)
                                                                {{ $materiaProfesor->deAnio->anio ?? $materiaProfesor->deAnio->descripcion }}
                                                            @else
                                                                Sin aÃ±o
                                                            @endif
                                                        </span>
                                                    </span>
                                                    <span class="badge-soft">{{ ($tieneTablaAsignaciones ?? false) ? $materiaProfesor->alumnos->count() : 0 }} alumno(s)</span>
                                                </summary>

                                                <div class="mt-3">
                                                    @if ($tieneTablaAsignaciones ?? false)
                                                        <form action="{{ route('asistencia.profesor.alumnos') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="materia_id" value="{{ $materiaProfesor->id }}">
                                                            <label class="form-label" for="profesor_alumnos_{{ $materiaProfesor->id }}">Agregar alumnos</label>
                                                            <select id="profesor_alumnos_{{ $materiaProfesor->id }}" class="form-select" name="registro_ids[]" multiple size="7" required>
                                                                @foreach (($alumnos ?? collect()) as $alumno)
                                                                    <option value="{{ $alumno->id }}">{{ $alumno->apellido }}, {{ $alumno->nombre }} - DNI {{ $alumno->dni }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button class="asistencia-btn mt-3" type="submit">Agregar seleccionados</button>
                                                        </form>
                                                    @else
                                                        <div class="alert alert-warning mb-0">No se pueden agregar alumnos hasta ejecutar la migración de asignaciones.</div>
                                                    @endif

                                                    <div class="mt-3">
                                                        <div class="row-title mb-2">Alumnos en {{ $materiaProfesor->descripcion }}</div>
                                                        @if ($tieneTablaAsignaciones ?? false)
                                                            @forelse ($materiaProfesor->alumnos as $alumnoMateria)
                                                                <div class="d-flex align-items-center justify-content-between gap-2 border rounded px-2 py-1 mb-2">
                                                                    <div class="text-truncate">
                                                                        <strong>{{ $alumnoMateria->apellido }}, {{ $alumnoMateria->nombre }}</strong>
                                                                        <span class="row-subtitle ms-2">DNI {{ $alumnoMateria->dni }} - {{ $alumnoMateria->email }}</span>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <p class="text-muted mb-0">Todavía no agregaste alumnos a esta materia.</p>
                                                            @endforelse
                                                        @else
                                                            <p class="text-muted mb-0">La lista de alumnos va a aparecer cuando exista la tabla de asignaciones.</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </details>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @endif

        @if ($rolActivo === 'admin')
        <section id="panel-admin" class="dashboard-panel active" role="tabpanel">
            <div class="d-flex justify-content-end mb-3">
                <form action="{{ route('asistencia.logout') }}" method="POST" class="m-0">
                    @csrf
                    <button class="asistencia-btn secondary" type="submit">Cerrar sesión</button>
                </form>
            </div>
            <div class="dashboard-shell">
                <div class="dashboard-header">
                    <div>
                        <h2>Vista Admin</h2>
                        <p>Gestión de materias, profesores y alumnos para el sistema de asistencia.</p>
                    </div>
                    <span class="status-pill">Administración real</span>
                </div>

                <div class="dashboard-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Revisa los datos:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                

                    <div id="admin-tab-usuarios" class="admin-tab-panel active">
                    <div class="metric-grid">
                        <div class="metric">
                            <small>Materias</small>
                            <strong>{{ ($materias ?? collect())->count() }}</strong>
                        </div>
                        <div class="metric">
                            <small>Profesores</small>
                            <strong>{{ ($profesores ?? collect())->count() }}</strong>
                        </div>
                        <div class="metric">
                            <small>Alumnos</small>
                            <strong>{{ ($alumnos ?? collect())->count() }}</strong>
                        </div>
                        <div class="metric">
                            <small>Asignaciones</small>
                            <strong>{{ $cantidadAsignaciones }}</strong>
                        </div>
                    </div>

                    @if ($adminPuedeCrearAdmins ?? false)
                    <div class="tool-panel mb-4">
                        <details class="admin-create-details">
                            <summary class="asistencia-btn d-inline-block">Crear nuevo admin</summary>
                            <div class="admin-create-body">
                            <div>
                        <div class="mb-3 d-none">
                            <label class="form-label" for="admin_tipo_usuario">Tipo de usuario</label>
                            <select id="admin_tipo_usuario" class="form-select">
                                <option value="admin" selected>Admin</option>
                            </select>
                        </div>
                        <div class="editable-grid">
                            <form class="edit-item is-hidden js-admin-user-form" data-admin-user-form="alumno" action="{{ route('asistencia.admin.usuarios.crear') }}" method="POST">
                                @csrf
                                <input type="hidden" name="rol" value="alumno">
                                <div class="edit-form-grid">
                                    <div>
                                        <label class="form-label" for="admin_alumno_nombre">Nombre</label>
                                        <input id="admin_alumno_nombre" class="form-control" name="nombre" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admin_alumno_apellido">Apellido</label>
                                        <input id="admin_alumno_apellido" class="form-control" name="apellido" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admin_alumno_dni">DNI</label>
                                        <input id="admin_alumno_dni" class="form-control" type="number" name="dni" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admin_alumno_cuil">CUIL</label>
                                        <input id="admin_alumno_cuil" class="form-control" type="number" name="cuil" placeholder="Opcional">
                                    </div>
                                    <div class="wide">
                                        <label class="form-label" for="admin_alumno_email">Email</label>
                                        <input id="admin_alumno_email" class="form-control" type="email" name="email" required>
                                    </div>
                                    <div class="wide">
                                        <label class="form-label" for="admin_alumno_carrera">Carrera</label>
                                        <select id="admin_alumno_carrera" class="form-select" name="carrera_id">
                                            <option value="">Sin carrera</option>
                                            @foreach (($carreras ?? collect()) as $carrera)
                                                <option value="{{ $carrera->id }}">{{ $carrera->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admin_alumno_password">Contraseña</label>
                                        <input id="admin_alumno_password" class="form-control" type="password" name="password" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admin_alumno_password_confirmation">Confirmar</label>
                                        <input id="admin_alumno_password_confirmation" class="form-control" type="password" name="password_confirmation" required>
                                    </div>
                                </div>
                                <button class="asistencia-btn mt-3" type="submit">Crear alumno</button>
                            </form>

                            <form class="edit-item is-hidden js-admin-user-form" data-admin-user-form="profesor" action="{{ route('asistencia.admin.usuarios.crear') }}" method="POST">
                                @csrf
                                <input type="hidden" name="rol" value="profesor">
                                <div class="edit-form-grid">
                                    <div>
                                        <label class="form-label" for="admin_profesor_nombre">Nombre</label>
                                        <input id="admin_profesor_nombre" class="form-control" name="nombre" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admin_profesor_apellido">Apellido</label>
                                        <input id="admin_profesor_apellido" class="form-control" name="apellido" required>
                                    </div>
                                    <div class="wide">
                                        <label class="form-label" for="admin_profesor_email">Email</label>
                                        <input id="admin_profesor_email" class="form-control" type="email" name="email" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admin_profesor_password">Contraseña</label>
                                        <input id="admin_profesor_password" class="form-control" type="password" name="password" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admin_profesor_password_confirmation">Confirmar</label>
                                        <input id="admin_profesor_password_confirmation" class="form-control" type="password" name="password_confirmation" required>
                                    </div>
                                </div>
                                <button class="asistencia-btn mt-3" type="submit">Crear profesor</button>
                            </form>

                            <form class="edit-item js-admin-user-form" data-admin-user-form="admin" action="{{ route('asistencia.admin.usuarios.crear') }}" method="POST">
                                @csrf
                                <input type="hidden" name="rol" value="admin">
                                <div class="mb-3">
                                    <label class="form-label" for="admin_usuario_nombre">Nombre</label>
                                    <input id="admin_usuario_nombre" class="form-control" name="nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="admin_usuario_email">Email</label>
                                    <input id="admin_usuario_email" class="form-control" type="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="admin_usuario_carrera">Carrera</label>
                                    <select id="admin_usuario_carrera" class="form-select" name="carrera_id" required>
                                        <option value="">Seleccionar carrera</option>
                                        @foreach (($carreras ?? collect()) as $carrera)
                                            <option value="{{ $carrera->id }}">{{ $carrera->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="admin_usuario_password">Contraseña</label>
                                    <input id="admin_usuario_password" class="form-control" type="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="admin_usuario_password_confirmation">Confirmar contraseña</label>
                                    <input id="admin_usuario_password_confirmation" class="form-control" type="password" name="password_confirmation" required>
                                </div>
                                <button class="asistencia-btn" type="submit">Crear admin</button>
                            </form>
                        </div>
                            </div>
                            </div>
                        </details>
                    </div>
                    @endif

                    @if ($adminPuedeCrearAdmins ?? false)
                    <div class="tool-panel mb-4">
                        <div class="editable-grid">
                            <div>
                                <label class="form-label" for="buscar_directora_profesor">Buscar profesores</label>
                                <input id="buscar_directora_profesor" class="form-control" type="text" placeholder="Buscar por nombre o email">
                                <div class="edit-stack mt-3" style="gap: 6px; max-height: 280px;">
                                    @forelse (($usuariosProfesores ?? collect()) as $usuarioProfesor)
                                        <div class="js-directora-profesor d-flex align-items-center gap-2 border rounded px-2 py-1" data-search="{{ $usuarioProfesor->name }} {{ $usuarioProfesor->email }}">
                                            <div class="text-truncate flex-grow-1">
                                                <strong>{{ $usuarioProfesor->name }}</strong>
                                                <span class="row-subtitle text-truncate ms-2">{{ $usuarioProfesor->email }} - {{ $usuarioProfesor->materias_asignadas_count }} materia(s)</span>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">Todavía no hay profesores validados.</p>
                                    @endforelse
                                    <p id="directora_profesores_vacio" class="text-muted mb-0 d-none">No se encontraron profesores.</p>
                                </div>
                            </div>
                            <div>
                                <label class="form-label" for="buscar_directora_alumno">Buscar alumnos</label>
                                <input id="buscar_directora_alumno" class="form-control" type="text" placeholder="Buscar por nombre, email o DNI">
                                <div class="edit-stack mt-3" style="gap: 6px; max-height: 280px;">
                                    @forelse (($alumnos ?? collect()) as $alumno)
                                        <div class="js-directora-alumno d-flex align-items-center gap-2 border rounded px-2 py-1" data-search="{{ $alumno->apellido }} {{ $alumno->nombre }} {{ $alumno->email }} {{ $alumno->dni }}">
                                            <div class="text-truncate flex-grow-1">
                                                <strong>{{ $alumno->apellido }}, {{ $alumno->nombre }}</strong>
                                                <span class="row-subtitle text-truncate ms-2">{{ $alumno->email }} - DNI {{ $alumno->dni }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">Todavía no hay alumnos registrados.</p>
                                    @endforelse
                                    <p id="directora_alumnos_vacio" class="text-muted mb-0 d-none">No se encontraron alumnos.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="tool-panel mb-4">
                        <details>
                            <summary class="asistencia-btn d-inline-block">Validar profesores</summary>
                            <form class="mt-3" action="{{ route('asistencia.admin.usuarios.profesor') }}" method="POST">
                                @csrf
                                <div class="filter-grid" style="grid-template-columns: 1fr;">
                                    <div>
                                        <label class="form-label" for="buscar_usuario_sin_carrera">Buscar usuario sin carrera</label>
                                        <input id="buscar_usuario_sin_carrera" class="form-control" type="text" placeholder="Buscar por nombre o email">
                                    </div>
                                </div>
                                <div class="edit-stack" style="gap: 6px; max-height: 260px;">
                                    @forelse (($usuariosSinCarrera ?? collect()) as $usuarioSinCarrera)
                                        <label class="js-usuario-sin-carrera d-flex align-items-center gap-2 border rounded px-2 py-1" data-search="{{ $usuarioSinCarrera->name }} {{ $usuarioSinCarrera->email }}">
                                            <input class="form-check-input m-0" type="checkbox" name="user_ids[]" value="{{ $usuarioSinCarrera->id }}">
                                            <strong class="text-nowrap">{{ $usuarioSinCarrera->name }}</strong>
                                            <span class="row-subtitle text-truncate">{{ $usuarioSinCarrera->email }}</span>
                                        </label>
                                    @empty
                                        <p class="text-muted mb-0">No hay usuarios registrados sin carrera para validar.</p>
                                    @endforelse
                                    <p id="usuarios_sin_carrera_vacio" class="text-muted mb-0 d-none">No se encontraron usuarios con esa búsqueda.</p>
                                </div>
                                <button class="asistencia-btn mt-3" type="submit">Validar seleccionados como profesor</button>
                            </form>
                        </details>
                    </div>

                    <div class="tool-panel mb-4">
                        <h3>Profesores validados</h3>
                        <div class="filter-grid" style="grid-template-columns: 1fr;">
                            <div>
                                <label class="form-label" for="buscar_profesor_validado">Buscar profesor</label>
                                <input id="buscar_profesor_validado" class="form-control" type="text" placeholder="Buscar por nombre o email">
                            </div>
                        </div>
                        <div class="edit-stack" style="gap: 6px; max-height: 280px;">
                            @forelse (($usuariosProfesores ?? collect()) as $usuarioProfesor)
                                <div class="js-profesor-validado d-flex align-items-center gap-2 border rounded px-2 py-1" data-search="{{ $usuarioProfesor->name }} {{ $usuarioProfesor->email }}">
                                    <div class="text-truncate flex-grow-1">
                                        <strong>{{ $usuarioProfesor->name }}</strong>
                                        <span class="row-subtitle text-truncate ms-2">{{ $usuarioProfesor->email }} - {{ $usuarioProfesor->materias_asignadas_count }} materia(s)</span>
                                    </div>
                                    <button class="asistencia-btn secondary py-1 px-2" type="button" data-open-profesor-modal="materias-profesor-{{ $usuarioProfesor->id }}">Materias</button>
                                </div>
                            @empty
                                <p class="text-muted mb-0">Todavía no hay profesores validados.</p>
                            @endforelse
                            <p id="profesores_validados_vacio" class="text-muted mb-0 d-none">No se encontraron profesores.</p>
                        </div>
                    </div>
                    @endif

                    <div class="work-grid d-none">
                        <div class="tool-panel">
                            <h3>Asignar alumno a materia</h3>
                            <form action="{{ route('asistencia.admin.alumno') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="materia_alumno" class="form-label">Materia</label>
                                    <div class="autocomplete-field">
                                        <input id="materia_alumno" class="form-control js-buscador" type="text" placeholder="Buscar por materia, carrera o año" autocomplete="off" data-hidden-target="materia_alumno_id" data-source="materias" required>
                                        <input id="materia_alumno_id" type="hidden" name="materia_id">
                                        <div class="autocomplete-results" data-results-for="materia_alumno"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="registro_alumno" class="form-label">Alumno</label>
                                    <div class="autocomplete-field">
                                        <input id="registro_alumno" class="form-control js-buscador" type="text" placeholder="Buscar alumno por nombre, apellido o DNI" autocomplete="off" data-hidden-target="registro_alumno_id" data-source="alumnos" required>
                                        <input id="registro_alumno_id" type="hidden" name="registro_id">
                                        <div class="autocomplete-results" data-results-for="registro_alumno"></div>
                                    </div>
                                </div>
                                <button class="asistencia-btn" type="submit" {{ !($tieneTablaAsignaciones ?? false) ? 'disabled' : '' }}>Asignar alumno</button>
                            </form>
                        </div>
                    </div>

                    <div class="tool-panel mt-4">
                        <h3>Materias configuradas</h3>
                        <div class="filter-grid">
                            <div>
                                <label for="filtro_materia_configurada" class="form-label">Buscar materia</label>
                                <input id="filtro_materia_configurada" class="form-control" type="text" placeholder="Buscar por materia, profesor, alumno o carrera">
                            </div>
                            <div>
                                <label for="filtro_carrera_configurada" class="form-label">Carrera</label>
                                <select id="filtro_carrera_configurada" class="form-select">
                                    <option value="">Todas</option>
                                    @foreach (($carreras ?? collect()) as $carrera)
                                        <option value="{{ $carrera->id }}">{{ $carrera->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="filtro_anio_configurado" class="form-label">Año</label>
                                <select id="filtro_anio_configurado" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach (($anios ?? collect()) as $anio)
                                        <option value="{{ $anio->id }}">{{ $anio->descripcion ?? $anio->anio }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex align-items-end">
                                <button id="limpiar_filtros_configurados" class="asistencia-btn secondary" type="button">Limpiar</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Materia</th>
                                        <th>Profesor</th>
                                        <th>Alumnos asignados</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse (($materias ?? collect()) as $materia)
                                        @php
                                            $alumnosTextoFiltro = '';
                                            if ($tieneTablaAsignaciones ?? false) {
                                                foreach ($materia->alumnos as $alumnoFiltro) {
                                                    $alumnosTextoFiltro .= ' ' . $alumnoFiltro->apellido . ' ' . $alumnoFiltro->nombre . ' ' . $alumnoFiltro->dni;
                                                }
                                            }
                                            $textoFiltroMateria = $materia->descripcion . ' ' .
                                                ($materia->deCarrera->descripcion ?? '') . ' ' .
                                                ($materia->deAnio->descripcion ?? '') . ' ' .
                                                ($materia->deAnio->anio ?? '') . ' ' .
                                                ($materia->horario && $materia->horario->profesor ? $materia->horario->profesor->apellido . ' ' . $materia->horario->profesor->nombre : '') . ' ' .
                                                $alumnosTextoFiltro;
                                        @endphp
                                        <tr class="js-materia-configurada d-none"
                                            data-search="{{ $textoFiltroMateria }}"
                                            data-carrera-id="{{ $materia->carrera_id }}"
                                            data-anio-id="{{ $materia->anio_id }}">
                                            <td>
                                                <strong>{{ $materia->descripcion }}</strong>
                                                <div class="row-subtitle">
                                                    {{ $materia->deCarrera->descripcion ?? 'Sin carrera' }}
                                                    @if ($materia->deAnio)
                                                        - {{ $materia->deAnio->anio ?? $materia->deAnio->descripcion }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if ($materia->horario && $materia->horario->profesor)
                                                    {{ $materia->horario->profesor->apellido }}, {{ $materia->horario->profesor->nombre }}
                                                @else
                                                    <span class="text-muted">Sin profesor</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($tieneTablaAsignaciones ?? false)
                                                    @forelse ($materia->alumnos as $alumno)
                                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                            <span class="badge-soft">{{ $alumno->apellido }}, {{ $alumno->nombre }}</span>
                                                            <form action="{{ route('asistencia.admin.alumno.quitar') }}" method="POST" class="m-0">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="materia_id" value="{{ $materia->id }}">
                                                                <input type="hidden" name="registro_id" value="{{ $alumno->id }}">
                                                                <button class="btn btn-sm btn-outline-danger" type="submit">Quitar</button>
                                                            </form>
                                                        </div>
                                                    @empty
                                                        <span class="text-muted">Sin alumnos</span>
                                                    @endforelse
                                                @else
                                                    <span class="text-muted">Pendiente de migración</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge-soft">{{ ($tieneTablaAsignaciones ?? false) ? $materia->alumnos->count() : 0 }} alumno(s)</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Todavía no hay materias cargadas.</td>
                                        </tr>
                                    @endforelse
                                    <tr id="materias_configuradas_vacio">
                                            <td colspan="4" class="text-center text-muted">Usá el buscador o elegí carrera y año para ver materias.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div>

                    @foreach (($usuariosProfesores ?? collect()) as $usuarioProfesor)
                        <div id="materias-profesor-{{ $usuarioProfesor->id }}" class="asistencia-modal" aria-hidden="true">
                            <div class="asistencia-modal-dialog">
                                <form action="{{ route('asistencia.admin.profesores.materias') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $usuarioProfesor->id }}">
                                    <div class="asistencia-modal-header">
                                        <div>
                                            <h3 class="mb-0">Materias de {{ $usuarioProfesor->name }}</h3>
                                            <p class="row-subtitle mb-0">{{ $usuarioProfesor->email }}</p>
                                        </div>
                                        <button class="asistencia-btn secondary" type="button" data-close-profesor-modal>Cerrar</button>
                                    </div>
                                    <div class="asistencia-modal-body">
                                        <div class="filter-grid" style="grid-template-columns: 1fr 1fr;">
                                            <div>
                                                <label class="form-label" for="modal_carrera_{{ $usuarioProfesor->id }}">Carrera</label>
                                                <select id="modal_carrera_{{ $usuarioProfesor->id }}" class="form-select js-modal-carrera">
                                                    @if ($adminPuedeCrearAdmins ?? false)
                                                        <option value="">Todas</option>
                                                    @endif
                                                    @foreach (($carrerasAdministrables ?? collect()) as $carrera)
                                                        <option value="{{ $carrera->id }}" {{ !($adminPuedeCrearAdmins ?? false) ? 'selected' : '' }}>{{ $carrera->descripcion }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label" for="modal_anio_{{ $usuarioProfesor->id }}">Año</label>
                                                <select id="modal_anio_{{ $usuarioProfesor->id }}" class="form-select js-modal-anio">
                                                    <option value="">Todos</option>
                                                    @foreach (($anios ?? collect()) as $anio)
                                                        <option value="{{ $anio->id }}">{{ $anio->descripcion ?? $anio->anio }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="edit-stack js-modal-materias" style="gap: 6px; max-height: none;">
                                            @forelse (($materias ?? collect())->whereIn('id', $materiasAdministrablesIds ?? collect()) as $materia)
                                                <label class="materia-check-row js-modal-materia-row"
                                                    data-carrera-id="{{ $materia->carrera_id }}"
                                                    data-anio-id="{{ $materia->anio_id }}">
                                                    <input class="form-check-input m-0" type="checkbox" name="materia_ids[]" value="{{ $materia->id }}" {{ ($usuarioProfesor->materias_asignadas_ids ?? collect())->contains($materia->id) ? 'checked' : '' }}>
                                                    <span class="text-truncate">
                                                        <strong>{{ $materia->descripcion }}</strong>
                                                        <span class="row-subtitle ms-2">
                                                            {{ $materia->deCarrera->descripcion ?? 'Sin carrera' }}
                                                            @if ($materia->deAnio)
                                                                - {{ $materia->deAnio->anio ?? $materia->deAnio->descripcion }}
                                                            @endif
                                                        </span>
                                                    </span>
                                                </label>
                                            @empty
                                                <p class="text-muted mb-0">Todavía no hay materias cargadas.</p>
                                            @endforelse
                                            <p class="text-muted mb-0 d-none js-modal-materias-empty">No hay materias con esos filtros.</p>
                                        </div>
                                    </div>
                                    <div class="asistencia-modal-footer">
                                        <button class="asistencia-btn secondary" type="button" data-close-profesor-modal>Cancelar</button>
                                        <button class="asistencia-btn" type="submit">Guardar materias</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <div id="admin-tab-carreras" class="admin-tab-panel d-none" style="display: none;">
                        <div class="editable-grid">
                            <div class="tool-panel">
                                <h3>Editar carreras</h3>
                                <div class="edit-stack">
                                    @forelse (($carreras ?? collect()) as $carrera)
                                        <form class="edit-item" action="{{ route('asistencia.admin.carreras.actualizar', $carrera) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="edit-form-grid">
                                                <div class="wide">
                                                    <label class="form-label" for="carrera_descripcion_{{ $carrera->id }}">Nombre</label>
                                                    <input id="carrera_descripcion_{{ $carrera->id }}" class="form-control" name="descripcion" value="{{ $carrera->descripcion }}" required>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="carrera_anios_{{ $carrera->id }}">Años</label>
                                                    <input id="carrera_anios_{{ $carrera->id }}" class="form-control" name="anios" type="number" min="1" value="{{ $carrera->anios }}">
                                                </div>
                                                <div>
                                                    <label class="form-label" for="carrera_resolucion_{{ $carrera->id }}">Resolución</label>
                                                    <input id="carrera_resolucion_{{ $carrera->id }}" class="form-control" name="resolucion" value="{{ $carrera->resolucion }}">
                                                </div>
                                                <div class="wide">
                                                    <label class="form-label" for="carrera_carpeta_{{ $carrera->id }}">Carpeta</label>
                                                    <input id="carrera_carpeta_{{ $carrera->id }}" class="form-control" name="nombre_carpeta" value="{{ $carrera->nombre_carpeta }}">
                                                </div>
                                                <div class="wide">
                                                    <label class="form-label" for="carrera_texto_{{ $carrera->id }}">Descripción</label>
                                                    <textarea id="carrera_texto_{{ $carrera->id }}" class="form-control" name="texto" rows="3">{{ $carrera->texto }}</textarea>
                                                </div>
                                            </div>
                                            <button class="asistencia-btn mt-3" type="submit">Guardar carrera</button>
                                        </form>
                                    @empty
                                        <p class="text-muted mb-0">Todavía no hay carreras cargadas.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="tool-panel">
                                <h3>Editar materias</h3>
                                <div class="filter-grid" style="grid-template-columns: 1fr;">
                                    <div>
                                        <label for="filtro_editar_materia" class="form-label">Buscar materia</label>
                                        <input id="filtro_editar_materia" class="form-control" type="text" placeholder="Buscar por materia, carrera, año o profesor">
                                    </div>
                                </div>
                                <div class="edit-stack">
                                    @forelse (($materias ?? collect()) as $materia)
                                        @php
                                            $textoEditarMateria = $materia->descripcion . ' ' .
                                                ($materia->deCarrera->descripcion ?? '') . ' ' .
                                                ($materia->deAnio->descripcion ?? '') . ' ' .
                                                ($materia->deAnio->anio ?? '') . ' ' .
                                                ($materia->horario && $materia->horario->profesor ? $materia->horario->profesor->apellido . ' ' . $materia->horario->profesor->nombre : '');
                                        @endphp
                                        <form class="edit-item js-editar-materia d-none" data-search="{{ $textoEditarMateria }}" action="{{ route('asistencia.admin.materias.actualizar', $materia) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="edit-form-grid">
                                                <div class="wide">
                                                    <label class="form-label" for="materia_descripcion_{{ $materia->id }}">Materia</label>
                                                    <input id="materia_descripcion_{{ $materia->id }}" class="form-control" name="descripcion" value="{{ $materia->descripcion }}" required>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="materia_carrera_{{ $materia->id }}">Carrera</label>
                                                    <select id="materia_carrera_{{ $materia->id }}" class="form-select" name="carrera_id">
                                                        <option value="">Sin carrera</option>
                                                        @foreach (($carreras ?? collect()) as $carrera)
                                                            <option value="{{ $carrera->id }}" {{ $materia->carrera_id == $carrera->id ? 'selected' : '' }}>{{ $carrera->descripcion }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="materia_anio_{{ $materia->id }}">Año</label>
                                                    <select id="materia_anio_{{ $materia->id }}" class="form-select" name="anio_id">
                                                        <option value="">Sin año</option>
                                                        @foreach (($anios ?? collect()) as $anio)
                                                            <option value="{{ $anio->id }}" {{ $materia->anio_id == $anio->id ? 'selected' : '' }}>{{ $anio->descripcion ?? $anio->anio }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="materia_profesor_edit_{{ $materia->id }}">Profesor</label>
                                                    <select id="materia_profesor_edit_{{ $materia->id }}" class="form-select" name="profesor_id">
                                                        <option value="">Sin profesor</option>
                                                        @foreach (($profesores ?? collect()) as $profesor)
                                                            <option value="{{ $profesor->id }}" {{ $materia->horario && $materia->horario->profesor_id == $profesor->id ? 'selected' : '' }}>{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="materia_orden_{{ $materia->id }}">Orden</label>
                                                    <input id="materia_orden_{{ $materia->id }}" class="form-control" name="orden" type="number" min="0" value="{{ $materia->orden }}">
                                                </div>
                                            </div>
                                            <button class="asistencia-btn mt-3" type="submit">Guardar materia</button>
                                        </form>
                                    @empty
                                        <p class="text-muted mb-0">Todavía no hay materias cargadas.</p>
                                    @endforelse
                                    <p id="editar_materias_vacio" class="text-muted mb-0">Busca una materia para editarla.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif
        @endguest
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleButtons = document.querySelectorAll('.role-button');
        const panels = document.querySelectorAll('.dashboard-panel');
        const searchInputs = document.querySelectorAll('.js-buscador');
        const adminTabButtons = document.querySelectorAll('.admin-nav-button');
        const adminTabPanels = document.querySelectorAll('.admin-tab-panel');
        const configuredSearch = document.getElementById('filtro_materia_configurada');
        const configuredCareer = document.getElementById('filtro_carrera_configurada');
        const configuredYear = document.getElementById('filtro_anio_configurado');
        const configuredClear = document.getElementById('limpiar_filtros_configurados');
        const configuredRows = document.querySelectorAll('.js-materia-configurada');
        const configuredEmpty = document.getElementById('materias_configuradas_vacio');
        const editMatterSearch = document.getElementById('filtro_editar_materia');
        const editMatterForms = document.querySelectorAll('.js-editar-materia');
        const editMatterEmpty = document.getElementById('editar_materias_vacio');
        const adminUserType = document.getElementById('admin_tipo_usuario');
        const adminUserForms = document.querySelectorAll('.js-admin-user-form');
        const userWithoutCareerSearch = document.getElementById('buscar_usuario_sin_carrera');
        const userWithoutCareerRows = document.querySelectorAll('.js-usuario-sin-carrera');
        const userWithoutCareerEmpty = document.getElementById('usuarios_sin_carrera_vacio');
        const validatedTeacherSearch = document.getElementById('buscar_profesor_validado');
        const validatedTeacherRows = document.querySelectorAll('.js-profesor-validado');
        const validatedTeacherEmpty = document.getElementById('profesores_validados_vacio');
        const principalTeacherSearch = document.getElementById('buscar_directora_profesor');
        const principalTeacherRows = document.querySelectorAll('.js-directora-profesor');
        const principalTeacherEmpty = document.getElementById('directora_profesores_vacio');
        const principalStudentSearch = document.getElementById('buscar_directora_alumno');
        const principalStudentRows = document.querySelectorAll('.js-directora-alumno');
        const principalStudentEmpty = document.getElementById('directora_alumnos_vacio');
        const autocompleteSources = {
            materias: [
                @foreach (($materias ?? collect()) as $materia)
                    {
                        id: '{{ $materia->id }}',
                        label: @json($materia->descripcion . ($materia->deCarrera ? ' - ' . $materia->deCarrera->descripcion : '') . ($materia->deAnio ? ' - ' . ($materia->deAnio->anio ?? $materia->deAnio->descripcion) : ''))
                    },
                @endforeach
            ],
            profesores: [
                @foreach (($profesores ?? collect()) as $profesor)
                    {
                        id: '{{ $profesor->id }}',
                        label: @json($profesor->apellido . ', ' . $profesor->nombre)
                    },
                @endforeach
            ],
            alumnos: [
                @foreach (($alumnos ?? collect()) as $alumno)
                    {
                        id: '{{ $alumno->id }}',
                        label: @json($alumno->apellido . ', ' . $alumno->nombre . ' - DNI ' . $alumno->dni)
                    },
                @endforeach
            ],
            usuarios: [
                @foreach (($usuarios ?? collect()) as $usuario)
                    {
                        id: '{{ $usuario->id }}',
                        label: @json($usuario->name . ' - ' . $usuario->email . ' - ' . ((int) ($usuario->is_admin ?? 0) === 2 ? 'Profesor' : ((int) ($usuario->is_admin ?? 0) === 1 ? 'Admin' : 'Alumno')))
                    },
                @endforeach
            ]
        };

        function normalizeText(value) {
            return value
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        }

        function closeAllResults(exceptInputId) {
            document.querySelectorAll('.autocomplete-results').forEach(function (resultsBox) {
                if (resultsBox.dataset.resultsFor !== exceptInputId) {
                    resultsBox.classList.remove('active');
                    resultsBox.innerHTML = '';
                }
            });
        }

        function syncAdminUserForms() {
            if (!adminUserType) {
                return;
            }

            const selectedType = adminUserType.value;
            adminUserForms.forEach(function (form) {
                const isActive = form.dataset.adminUserForm === selectedType;
                form.classList.toggle('is-hidden', !isActive);
                form.querySelectorAll('input, select').forEach(function (field) {
                    if (field.type !== 'hidden') {
                        field.disabled = !isActive;
                    }
                });
            });
        }

        if (adminUserType) {
            adminUserType.addEventListener('change', syncAdminUserForms);
            syncAdminUserForms();
        }

        function filterUsersWithoutCareer() {
            const query = normalizeText(userWithoutCareerSearch ? userWithoutCareerSearch.value : '');
            let visibleRows = 0;

            userWithoutCareerRows.forEach(function (row) {
                const visible = !query || normalizeText(row.dataset.search || '').includes(query);
                row.classList.toggle('d-none', !visible);
                if (visible) {
                    visibleRows++;
                }
            });

            if (userWithoutCareerEmpty) {
                userWithoutCareerEmpty.classList.toggle('d-none', visibleRows > 0);
            }
        }

        if (userWithoutCareerSearch) {
            userWithoutCareerSearch.addEventListener('input', filterUsersWithoutCareer);
            filterUsersWithoutCareer();
        }

        function filterValidatedTeachers() {
            const query = normalizeText(validatedTeacherSearch ? validatedTeacherSearch.value : '');
            let visibleRows = 0;

            validatedTeacherRows.forEach(function (row) {
                const visible = !query || normalizeText(row.dataset.search || '').includes(query);
                row.classList.toggle('d-none', !visible);
                if (visible) {
                    visibleRows++;
                }
            });

            if (validatedTeacherEmpty) {
                validatedTeacherEmpty.classList.toggle('d-none', visibleRows > 0);
            }
        }

        if (validatedTeacherSearch) {
            validatedTeacherSearch.addEventListener('input', filterValidatedTeachers);
            filterValidatedTeachers();
        }

        function filterPrincipalRows(searchInput, rows, emptyMessage) {
            const query = normalizeText(searchInput ? searchInput.value : '');
            let visibleRows = 0;

            rows.forEach(function (row) {
                const visible = !query || normalizeText(row.dataset.search || '').includes(query);
                row.classList.toggle('d-none', !visible);
                if (visible) {
                    visibleRows++;
                }
            });

            if (emptyMessage) {
                emptyMessage.classList.toggle('d-none', visibleRows > 0);
            }
        }

        if (principalTeacherSearch) {
            principalTeacherSearch.addEventListener('input', function () {
                filterPrincipalRows(principalTeacherSearch, principalTeacherRows, principalTeacherEmpty);
            });
            filterPrincipalRows(principalTeacherSearch, principalTeacherRows, principalTeacherEmpty);
        }

        if (principalStudentSearch) {
            principalStudentSearch.addEventListener('input', function () {
                filterPrincipalRows(principalStudentSearch, principalStudentRows, principalStudentEmpty);
            });
            filterPrincipalRows(principalStudentSearch, principalStudentRows, principalStudentEmpty);
        }

        function filterModalSubjects(modal) {
            const careerSelect = modal.querySelector('.js-modal-carrera');
            const yearSelect = modal.querySelector('.js-modal-anio');
            const rows = modal.querySelectorAll('.js-modal-materia-row');
            const emptyMessage = modal.querySelector('.js-modal-materias-empty');
            const careerId = careerSelect ? careerSelect.value : '';
            const yearId = yearSelect ? yearSelect.value : '';
            let visibleRows = 0;

            rows.forEach(function (row) {
                const matchesCareer = !careerId || row.dataset.carreraId === careerId;
                const matchesYear = !yearId || row.dataset.anioId === yearId;
                const visible = matchesCareer && matchesYear;
                row.classList.toggle('d-none', !visible);
                if (visible) {
                    visibleRows++;
                }
            });

            if (emptyMessage) {
                emptyMessage.classList.toggle('d-none', visibleRows > 0);
            }
        }

        document.querySelectorAll('[data-open-profesor-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                const modal = document.getElementById(button.dataset.openProfesorModal);
                if (!modal) {
                    return;
                }

                modal.classList.add('active');
                modal.setAttribute('aria-hidden', 'false');
                filterModalSubjects(modal);
            });
        });

        document.querySelectorAll('[data-close-profesor-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                const modal = button.closest('.asistencia-modal');
                if (!modal) {
                    return;
                }

                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
            });
        });

        document.querySelectorAll('.asistencia-modal').forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.classList.remove('active');
                    modal.setAttribute('aria-hidden', 'true');
                }
            });

            modal.querySelectorAll('.js-modal-carrera, .js-modal-anio').forEach(function (select) {
                select.addEventListener('change', function () {
                    filterModalSubjects(modal);
                });
            });
        });

        roleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const role = button.dataset.role;

                roleButtons.forEach(function (item) {
                    item.classList.remove('active');
                    item.setAttribute('aria-selected', 'false');
                });

                panels.forEach(function (panel) {
                    panel.classList.remove('active');
                });

                button.classList.add('active');
                button.setAttribute('aria-selected', 'true');
                document.getElementById('panel-' + role).classList.add('active');
            });
        });

        adminTabButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const tab = button.dataset.adminTab;

                adminTabButtons.forEach(function (item) {
                    item.classList.remove('active');
                });

                adminTabPanels.forEach(function (panel) {
                    panel.classList.remove('active');
                });

                button.classList.add('active');
                document.getElementById('admin-tab-' + tab).classList.add('active');
            });
        });

        function filterConfiguredSubjects() {
            if (!configuredRows.length) {
                return;
            }

            const query = normalizeText(configuredSearch ? configuredSearch.value : '');
            const careerId = configuredCareer ? configuredCareer.value : '';
            const yearId = configuredYear ? configuredYear.value : '';
            const hasFilter = query || careerId || yearId;
            let visibleRows = 0;

            configuredRows.forEach(function (row) {
                const matchesText = !query || normalizeText(row.dataset.search || '').includes(query);
                const matchesCareer = !careerId || row.dataset.carreraId === careerId;
                const matchesYear = !yearId || row.dataset.anioId === yearId;
                const visible = hasFilter && matchesText && matchesCareer && matchesYear;

                row.classList.toggle('d-none', !visible);
                if (visible) {
                    visibleRows++;
                }
            });

            if (configuredEmpty) {
                configuredEmpty.classList.toggle('d-none', visibleRows > 0);
                configuredEmpty.querySelector('td').textContent = hasFilter
                    ? 'No se encontraron materias con esos filtros.'
                    : 'Usá el buscador o elegí carrera y año para ver materias.';
            }
        }

        if (configuredSearch) {
            configuredSearch.addEventListener('input', filterConfiguredSubjects);
        }
        if (configuredCareer) {
            configuredCareer.addEventListener('change', filterConfiguredSubjects);
        }
        if (configuredYear) {
            configuredYear.addEventListener('change', filterConfiguredSubjects);
        }
        if (configuredClear) {
            configuredClear.addEventListener('click', function () {
                configuredSearch.value = '';
                configuredCareer.value = '';
                configuredYear.value = '';
                filterConfiguredSubjects();
            });
        }
        filterConfiguredSubjects();

        function filterEditableSubjects() {
            if (!editMatterForms.length) {
                return;
            }

            const query = normalizeText(editMatterSearch ? editMatterSearch.value : '');
            let visibleForms = 0;

            editMatterForms.forEach(function (form) {
                const visible = query && normalizeText(form.dataset.search || '').includes(query);
                form.classList.toggle('d-none', !visible);
                if (visible) {
                    visibleForms++;
                }
            });

            if (editMatterEmpty) {
                editMatterEmpty.classList.toggle('d-none', visibleForms > 0);
                editMatterEmpty.textContent = query
                    ? 'No se encontraron materias para editar.'
                    : 'Busca una materia para editarla.';
            }
        }

        if (editMatterSearch) {
            editMatterSearch.addEventListener('input', filterEditableSubjects);
        }
        filterEditableSubjects();

        searchInputs.forEach(function (input) {
            const hiddenInput = document.getElementById(input.dataset.hiddenTarget);
            const resultsBox = document.querySelector('[data-results-for="' + input.id + '"]');
            const source = autocompleteSources[input.dataset.source] || [];

            function syncHiddenValue() {
                const normalizedInput = normalizeText(input.value);
                const selectedItem = source.find(function (item) {
                    return normalizeText(item.label) === normalizedInput;
                });

                hiddenInput.value = selectedItem ? selectedItem.id : '';
            }

            function selectItem(item) {
                input.value = item.label;
                hiddenInput.value = item.id;
                input.setCustomValidity('');
                resultsBox.classList.remove('active');
                resultsBox.innerHTML = '';
            }

            function renderResults() {
                const query = normalizeText(input.value);
                hiddenInput.value = '';
                closeAllResults(input.id);

                if (!query) {
                    resultsBox.classList.remove('active');
                    resultsBox.innerHTML = '';
                    return;
                }

                const matches = source.filter(function (item) {
                    return normalizeText(item.label).includes(query);
                }).slice(0, 8);

                if (!matches.length) {
                    resultsBox.innerHTML = '<div class="autocomplete-empty">Sin resultados</div>';
                    resultsBox.classList.add('active');
                    return;
                }

                resultsBox.innerHTML = '';
                matches.forEach(function (item) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'autocomplete-option';
                    button.textContent = item.label;
                    button.addEventListener('mousedown', function (event) {
                        event.preventDefault();
                        selectItem(item);
                    });
                    resultsBox.appendChild(button);
                });
                resultsBox.classList.add('active');
                syncHiddenValue();
            }

            input.addEventListener('input', renderResults);
            input.addEventListener('focus', renderResults);
            input.addEventListener('change', syncHiddenValue);
            input.addEventListener('blur', function () {
                setTimeout(function () {
                    resultsBox.classList.remove('active');
                }, 120);
            });
        });

        document.querySelectorAll('#panel-admin form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                const formSearches = form.querySelectorAll('.js-buscador');
                let valid = true;

                formSearches.forEach(function (input) {
                    const hiddenInput = document.getElementById(input.dataset.hiddenTarget);
                    const needsSelection = input.hasAttribute('required') || input.value.trim() !== '';
                    const source = autocompleteSources[input.dataset.source] || [];
                    const exactMatch = source.find(function (item) {
                        return normalizeText(item.label) === normalizeText(input.value);
                    });

                    if (exactMatch) {
                        hiddenInput.value = exactMatch.id;
                    }

                    if (needsSelection && !hiddenInput.value) {
                        input.setCustomValidity('Seleccioná una opción de la lista.');
                        input.reportValidity();
                        valid = false;
                    } else {
                        input.setCustomValidity('');
                    }
                });

                if (!valid) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
@endsection
