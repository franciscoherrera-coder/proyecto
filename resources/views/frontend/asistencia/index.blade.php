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

    .dashboard-header-actions {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .dashboard-header-actions .asistencia-btn.secondary {
        background: #e4e7eb;
        border-color: #e4e7eb;
        color: #243b53;
    }

    .career-choice-list {
        display: grid;
        gap: 8px;
    }

    .career-choice {
        cursor: pointer;
        padding: 10px 12px;
    }

    .career-choice:has(input:checked) {
        background: #fce8e6;
        border-color: #700101;
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

    #panel-admin .metric-grid,
    #panel-admin #admin-tab-alumnos {
        display: none;
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

    .materia-alumno-compacta {
        align-items: center;
        border-bottom: 1px solid #edf2f7;
        color: #243b53;
        display: flex;
        gap: 8px;
        justify-content: space-between;
        min-height: 40px;
        padding: 6px 8px;
    }

    .materia-alumno-compacta:hover {
        background: #f8fafc;
    }

    .materia-alumno-compacta:last-child {
        border-bottom: 0;
    }

    .materia-alumno-compacta .row-subtitle {
        display: inline;
        font-size: 0.78rem;
        margin-left: 8px;
    }

    .materia-alumno-compacta .badge-soft {
        flex: 0 0 auto;
        font-size: 0.68rem;
        padding: 4px 8px;
    }

    .alertas-asistencia {
        height: auto !important;
        min-height: 0 !important;
        padding: 12px 14px;
    }

    .alertas-asistencia .alert-heading {
        font-size: 1rem;
        margin-bottom: 2px;
    }

    .tabla-alertas-asistencia {
        color: inherit;
        margin: 0;
        table-layout: auto;
        width: 100%;
    }

    .tabla-alertas-asistencia tr,
    .tabla-alertas-asistencia td {
        height: auto !important;
        min-height: 0 !important;
    }

    .tabla-alertas-asistencia td {
        border-top: 1px solid rgba(132, 32, 41, .2);
        font-size: .82rem;
        line-height: 1.25;
        padding: 5px 4px;
        vertical-align: middle;
    }

    .tabla-alertas-asistencia td:first-child {
        padding-left: 0;
    }

    .tabla-alertas-asistencia td:last-child {
        padding-right: 0;
        text-align: right;
        white-space: nowrap;
        width: 1%;
    }

    .tabla-alertas-asistencia .btn {
        padding: 3px 8px;
        white-space: nowrap;
    }

    .materia-alumno-acciones { align-items: center; display: flex; flex: 0 0 auto; gap: 6px; }
    .boton-escanear-qr { border-radius: 999px; font-size: .68rem; padding: 4px 9px; }
    #lector_qr_alumno { margin: 0 auto; max-width: 460px; width: 100%; }

    .row-title {
        color: #243b53;
        font-weight: 800;
    }

    .profesor-materia-link,
    .profesor-materia-link strong {
        color: #243b53;
    }

    .profesor-materia-link:hover,
    .profesor-materia-link:hover strong {
        color: #700101;
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

    .sustitucion-modal {
        align-items: center;
        background: rgba(17, 24, 39, 0.72);
        display: none;
        inset: 0;
        justify-content: center;
        padding: 18px;
        position: fixed;
        z-index: 1100;
    }

    .sustitucion-modal.active {
        display: flex;
    }

    .sustitucion-modal-dialog {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 28px 70px rgba(17, 24, 39, 0.35);
        max-width: 620px;
        overflow: hidden;
        width: 100%;
    }

    .sustitucion-modal-icon {
        align-items: center;
        background: #fff3cd;
        border-radius: 50%;
        color: #856404;
        display: inline-flex;
        flex: 0 0 42px;
        font-size: 1.35rem;
        font-weight: 800;
        height: 42px;
        justify-content: center;
    }

    .sustitucion-lista {
        display: grid;
        gap: 8px;
        list-style: none;
        margin: 16px 0 0;
        max-height: 280px;
        overflow-y: auto;
        padding: 0;
    }

    .sustitucion-lista li {
        background: #fff8e1;
        border: 1px solid #ffe08a;
        border-radius: 8px;
        color: #5f4700;
        padding: 10px 12px;
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

    .carreras-checkbox-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: 6px;
        min-height: 0;
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

        .dashboard-header-actions {
            align-items: stretch;
            flex-direction: column;
            width: 100%;
        }

        .tabla-alertas-asistencia td {
            font-size: .76rem;
        }

        .edit-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="asistencia-page">
    <div class="container">
        @guest
        <section class="asistencia-hero">
            <div class="asistencia-kicker">Acceso requerido</div>
            <h1 class="asistencia-title">Sistema de asistencia</h1>
            <p class="asistencia-copy">
                Iniciá sesión para entrar al panel de asistencia o registrate para pedir un acceso como alumno, profesor o admin.
            </p>
        </section>
        @else
            <div class="d-flex justify-content-end">
                <form action="{{ route('asistencia.logout') }}" method="POST" class="m-0">
                    @csrf
                    <button class="asistencia-btn secondary btn-cerrar-sesion" type="submit">Cerrar sesión</button>
                </form>
            </div>
        @endguest

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
                                <label class="form-label" for="login_identificador">DNI o correo electrónico</label>
                                <input id="login_identificador" class="form-control" name="identificador" value="{{ old('identificador') }}" autocomplete="username" inputmode="numeric" required>
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
                        <h2>Mis materias</h2>
                        <p>Estas son las materias en las que tu profesor te validó.</p>
                    </div>
                    <span class="status-pill">{{ ($materiasAlumno ?? collect())->count() }} materia(s)</span>
                </div>
                <div class="dashboard-body">
                    @if (($alertasAsistenciaAlumno ?? collect())->isNotEmpty())
                        <div class="alert alert-danger alertas-asistencia" role="alert">
                            <h3 class="alert-heading">Asistencia por debajo del mínimo</h3>
                            <table class="tabla-alertas-asistencia">
                                <tbody>
                                    @foreach ($alertasAsistenciaAlumno as $alertaAsistencia)
                                        <tr>
                                            <td>
                                                <strong>{{ $alertaAsistencia['materia']->descripcion }}</strong>
                                                · {{ number_format($alertaAsistencia['porcentaje'], 1, ',', '.') }}%
                                                · mínimo {{ $alertaAsistencia['porcentaje_minimo'] }}%
                                            </td>
                                            <td>
                                                <a class="btn btn-sm btn-outline-danger" href="{{ route('asistencia.alumno.materia', $alertaAsistencia['materia']) }}">Ver detalle</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if (!($tieneTablaAsignaciones ?? false))
                        <div class="alert alert-warning mb-0">Las asignaciones de materias todavía no están disponibles.</div>
                    @elseif (($materiasAlumno ?? collect())->isEmpty())
                        <div class="tool-panel"><p class="text-muted mb-0">Todavía no tenés materias validadas por un profesor.</p></div>
                    @else
                        <div class="edit-stack">
                            @foreach (($materiasAlumno ?? collect())->groupBy('carrera_id') as $materiasCarrera)
                                <div class="tool-panel">
                                    <h3>{{ $materiasCarrera->first()->deCarrera->descripcion ?? 'Sin carrera' }}</h3>
                                    @foreach ($materiasCarrera as $materiaAlumno)
                                        <div class="materia-alumno-compacta">
                                            <span class="text-truncate">
                                                <span class="row-title">{{ $materiaAlumno->descripcion }}</span>
                                                <span class="row-subtitle">{{ $materiaAlumno->deAnio->anio ?? $materiaAlumno->deAnio->descripcion ?? 'Sin año' }} · {{ $materiaAlumno->horario && $materiaAlumno->horario->profesor ? $materiaAlumno->horario->profesor->apellido . ', ' . $materiaAlumno->horario->profesor->nombre : 'Profesor no informado' }}</span>
                                            </span>
                                            <span class="materia-alumno-acciones">
                                                <button class="btn btn-outline-primary boton-escanear-qr js-escanear-qr" type="button" data-materia="{{ $materiaAlumno->descripcion }}">Escanear QR</button>
                                                <a class="badge-soft text-decoration-none" href="{{ route('asistencia.alumno.materia', $materiaAlumno) }}">Ver materia</a>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @if (false)
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
        @endif

        @if ($rolActivo === 'profesor')
        <section id="panel-profesor" class="dashboard-panel active" role="tabpanel">
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
                                @endphp
                                <details class="tool-panel">
                                    <summary class="d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <h3 class="mb-1">{{ $carreraPanel->descripcion ?? 'Sin carrera' }}</h3>
                                            <p class="row-subtitle mb-0">{{ $materiasCarrera->count() }} materia(s) asignada(s)</p>
                                        </div>
                                    </summary>

                                    <div class="edit-stack mt-3" style="gap: 8px; max-height: none;">
                                        @foreach ($materiasCarrera as $materiaProfesor)
                                            <details class="edit-item">
                                                <summary class="d-flex align-items-center justify-content-between gap-2">
                                                    <span>
                                                        <a class="profesor-materia-link text-decoration-none" href="{{ route('asistencia.profesor.materia', $materiaProfesor) }}"><strong>{{ $materiaProfesor->descripcion }}</strong></a>
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
                                                    <div>
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
            <div class="dashboard-shell">
                <div class="dashboard-header">
                    <div>
                        <h2>Vista Admin</h2>
                        <p>Gestión de materias, profesores y alumnos para el sistema de asistencia.</p>
                    </div>
                    <div class="dashboard-header-actions">
                        @if ($carreraActiva ?? null)
                            <span class="status-pill">{{ $carreraActiva->descripcion }}</span>
                            @if (($carrerasPreceptor ?? collect())->count() > 1)
                                <button class="asistencia-btn secondary" type="button" data-open-career-selector>Cambiar carrera</button>
                            @endif
                        @else
                            <span class="status-pill">Administración real</span>
                        @endif
                    </div>
                </div>

                <div class="dashboard-body">
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

                    <div id="admin-tab-alumnos" class="tool-panel mb-4">
                        <h3>ABM de alumnos por carrera</h3>
                        <p class="row-subtitle">Cada alumno se registra con una carrera y su acceso queda vinculado mediante el DNI.</p>

                        <details class="mb-3">
                            <summary class="asistencia-btn d-inline-block">Crear alumno</summary>
                            <form class="edit-item mt-3" action="{{ route('asistencia.admin.alumnos.crear') }}" method="POST">
                                @csrf
                                <div class="edit-form-grid">
                                    <div>
                                        <label class="form-label" for="alumno_nuevo_nombre">Nombre</label>
                                        <input id="alumno_nuevo_nombre" class="form-control" name="nombre" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="alumno_nuevo_apellido">Apellido</label>
                                        <input id="alumno_nuevo_apellido" class="form-control" name="apellido" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="alumno_nuevo_dni">DNI</label>
                                        <input id="alumno_nuevo_dni" class="form-control" type="number" name="dni" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="alumno_nuevo_cuil">CUIL</label>
                                        <input id="alumno_nuevo_cuil" class="form-control" type="number" name="cuil" required>
                                    </div>
                                    <div class="wide">
                                        <label class="form-label" for="alumno_nuevo_email">Correo electrónico</label>
                                        <input id="alumno_nuevo_email" class="form-control" type="email" name="email" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="alumno_nuevo_carrera">Carrera</label>
                                        <select id="alumno_nuevo_carrera" class="form-select" name="carrera_id" required>
                                            <option value="">Seleccionar carrera</option>
                                            @foreach (($carrerasAdministrables ?? collect()) as $carrera)
                                                <option value="{{ $carrera->id }}">{{ $carrera->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="alumno_nuevo_password">Contraseña de acceso</label>
                                        <input id="alumno_nuevo_password" class="form-control" type="password" name="password" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="alumno_nuevo_password_confirmation">Confirmar contraseña</label>
                                        <input id="alumno_nuevo_password_confirmation" class="form-control" type="password" name="password_confirmation" required>
                                    </div>
                                </div>
                                <button class="asistencia-btn mt-3" type="submit">Guardar alumno</button>
                            </form>
                        </details>

                        @forelse (($alumnosPorCarrera ?? collect())->groupBy('carrera_id') as $alumnosCarrera)
                            <details class="edit-item mb-2">
                                <summary>
                                    <strong>{{ $alumnosCarrera->first()->carrera->descripcion ?? 'Sin carrera' }}</strong>
                                    <span class="row-subtitle ms-2">{{ $alumnosCarrera->count() }} alumno(s)</span>
                                </summary>
                                <div class="edit-stack mt-3">
                                    @foreach ($alumnosCarrera as $alumno)
                                        <details class="border rounded p-2">
                                            <summary>
                                                <strong>{{ $alumno->apellido }}, {{ $alumno->nombre }}</strong>
                                                <span class="row-subtitle ms-2">DNI {{ $alumno->dni }}</span>
                                            </summary>
                                            <form class="mt-3" action="{{ route('asistencia.admin.alumnos.actualizar', $alumno) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="edit-form-grid">
                                                    <div><label class="form-label">Nombre<input class="form-control" name="nombre" value="{{ $alumno->nombre }}" required></label></div>
                                                    <div><label class="form-label">Apellido<input class="form-control" name="apellido" value="{{ $alumno->apellido }}" required></label></div>
                                                    <div><label class="form-label">DNI<input class="form-control" type="number" name="dni" value="{{ $alumno->dni }}" required></label></div>
                                                    <div><label class="form-label">CUIL<input class="form-control" type="number" name="cuil" value="{{ $alumno->cuil }}" required></label></div>
                                                    <div class="wide"><label class="form-label">Correo electrónico<input class="form-control" type="email" name="email" value="{{ $alumno->email }}" required></label></div>
                                                    <div><label class="form-label">Carrera<select class="form-select" name="carrera_id" required>@foreach (($carrerasAdministrables ?? collect()) as $carrera)<option value="{{ $carrera->id }}" {{ $alumno->carrera_id == $carrera->id ? 'selected' : '' }}>{{ $carrera->descripcion }}</option>@endforeach</select></label></div>
                                                    <div><label class="form-label">Nueva contraseña <small class="text-muted">(opcional)</small><input class="form-control" type="password" name="password"></label></div>
                                                    <div><label class="form-label">Confirmar contraseña<input class="form-control" type="password" name="password_confirmation"></label></div>
                                                </div>
                                                <button class="asistencia-btn mt-3" type="submit">Actualizar alumno</button>
                                            </form>
                                            <form class="mt-2" action="{{ route('asistencia.admin.alumnos.eliminar', $alumno) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm" type="submit">Eliminar alumno y acceso</button>
                                            </form>
                                        </details>
                                    @endforeach
                                </div>
                            </details>
                        @empty
                            <p class="text-muted mb-0">Todavía no hay alumnos registrados en las carreras administrables.</p>
                        @endforelse
                    </div>

                    @if ($adminPuedeCrearAdmins ?? false)
                    <div class="tool-panel mb-4">
                        <details>
                            <summary class="asistencia-btn d-inline-block">Crear nuevo preceptor</summary>
                            <form class="edit-item mt-3 js-form-carreras-preceptor" style="max-width: 560px;" action="{{ route('asistencia.admin.usuarios.crear') }}" method="POST">
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
                                    <div class="form-label">Carreras administradas</div>
                                    <div class="carreras-checkbox-list">
                                        @foreach (($carreras ?? collect()) as $carrera)
                                            @php
                                                $preceptorActualCarrera = ($preceptoresPorCarrera ?? collect())->get($carrera->id);
                                            @endphp
                                            <label class="materia-check-row">
                                                <input class="form-check-input m-0" type="checkbox" name="carrera_ids[]" value="{{ $carrera->id }}"
                                                    data-carrera-nombre="{{ $carrera->descripcion }}"
                                                    data-preceptor-actual="{{ $preceptorActualCarrera->name ?? '' }}"
                                                    data-asignada-inicialmente="0"
                                                    {{ in_array($carrera->id, old('carrera_ids', [])) ? 'checked' : '' }}>
                                                <span>{{ $carrera->descripcion }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="form-text">Seleccioná una o más carreras para el preceptor.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="admin_usuario_password">Contraseña</label>
                                    <input id="admin_usuario_password" class="form-control" type="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="admin_usuario_password_confirmation">Confirmar contraseña</label>
                                    <input id="admin_usuario_password_confirmation" class="form-control" type="password" name="password_confirmation" required>
                                </div>
                                <button class="asistencia-btn" type="submit">Crear preceptor</button>
                            </form>
                        </details>
                    </div>
                    @endif

                    @if ($adminPuedeCrearAdmins ?? false)
                    <div class="tool-panel mb-4">
                        <h3>Carreras de preceptores</h3>
                        <p class="row-subtitle">Podés asignar más de una carrera a cada preceptor.</p>
                        <div class="edit-stack js-preceptores-accordion">
                            @forelse (($usuariosPreceptores ?? collect()) as $preceptor)
                                <details class="edit-item js-preceptor-details">
                                    <summary>
                                        <strong>{{ $preceptor->name }}</strong>
                                        <span class="row-subtitle ms-2">{{ $preceptor->email }}</span>
                                    </summary>
                                    <form class="mt-3 js-form-carreras-preceptor" action="{{ route('asistencia.admin.preceptores.carreras', $preceptor) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="carreras-checkbox-list">
                                            @foreach (($carreras ?? collect()) as $carrera)
                                                @php
                                                    $preceptorActualCarrera = ($preceptoresPorCarrera ?? collect())->get($carrera->id);
                                                    $carreraAsignadaAlPreceptor = ($preceptor->carreras_administradas_ids ?? collect())->contains($carrera->id);
                                                @endphp
                                                <label class="materia-check-row">
                                                    <input class="form-check-input m-0" type="checkbox" name="carrera_ids[]" value="{{ $carrera->id }}"
                                                        data-carrera-nombre="{{ $carrera->descripcion }}"
                                                        data-preceptor-actual="{{ $preceptorActualCarrera->name ?? '' }}"
                                                        data-asignada-inicialmente="{{ $carreraAsignadaAlPreceptor ? '1' : '0' }}"
                                                        {{ $carreraAsignadaAlPreceptor ? 'checked' : '' }}>
                                                    <span>{{ $carrera->descripcion }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <button class="asistencia-btn mt-3" type="submit">Guardar carreras</button>
                                    </form>
                                </details>
                            @empty
                                <p class="text-muted mb-0">Todavía no hay preceptores creados.</p>
                            @endforelse
                        </div>
                    </div>
                    @endif

                    <div id="modal-confirmar-sustituciones-preceptor" class="sustitucion-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="titulo-confirmar-sustituciones-preceptor">
                        <div class="sustitucion-modal-dialog">
                            <div class="asistencia-modal-header">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="sustitucion-modal-icon" aria-hidden="true">!</span>
                                    <div>
                                        <h3 id="titulo-confirmar-sustituciones-preceptor" class="mb-1">Confirmar cambio de preceptor</h3>
                                        <p class="row-subtitle mb-0">Las siguientes carreras ya tienen un preceptor asignado.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="asistencia-modal-body">
                                <p class="mb-0">Si continuás, el preceptor seleccionado reemplazará al actual:</p>
                                <ul id="lista-confirmar-sustituciones-preceptor" class="sustitucion-lista"></ul>
                            </div>
                            <div class="asistencia-modal-footer justify-content-end">
                                <button class="asistencia-btn secondary" type="button" data-cancelar-sustituciones-preceptor>Cancelar</button>
                                <button class="asistencia-btn" type="button" data-confirmar-sustituciones-preceptor>Sustituir y guardar</button>
                            </div>
                        </div>
                    </div>

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
                                <input type="hidden" name="carrera_id" value="{{ $carreraActiva->id }}">
                                <div class="filter-grid" style="grid-template-columns: 1fr;">
                                    <div>
                                        <label class="form-label" for="buscar_usuario_sin_carrera">Buscar profesor pendiente en esta carrera</label>
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
                                        <p class="text-muted mb-0">No hay profesores pendientes de validación en esta carrera.</p>
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
                            @if (($carrerasAdministrables ?? collect())->count() > 1)
                                <div>
                                    <label for="filtro_carrera_configurada" class="form-label">Carrera</label>
                                    <select id="filtro_carrera_configurada" class="form-select">
                                        <option value="">Todas</option>
                                        @foreach (($carrerasAdministrables ?? collect()) as $carrera)
                                            <option value="{{ $carrera->id }}">{{ $carrera->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
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
                                        <th>Listado de alumnos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse (($materias ?? collect())->whereIn('id', $materiasAdministrablesIds ?? collect()) as $materia)
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
                                                <a class="btn btn-link p-0 fw-bold text-start" href="{{ route('asistencia.admin.materia', $materia) }}" target="_blank" rel="noopener">{{ $materia->descripcion }}</a>
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
                                            <td class="d-none">
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
                                            <td colspan="3" class="text-center text-muted">Todavía no hay materias cargadas.</td>
                                        </tr>
                                    @endforelse
                                    <tr id="materias_configuradas_vacio">
                                            <td colspan="3" class="text-center text-muted">Usá el buscador o elegí carrera y año para ver materias.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div>

                    @foreach (($usuariosProfesores ?? collect()) as $usuarioProfesor)
                        <div id="materias-profesor-{{ $usuarioProfesor->id }}" class="asistencia-modal" aria-hidden="true">
                            <div class="asistencia-modal-dialog">
                                <form class="js-form-materias-profesor" action="{{ route('asistencia.admin.profesores.materias') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $usuarioProfesor->id }}">
                                    @if ($carreraActiva ?? null)
                                        <input type="hidden" name="carrera_contexto_id" value="{{ $carreraActiva->id }}">
                                    @endif
                                    <div class="asistencia-modal-header">
                                        <div>
                                            <h3 class="mb-0">Materias de {{ $usuarioProfesor->name }}</h3>
                                            <p class="row-subtitle mb-0">{{ $usuarioProfesor->email }}</p>
                                        </div>
                                        <button class="asistencia-btn secondary" type="button" data-close-profesor-modal>Cerrar</button>
                                    </div>
                                    <div class="asistencia-modal-body">
                                        <div class="filter-grid" style="grid-template-columns: 1fr 1fr;">
                                            @if (($carrerasAdministrables ?? collect())->count() > 1)
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
                                            @endif
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
                                                @php
                                                    $materiaAsignadaAlProfesor = ($usuarioProfesor->materias_asignadas_ids ?? collect())->contains($materia->id);
                                                    $profesorActual = optional($materia->horario)->profesor;
                                                    $nombreProfesorActual = $profesorActual
                                                        ? trim($profesorActual->apellido . ', ' . $profesorActual->nombre)
                                                        : '';
                                                @endphp
                                                <label class="materia-check-row js-modal-materia-row"
                                                    data-carrera-id="{{ $materia->carrera_id }}"
                                                    data-anio-id="{{ $materia->anio_id }}">
                                                    <input class="form-check-input m-0" type="checkbox" name="materia_ids[]" value="{{ $materia->id }}"
                                                        data-materia-nombre="{{ $materia->descripcion }}"
                                                        data-profesor-actual="{{ $nombreProfesorActual }}"
                                                        data-asignada-inicialmente="{{ $materiaAsignadaAlProfesor ? '1' : '0' }}"
                                                        {{ $materiaAsignadaAlProfesor ? 'checked' : '' }}>
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

                    <div id="modal-confirmar-sustituciones" class="sustitucion-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="titulo-confirmar-sustituciones">
                        <div class="sustitucion-modal-dialog">
                            <div class="asistencia-modal-header">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="sustitucion-modal-icon" aria-hidden="true">!</span>
                                    <div>
                                        <h3 id="titulo-confirmar-sustituciones" class="mb-1">Confirmar sustitución</h3>
                                        <p class="row-subtitle mb-0">Las siguientes materias ya tienen un profesor asignado.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="asistencia-modal-body">
                                <p class="mb-0">Si continuás, el profesor seleccionado reemplazará al actual:</p>
                                <ul id="lista-confirmar-sustituciones" class="sustitucion-lista"></ul>
                            </div>
                            <div class="asistencia-modal-footer justify-content-end">
                                <button class="asistencia-btn secondary" type="button" data-cancelar-sustituciones>Cancelar</button>
                                <button class="asistencia-btn" type="button" data-confirmar-sustituciones>Sustituir y guardar</button>
                            </div>
                        </div>
                    </div>

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

            @if (($carrerasPreceptor ?? collect())->count() > 1)
                <div id="modal-seleccionar-carrera" class="asistencia-modal{{ ($mostrarSelectorCarrera ?? false) ? ' active' : '' }}" role="dialog" aria-modal="true" aria-hidden="{{ ($mostrarSelectorCarrera ?? false) ? 'false' : 'true' }}" aria-labelledby="titulo-seleccionar-carrera">
                    <div class="asistencia-modal-dialog" style="max-width: 560px;">
                        <form action="{{ route('asistencia.index') }}" method="GET">
                            <input type="hidden" name="admin_tab" value="{{ $adminTabActivo }}">
                            <div class="asistencia-modal-header">
                                <div>
                                    <h3 id="titulo-seleccionar-carrera" class="mb-1">Cambiar carrera</h3>
                                    <p class="row-subtitle mb-0">Elegí la carrera que querés administrar.</p>
                                </div>
                                <button class="asistencia-btn secondary" type="button" data-close-career-selector>Cerrar</button>
                            </div>
                            <div class="asistencia-modal-body">
                                <div class="career-choice-list">
                                    @foreach ($carrerasPreceptor as $carreraPreceptor)
                                        <label class="materia-check-row career-choice">
                                            <input class="form-check-input m-0" type="radio" name="carrera_id" value="{{ $carreraPreceptor->id }}" {{ ($carreraActiva && $carreraActiva->id === $carreraPreceptor->id) ? 'checked' : '' }} required>
                                            <span>{{ $carreraPreceptor->descripcion }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="asistencia-modal-footer">
                                <button class="asistencia-btn secondary" type="button" data-close-career-selector>Cancelar</button>
                                <button class="asistencia-btn" type="submit">Usar esta carrera</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </section>
        @endif
        @endguest
    </div>

    @auth
        @if (($rolUsuario ?? null) === 'alumno')
            <div class="modal fade" id="modalEscanearQr" tabindex="-1" aria-labelledby="tituloModalEscanearQr" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title h5" id="tituloModalEscanearQr">Escanear código QR</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <p id="materiaQrAlumno" class="text-muted text-center"></p>
                            <div id="lector_qr_alumno"></div>
                            <div id="mensaje_qr_alumno" class="alert alert-danger d-none mt-3 mb-0"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth
</main>

@auth
    @if (($rolUsuario ?? null) === 'alumno')
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    @endif
@endauth
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalQrElement = document.getElementById('modalEscanearQr');
        if (modalQrElement && typeof Html5Qrcode !== 'undefined') {
            const modalQr = new bootstrap.Modal(modalQrElement);
            const materiaQr = document.getElementById('materiaQrAlumno');
            const mensajeQr = document.getElementById('mensaje_qr_alumno');
            let lectorQr = null;
            let lecturaCompletada = false;

            document.querySelectorAll('.js-escanear-qr').forEach(function (button) {
                button.addEventListener('click', function () {
                    materiaQr.textContent = button.dataset.materia || '';
                    mensajeQr.classList.add('d-none');
                    mensajeQr.textContent = '';
                    lecturaCompletada = false;
                    modalQr.show();
                });
            });

            modalQrElement.addEventListener('shown.bs.modal', function () {
                lectorQr = new Html5Qrcode('lector_qr_alumno');
                lectorQr.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 230, height: 230 } },
                    function (textoQr) {
                        if (lecturaCompletada) return;

                        try {
                            const destino = new URL(textoQr);
                            if (!destino.pathname.includes('/asistencia/qr/')) {
                                throw new Error('Código incorrecto');
                            }

                            lecturaCompletada = true;
                            const urlLocal = window.location.origin + destino.pathname + destino.search;
                            lectorQr.stop().finally(function () {
                                window.location.href = urlLocal;
                            });
                        } catch (error) {
                            mensajeQr.textContent = 'Este código QR no pertenece al sistema de asistencia.';
                            mensajeQr.classList.remove('d-none');
                        }
                    },
                    function () {}
                ).catch(function () {
                    mensajeQr.textContent = 'No se pudo abrir la cámara. Revisá los permisos del navegador.';
                    mensajeQr.classList.remove('d-none');
                });
            });

            modalQrElement.addEventListener('hidden.bs.modal', function () {
                if (lectorQr && lectorQr.isScanning) {
                    lectorQr.stop().then(function () { lectorQr.clear(); }).catch(function () {});
                } else if (lectorQr) {
                    lectorQr.clear();
                }
                lectorQr = null;
            });
        }

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
        const preceptorAccordions = document.querySelectorAll('.js-preceptores-accordion');
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
        const careerSelectorModal = document.getElementById('modal-seleccionar-carrera');
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

        function setCareerSelectorOpen(open) {
            if (!careerSelectorModal) {
                return;
            }

            careerSelectorModal.classList.toggle('active', open);
            careerSelectorModal.setAttribute('aria-hidden', open ? 'false' : 'true');

            if (open) {
                const selectedCareer = careerSelectorModal.querySelector('input[name="carrera_id"]:checked');
                if (selectedCareer) {
                    selectedCareer.focus();
                }
            }
        }

        document.querySelectorAll('[data-open-career-selector]').forEach(function (button) {
            button.addEventListener('click', function () {
                setCareerSelectorOpen(true);
            });
        });

        document.querySelectorAll('[data-close-career-selector]').forEach(function (button) {
            button.addEventListener('click', function () {
                setCareerSelectorOpen(false);
            });
        });

        if (careerSelectorModal) {
            careerSelectorModal.addEventListener('click', function (event) {
                if (event.target === careerSelectorModal) {
                    setCareerSelectorOpen(false);
                }
            });

            if (careerSelectorModal.classList.contains('active')) {
                setCareerSelectorOpen(true);
            }
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && careerSelectorModal && careerSelectorModal.classList.contains('active')) {
                setCareerSelectorOpen(false);
            }
        });

        preceptorAccordions.forEach(function (accordion) {
            const preceptorDetails = accordion.querySelectorAll('.js-preceptor-details');

            preceptorDetails.forEach(function (details) {
                details.addEventListener('toggle', function () {
                    if (!details.open) {
                        return;
                    }

                    preceptorDetails.forEach(function (otherDetails) {
                        if (otherDetails !== details) {
                            otherDetails.open = false;
                        }
                    });
                });
            });
        });

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

        const careerReplacementModal = document.getElementById('modal-confirmar-sustituciones-preceptor');
        const careerReplacementList = document.getElementById('lista-confirmar-sustituciones-preceptor');
        let pendingCareerForm = null;

        function closeCareerReplacementModal() {
            if (!careerReplacementModal) {
                return;
            }

            careerReplacementModal.classList.remove('active');
            careerReplacementModal.setAttribute('aria-hidden', 'true');
            pendingCareerForm = null;
        }

        function openCareerReplacementModal(replacements, form) {
            if (!careerReplacementModal || !careerReplacementList) {
                return;
            }

            pendingCareerForm = form;
            careerReplacementList.innerHTML = '';

            replacements.forEach(function (replacement) {
                const item = document.createElement('li');
                item.textContent = 'Sustituir a ' + replacement.preceptor + ' como preceptor de la carrera ' + replacement.career + '.';
                careerReplacementList.appendChild(item);
            });

            careerReplacementModal.classList.add('active');
            careerReplacementModal.setAttribute('aria-hidden', 'false');
            careerReplacementModal.querySelector('[data-confirmar-sustituciones-preceptor]').focus();
        }

        document.querySelectorAll('.js-form-carreras-preceptor').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.sustitucionesPreceptorConfirmadas === '1') {
                    delete form.dataset.sustitucionesPreceptorConfirmadas;
                    return;
                }

                const replacements = [];
                form.querySelectorAll('input[name="carrera_ids[]"]:checked').forEach(function (checkbox) {
                    const wasInitiallyAssigned = checkbox.dataset.asignadaInicialmente === '1';
                    const currentPreceptor = (checkbox.dataset.preceptorActual || '').trim();

                    if (!wasInitiallyAssigned && currentPreceptor) {
                        replacements.push({
                            preceptor: currentPreceptor,
                            career: checkbox.dataset.carreraNombre
                        });
                    }
                });

                if (replacements.length > 0) {
                    event.preventDefault();
                    openCareerReplacementModal(replacements, form);
                }
            });
        });

        if (careerReplacementModal) {
            careerReplacementModal.querySelector('[data-cancelar-sustituciones-preceptor]').addEventListener('click', closeCareerReplacementModal);
            careerReplacementModal.querySelector('[data-confirmar-sustituciones-preceptor]').addEventListener('click', function () {
                if (!pendingCareerForm) {
                    return;
                }

                const form = pendingCareerForm;
                form.dataset.sustitucionesPreceptorConfirmadas = '1';
                closeCareerReplacementModal();
                form.requestSubmit();
            });
            careerReplacementModal.addEventListener('click', function (event) {
                if (event.target === careerReplacementModal) {
                    closeCareerReplacementModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && careerReplacementModal && careerReplacementModal.classList.contains('active')) {
                closeCareerReplacementModal();
            }
        });

        const replacementModal = document.getElementById('modal-confirmar-sustituciones');
        const replacementList = document.getElementById('lista-confirmar-sustituciones');
        let pendingSubjectForm = null;

        function closeReplacementModal() {
            if (!replacementModal) {
                return;
            }

            replacementModal.classList.remove('active');
            replacementModal.setAttribute('aria-hidden', 'true');
            pendingSubjectForm = null;
        }

        function openReplacementModal(replacements, form) {
            if (!replacementModal || !replacementList) {
                return;
            }

            pendingSubjectForm = form;
            replacementList.innerHTML = '';

            replacements.forEach(function (replacement) {
                const item = document.createElement('li');
                item.textContent = 'Sustituir a ' + replacement.teacher + ' en la materia ' + replacement.subject + '.';
                replacementList.appendChild(item);
            });

            replacementModal.classList.add('active');
            replacementModal.setAttribute('aria-hidden', 'false');
            replacementModal.querySelector('[data-confirmar-sustituciones]').focus();
        }

        if (replacementModal) {
            replacementModal.querySelector('[data-cancelar-sustituciones]').addEventListener('click', closeReplacementModal);
            replacementModal.querySelector('[data-confirmar-sustituciones]').addEventListener('click', function () {
                if (!pendingSubjectForm) {
                    return;
                }

                const form = pendingSubjectForm;
                form.dataset.sustitucionesConfirmadas = '1';
                closeReplacementModal();
                form.requestSubmit();
            });
            replacementModal.addEventListener('click', function (event) {
                if (event.target === replacementModal) {
                    closeReplacementModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && replacementModal && replacementModal.classList.contains('active')) {
                closeReplacementModal();
            }
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

            const subjectForm = modal.querySelector('.js-form-materias-profesor');
            if (subjectForm) {
                subjectForm.addEventListener('submit', function (event) {
                    if (subjectForm.dataset.sustitucionesConfirmadas === '1') {
                        delete subjectForm.dataset.sustitucionesConfirmadas;
                        return;
                    }

                    const replacements = [];

                    subjectForm.querySelectorAll('input[name="materia_ids[]"]:checked').forEach(function (checkbox) {
                        const wasInitiallyAssigned = checkbox.dataset.asignadaInicialmente === '1';
                        const currentTeacher = (checkbox.dataset.profesorActual || '').trim();

                        if (!wasInitiallyAssigned && currentTeacher) {
                            replacements.push({
                                teacher: currentTeacher,
                                subject: checkbox.dataset.materiaNombre
                            });
                        }
                    });

                    if (replacements.length > 0) {
                        event.preventDefault();
                        openReplacementModal(replacements, subjectForm);
                    }
                });
            }
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
