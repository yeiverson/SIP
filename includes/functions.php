<?php
/**
 * Funciones helpers compartidas.
 */

function h($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function solo_numeros($valor) {
    return preg_replace('/\D/', '', (string)$valor);
}

function formatear_cedula($tipo, $numero) {
    return $tipo . '-' . number_format((int)$numero, 0, '', '.');
}

function formatear_fecha($fecha) {
    if (!$fecha) return '—';
    $timestamp = strtotime($fecha);
    return date('d/m/Y', $timestamp);
}

function obtener_meses() {
    return ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
            'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
}

function obtener_dias() {
    return ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
}

function fecha_hoy_formateada() {
    $dias = obtener_dias();
    $meses = obtener_meses();
    return $dias[date('w')] . ', ' . date('d') . ' de ' . $meses[date('n')-1] . ' de ' . date('Y');
}

function escapar_texto_multilinea($texto) {
    return nl2br(h($texto));
}

function alerta_success($mensaje) {
    return "<div class='alert alert-success'>✅ " . h($mensaje) . "</div>";
}

function alerta_error($mensaje) {
    return "<div class='alert alert-error'>❌ " . h($mensaje) . "</div>";
}

function alerta_warning($mensaje) {
    return "<div class='alert alert-warning'>⚠️ " . h($mensaje) . "</div>";
}

function alerta_info($mensaje) {
    return "<div class='alert alert-info'>ℹ️ " . h($mensaje) . "</div>";
}
