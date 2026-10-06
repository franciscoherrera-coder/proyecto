<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$outputDirectory = dirname(__DIR__) . '/public/documentos';
if (!is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0775, true);
}

$outputPath = $outputDirectory . '/Matriz_de_Riesgos_Proyecto_Asistencia.pdf';

$html = <<<'HTML'
<style>
    @page { size: A4 portrait; margin: 50pt 42pt 52pt; }
    body { font-family: dejavusans, sans-serif; color: #243143; font-size: 9.4pt; line-height: 1.42; }
    .header { position: fixed; top: -32pt; left: 0; right: 0; color: #587086; border-bottom: 0.5pt solid #c8d4df; font-size: 7.5pt; letter-spacing: 0.7pt; padding-bottom: 3pt; }
    .footer { position: fixed; bottom: -34pt; left: 0; right: 0; color: #718096; border-top: 0.5pt solid #d7e0e8; font-size: 7.5pt; padding-top: 4pt; }
    .page-number:after { content: counter(page); }
    h1 { color: #163a5f; font-size: 24pt; line-height: 1.12; margin: 0 0 8pt; }
    h2 { color: #163a5f; font-size: 15pt; border-bottom: 1.2pt solid #2b6f91; padding-bottom: 4pt; margin: 17pt 0 8pt; }
    h3 { color: #245977; font-size: 11.5pt; margin: 12pt 0 5pt; }
    p { margin: 0 0 7pt; text-align: justify; }
    .cover { padding-top: 74pt; }
    .eyebrow { color: #2b6f91; font-weight: bold; letter-spacing: 1.2pt; font-size: 9pt; margin-bottom: 11pt; }
    .subtitle { color: #4f6475; font-size: 13pt; line-height: 1.45; margin: 0 0 25pt; }
    .meta { margin-top: 32pt; padding: 13pt 15pt; background: #eff5f8; border-left: 4pt solid #2b6f91; }
    .meta p { margin: 2pt 0; text-align: left; }
    .note { background: #f3f7fa; border-left: 3pt solid #5b91ad; padding: 8pt 10pt; margin: 9pt 0; }
    .small { font-size: 8.3pt; color: #536778; }
    table { width: 100%; border-collapse: collapse; margin: 7pt 0 12pt; }
    th { background: #244f6c; color: #ffffff; font-size: 8.2pt; padding: 6pt 5pt; border: 0.6pt solid #244f6c; text-align: left; }
    td { padding: 5pt; border: 0.6pt solid #c8d4df; vertical-align: top; }
    tr:nth-child(even) td { background: #f6f9fb; }
    .risk-table { font-size: 7.5pt; line-height: 1.3; }
    .risk-table th { font-size: 7.2pt; }
    .center { text-align: center; }
    .critical { background: #f6c6c6 !important; color: #842029; font-weight: bold; text-align: center; }
    .moderate { background: #ffe8ad !important; color: #765600; font-weight: bold; text-align: center; }
    .minor { background: #d8eddd !important; color: #235b32; font-weight: bold; text-align: center; }
    .matrix td, .matrix th { text-align: center; vertical-align: middle; padding: 10pt 6pt; }
    .matrix .axis { background: #244f6c; color: #fff; font-weight: bold; }
    .risk-id { color: #163a5f; font-weight: bold; white-space: nowrap; }
    .response { margin: 8pt 0 12pt; border: 0.7pt solid #c7d5df; }
    .response-title { background: #e9f1f5; color: #163a5f; font-weight: bold; padding: 7pt 9pt; }
    .response-body { padding: 8pt 10pt; }
    ul { margin: 3pt 0 3pt 15pt; padding: 0; }
    li { margin-bottom: 4pt; }
    .page-break { page-break-before: always; }
    .signature { margin-top: 22pt; }
    .signature td { border: 0; padding: 22pt 20pt 0 0; width: 50%; }
    .signature-line { border-top: 0.7pt solid #6f7f8d; padding-top: 4pt; color: #667788; font-size: 8pt; }
</style>

<div class="header">PROYECTO DE ASISTENCIA · MATRIZ DE RIESGOS</div>
<div class="footer">ISFT N.º 38 · Página <span class="page-number"></span></div>

<div class="cover">
    <div class="eyebrow">ESTIMAR Y ANTICIPAR</div>
    <h1>Matriz de riesgos</h1>
    <div class="subtitle">Sistema web de gestión y registro de asistencia académica mediante planilla diaria y códigos QR</div>

    <div class="meta">
        <p><strong>Proyecto:</strong> Módulo de Asistencia</p>
        <p><strong>Institución:</strong> Instituto Superior de Formación Técnica N.º 38</p>
        <p><strong>Tecnología analizada:</strong> Laravel, MySQL y autenticación web por roles</p>
        <p><strong>Fecha de evaluación:</strong> 18 de septiembre de 2026</p>
        <p><strong>Versión:</strong> 1.0</p>
    </div>
</div>

<div class="page-break"></div>

<h2>1. Propósito y alcance</h2>
<p>Este documento identifica los acontecimientos inciertos que podrían afectar la calidad, seguridad, continuidad y adopción del proyecto de asistencia. El análisis se basa en las funciones actualmente implementadas: administración de alumnos, profesores, materias y carreras; asignación por roles; registro diario; estados presente, ausente, tarde y justificado; cálculo de porcentajes; y auto-registro mediante códigos QR.</p>
<p>Como en el documento tomado como modelo, se distingue un <strong>riesgo</strong> —algo que podría ocurrir— de un <strong>problema</strong> —algo que ya ocurrió—. La matriz permite priorizar el trabajo preventivo sin asumir que todos los riesgos merecen la misma respuesta.</p>

<h2>2. Estimación del trabajo por Story Points</h2>
<p>La estimación se expresa en <strong>Story Points (SP)</strong> sobre la escala 1, 2, 3, 5, 8 y 13. Cada valor combina complejidad técnica, volumen de trabajo e incertidumbre. No representa horas ni días de calendario: permite comparar el tamaño relativo de las historias, organizar iteraciones y detectar las partes del proyecto que necesitan mayor análisis.</p>
<p>Las historias se derivan de las funciones observadas en el módulo de asistencia. Ninguna supera 13 puntos; una historia mayor debería dividirse antes de ingresar a una iteración.</p>

<table class="risk-table">
    <thead>
        <tr>
            <th style="width:31%">Historia de usuario / trabajo</th>
            <th style="width:8%">SP</th>
            <th style="width:61%">Justificación</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Autenticación y acceso según rol</strong><br>Permitir el ingreso con DNI o correo y diferenciar directora, preceptor, profesor y alumno.</td><td class="center"><strong>8</strong></td><td>Combina autenticación, resolución de roles heredados, autorización por operación y protección frente a accesos cruzados. Un error afecta todo el módulo.</td></tr>
        <tr><td><strong>Administración de usuarios y carreras</strong><br>Crear y actualizar alumnos, profesores y preceptores, vinculándolos con sus carreras.</td><td class="center"><strong>8</strong></td><td>Incluye validaciones de identidad, sincronización entre usuarios y registros, múltiples carreras por preceptor y restricciones especiales para la dirección.</td></tr>
        <tr><td><strong>Asignación de profesores y alumnos</strong><br>Relacionar profesores con materias y alumnos con las materias de su carrera.</td><td class="center"><strong>5</strong></td><td>El volumen es medio, pero requiere validar pertenencia, evitar asignaciones fuera de carrera y convivir con horarios y datos ya existentes.</td></tr>
        <tr><td><strong>Planilla diaria de asistencia</strong><br>Registrar presente, ausente, tarde o justificado y el motivo correspondiente.</td><td class="center"><strong>8</strong></td><td>Es el núcleo funcional. Debe respetar el día de cursada, impedir cambios sobre fechas no habilitadas, validar alumnos y mantener unicidad por materia, alumno y fecha.</td></tr>
        <tr><td><strong>Registro de asistencia mediante QR</strong><br>Generar, habilitar e inhabilitar códigos para presente o tarde y permitir el auto-registro del alumno.</td><td class="center"><strong>8</strong></td><td>Agrega tokens, vigencia diaria, autenticación, pertenencia a la materia, prevención de reutilización y coordinación en tiempo real con la planilla del profesor.</td></tr>
        <tr><td><strong>Historial y porcentajes</strong><br>Mostrar el detalle por alumno y calcular presentes, tardanzas, ausencias y justificaciones.</td><td class="center"><strong>5</strong></td><td>Las consultas son conocidas, pero la regla de cálculo —incluida la media presencia por tardanza— debe ser consistente en vistas de profesor y alumno.</td></tr>
        <tr><td><strong>Modelo de datos e integridad</strong><br>Crear tablas, relaciones, índices y restricciones para asistencias, QR y asignaciones.</td><td class="center"><strong>5</strong></td><td>El diseño es acotado, aunque debe integrarse con tablas heredadas, soportar migraciones seguras y preservar la consistencia de los antecedentes.</td></tr>
        <tr><td><strong>Pruebas, despliegue y validación con usuarios</strong><br>Probar flujos por rol, migrar el entorno y validar la operación durante una clase.</td><td class="center"><strong>8</strong></td><td>Depende de datos representativos y disponibilidad de docentes y alumnos. Incluye casos negativos, QR, permisos, porcentajes, recuperación y capacitación básica.</td></tr>
    </tbody>
</table>

<div class="note"><strong>Total estimado del backlog inicial: 55 Story Points.</strong> Las historias de mayor tamaño son autenticación y roles, administración de usuarios, planilla diaria, QR y validación final. El total sirve para planificar capacidad por iteración; no debe convertirse directamente en horas sin medir antes la velocidad real del equipo.</div>

<h3>Distribución sugerida en cuatro iteraciones</h3>
<table class="risk-table">
    <thead><tr><th style="width:16%">Iteración</th><th>Alcance principal</th><th style="width:15%">SP previstos</th></tr></thead>
    <tbody>
        <tr><td>Iteración 1</td><td>Modelo de datos, autenticación y base de roles.</td><td class="center">13</td></tr>
        <tr><td>Iteración 2</td><td>Administración de usuarios, carreras y asignaciones.</td><td class="center">13</td></tr>
        <tr><td>Iteración 3</td><td>Planilla diaria y registro mediante QR.</td><td class="center">16</td></tr>
        <tr><td>Iteración 4</td><td>Historial, porcentajes, pruebas, despliegue y validación.</td><td class="center">13</td></tr>
    </tbody>
</table>
<p class="small">La distribución es una propuesta inicial. Los 16 puntos de la tercera iteración pertenecen a dos historias independientes de 8 SP; no constituyen una historia única superior a 13.</p>

<div class="page-break"></div>

<h2>3. Criterios de evaluación de riesgos</h2>
<table>
    <thead><tr><th style="width:18%">Dimensión</th><th style="width:18%">Nivel</th><th>Criterio aplicado al proyecto</th></tr></thead>
    <tbody>
        <tr><td rowspan="3"><strong>Probabilidad</strong></td><td>Baja</td><td>Es poco probable durante el ciclo lectivo o requiere condiciones excepcionales.</td></tr>
        <tr><td>Media</td><td>Puede ocurrir en algunas materias, usuarios o despliegues.</td></tr>
        <tr><td>Alta</td><td>Es esperable si no se incorpora un control preventivo específico.</td></tr>
        <tr><td rowspan="3"><strong>Impacto</strong></td><td>Bajo</td><td>Interrupción o corrección menor, sin comprometer datos académicos.</td></tr>
        <tr><td>Medio</td><td>Afecta una clase, materia o grupo y exige intervención operativa.</td></tr>
        <tr><td>Alto</td><td>Puede alterar antecedentes académicos, exponer datos personales o impedir el servicio.</td></tr>
    </tbody>
</table>

<div class="note"><strong>Regla de prioridad:</strong> se usa el mismo cruce cualitativo del modelo. Son críticos los riesgos con probabilidad alta e impacto alto, o con probabilidad media e impacto alto. Los restantes se clasifican como moderados o menores.</div>

<h2>4. Riesgos identificados</h2>

<h3>R1 · Registro indebido mediante un QR compartido</h3>
<p>Un alumno podría fotografiar o reenviar el QR habilitado a otra persona que no se encuentre físicamente en el aula. Aunque el sistema verifica autenticación, rol, materia, fecha y habilitación, no acredita presencia física. La consecuencia sería una asistencia válida técnicamente pero falsa desde el punto de vista académico.</p>

<h3>R2 · Configuración incorrecta de roles y asignaciones</h3>
<p>Una asociación errónea entre usuario, profesor, carrera, materia o alumno podría conceder acceso indebido o impedir el acceso legítimo. La complejidad aumenta porque el proyecto convive con datos y tablas heredadas y resuelve el rol desde distintos campos del usuario.</p>

<h3>R3 · Pérdida de historial al quitar o eliminar entidades</h3>
<p>Al quitar un alumno de una materia se eliminan sus asistencias de esa materia, y las claves foráneas de asistencias usan borrado en cascada. Una operación administrativa equivocada podría destruir antecedentes que deberían conservarse para revisión o auditoría.</p>

<h3>R4 · Falta de conectividad o de un dispositivo durante la clase</h3>
<p>Una caída de red, servidor, base de datos o un problema con los teléfonos puede impedir el uso del QR y retrasar el cierre de la planilla. El riesgo es operativo y frecuente en entornos educativos con infraestructura variable.</p>

<h3>R5 · Porcentajes de asistencia que no representen la regla académica</h3>
<p>El cálculo actual computa una tardanza como media presencia, no suma las justificadas y toma como total las fechas distintas que ya poseen registros. Si la política institucional o la carga de una clase no coincide con estas reglas, el porcentaje mostrado puede inducir decisiones académicas incorrectas.</p>

<h3>R6 · Exposición de datos personales y motivos de justificación</h3>
<p>El sistema almacena DNI, datos de alumnos, historial y motivos de justificación por enfermedad o trabajo. Un permiso mal aplicado, una copia insegura de la base o un entorno con depuración habilitada podría revelar información personal o sensible.</p>

<h3>R7 · Fallo de despliegue por migraciones o datos heredados</h3>
<p>La funcionalidad depende de tablas incorporadas recientemente y de relaciones con registros preexistentes. Una migración incompleta, duplicada o ejecutada fuera de orden puede dejar sin asignaciones, QR o planillas al entorno productivo.</p>

<h3>R8 · Imposibilidad de reconstruir quién modificó una asistencia</h3>
<p>Las asistencias conservan fecha de creación y actualización, pero no una bitácora con usuario, valor anterior, motivo del cambio y origen —profesor o QR—. Ante un reclamo, podría no existir evidencia suficiente para reconstruir lo ocurrido.</p>

<div class="page-break"></div>

<h2>5. Matriz de evaluación</h2>
<table class="risk-table">
    <thead>
        <tr>
            <th style="width:6%">ID</th>
            <th style="width:26%">Evento de riesgo</th>
            <th style="width:12%">Prob.</th>
            <th style="width:12%">Impacto</th>
            <th style="width:13%">Prioridad</th>
            <th style="width:31%">Indicador / disparador</th>
        </tr>
    </thead>
    <tbody>
        <tr><td class="risk-id">R1</td><td>QR compartido y registro sin presencia física.</td><td class="center">Alta</td><td class="center">Alto</td><td class="critical">Crítico</td><td>Registros simultáneos desde ubicaciones o tiempos incompatibles; reclamo del docente.</td></tr>
        <tr><td class="risk-id">R2</td><td>Rol o asignación configurados de forma incorrecta.</td><td class="center">Media</td><td class="center">Alto</td><td class="critical">Crítico</td><td>Usuario que visualiza una materia ajena o recibe un 403 en una materia propia.</td></tr>
        <tr><td class="risk-id">R3</td><td>Borrado accidental de historial de asistencia.</td><td class="center">Baja</td><td class="center">Alto</td><td class="moderate">Moderado</td><td>Solicitud de baja, cambio de carrera o eliminación de una relación con datos históricos.</td></tr>
        <tr><td class="risk-id">R4</td><td>Indisponibilidad de red, servidor o dispositivo.</td><td class="center">Alta</td><td class="center">Medio</td><td class="moderate">Moderado</td><td>Tiempo de respuesta elevado, error de conexión o imposibilidad de abrir el QR.</td></tr>
        <tr><td class="risk-id">R5</td><td>Cálculo de porcentaje distinto de la regla institucional.</td><td class="center">Media</td><td class="center">Alto</td><td class="critical">Crítico</td><td>Diferencias entre planilla manual y sistema; cambio de criterio sobre tardanzas o justificadas.</td></tr>
        <tr><td class="risk-id">R6</td><td>Exposición de datos personales o justificativos.</td><td class="center">Media</td><td class="center">Alto</td><td class="critical">Crítico</td><td>Acceso fuera del rol, respaldo sin protección o detalle técnico visible al usuario.</td></tr>
        <tr><td class="risk-id">R7</td><td>Despliegue incompleto por migraciones y datos heredados.</td><td class="center">Media</td><td class="center">Medio</td><td class="moderate">Moderado</td><td>Tabla inexistente, restricción duplicada o datos sin relación luego de desplegar.</td></tr>
        <tr><td class="risk-id">R8</td><td>Ausencia de auditoría suficiente sobre cambios.</td><td class="center">Media</td><td class="center">Alto</td><td class="critical">Crítico</td><td>Reclamo que exige conocer autor, origen, valor anterior y hora exacta del cambio.</td></tr>
    </tbody>
</table>

<h2>6. Cruce de prioridad</h2>
<table class="matrix">
    <thead>
        <tr><th>Probabilidad \ Impacto</th><th>Impacto bajo</th><th>Impacto medio</th><th>Impacto alto</th></tr>
    </thead>
    <tbody>
        <tr><td class="axis">Alta</td><td class="moderate">Moderado</td><td class="moderate">Moderado<br><span class="small">R4</span></td><td class="critical">Crítico<br><span class="small">R1</span></td></tr>
        <tr><td class="axis">Media</td><td class="minor">Menor</td><td class="moderate">Moderado<br><span class="small">R7</span></td><td class="critical">Crítico<br><span class="small">R2, R5, R6, R8</span></td></tr>
        <tr><td class="axis">Baja</td><td class="minor">Menor</td><td class="moderate">Moderado</td><td class="moderate">Moderado<br><span class="small">R3</span></td></tr>
    </tbody>
</table>

<p><strong>Lectura:</strong> R1 requiere atención inmediata porque combina una forma sencilla de materialización con una consecuencia directa sobre la confiabilidad del registro. R2, R5, R6 y R8 también son críticos: podrían comprometer autorizaciones, decisiones académicas, privacidad o capacidad de responder ante reclamos.</p>

<div class="page-break"></div>

<h2>7. Respuesta para los riesgos críticos</h2>

<div class="response">
    <div class="response-title">R1 · Registro indebido mediante QR — Responsable: equipo técnico + docente</div>
    <div class="response-body">
        <ul>
            <li><strong>Mitigar:</strong> emitir QR de corta duración, rotarlo durante la clase, registrar hora e IP aproximada, mostrar al docente confirmaciones en tiempo real y cerrar el código al finalizar.</li>
            <li><strong>Contingencia:</strong> permitir al profesor invalidar el auto-registro y corregir la planilla, dejando evidencia del cambio.</li>
            <li><strong>Verificación:</strong> prueba controlada de reenvío del QR dentro y fuera del período habilitado.</li>
        </ul>
    </div>
</div>

<div class="response">
    <div class="response-title">R2 · Roles y asignaciones incorrectos — Responsable: administración funcional</div>
    <div class="response-body">
        <ul>
            <li><strong>Mitigar:</strong> definir una fuente única de rol, aplicar autorización por política a cada operación, revisar asignaciones con doble confirmación y ejecutar pruebas por rol y por carrera.</li>
            <li><strong>Contingencia:</strong> bloquear temporalmente al usuario afectado, restaurar la asignación correcta y revisar las acciones efectuadas durante el período.</li>
            <li><strong>Verificación:</strong> matriz de permisos con casos positivos y negativos para directora, preceptor, profesor y alumno.</li>
        </ul>
    </div>
</div>

<div class="response">
    <div class="response-title">R5 · Porcentaje incorrecto — Responsable: referente académico + equipo técnico</div>
    <div class="response-body">
        <ul>
            <li><strong>Mitigar:</strong> documentar y aprobar la regla para tardanzas y justificadas; registrar cada clase prevista; centralizar el cálculo en un único servicio y cubrirlo con pruebas automatizadas.</li>
            <li><strong>Contingencia:</strong> suspender decisiones basadas en el porcentaje, exportar el historial y recalcular luego de corregir la regla.</li>
            <li><strong>Verificación:</strong> comparar casos límite con una planilla aprobada por la institución.</li>
        </ul>
    </div>
</div>

<div class="response">
    <div class="response-title">R6 · Exposición de datos — Responsable: administración del sistema</div>
    <div class="response-body">
        <ul>
            <li><strong>Mitigar:</strong> usar HTTPS, depuración desactivada en producción, mínimos privilegios, respaldos cifrados, control de acceso a justificativos y política de retención.</li>
            <li><strong>Contingencia:</strong> revocar sesiones y credenciales, preservar registros, delimitar los datos expuestos, notificar a responsables y corregir el permiso o entorno.</li>
            <li><strong>Verificación:</strong> revisión de configuración productiva y pruebas de acceso cruzado entre roles.</li>
        </ul>
    </div>
</div>

<div class="response">
    <div class="response-title">R8 · Falta de trazabilidad — Responsable: equipo técnico</div>
    <div class="response-body">
        <ul>
            <li><strong>Mitigar:</strong> crear una bitácora inmutable con usuario, fecha y hora, origen, valor anterior y nuevo, materia, alumno y motivo; restringir su consulta.</li>
            <li><strong>Contingencia:</strong> conservar copias de base y logs de aplicación, reconstruir el evento con timestamps disponibles y documentar la incertidumbre.</li>
            <li><strong>Verificación:</strong> simular una corrección y comprobar que el historial permita explicar todo el cambio.</li>
        </ul>
    </div>
</div>

<h2>8. Tratamiento de los riesgos moderados</h2>
<table class="risk-table">
    <thead><tr><th style="width:9%">ID</th><th style="width:45%">Acción preventiva</th><th>Contingencia</th></tr></thead>
    <tbody>
        <tr><td class="risk-id">R3</td><td>Reemplazar borrado físico por baja lógica; impedir eliminar relaciones con historial sin confirmación reforzada; respaldar antes de operaciones masivas.</td><td>Restaurar desde respaldo y reconciliar registros con la planilla del profesor.</td></tr>
        <tr><td class="risk-id">R4</td><td>Disponer de planilla local imprimible o modo de carga diferida; monitorear servicio antes del horario de cursada.</td><td>Tomar asistencia fuera de línea y habilitar una ventana controlada de carga posterior con auditoría.</td></tr>
        <tr><td class="risk-id">R7</td><td>Ensayar migraciones sobre copia anonimizada, verificar prerequisitos y preparar lista de comprobación y reversión.</td><td>Volver a la versión anterior, restaurar la copia previa y corregir la migración en un entorno de prueba.</td></tr>
    </tbody>
</table>

<h2>9. Seguimiento</h2>
<p>La matriz debe revisarse al comienzo de cada iteración y antes de cada despliegue. Cada responsable informa cambios en probabilidad, impacto, disparadores observados y estado de las acciones. Un riesgo materializado deja de administrarse como posibilidad y pasa al registro de incidentes o problemas, con responsable y fecha de resolución.</p>

<table class="signature">
    <tr>
        <td><div class="signature-line">Responsable del proyecto / Fecha</div></td>
        <td><div class="signature-line">Referente académico / Fecha</div></td>
    </tr>
</table>
HTML;

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->render();
file_put_contents($outputPath, $dompdf->output());

echo $outputPath . PHP_EOL;
