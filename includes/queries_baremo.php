<?php
/**
 * Consultas relacionadas con el baremo (preguntas para entrevista).
 */

if (!function_exists('query_baremo_preguntas_por_categoria')) {
    /**
     * Obtiene todas las preguntas del baremo agrupadas por categoría (usado por llenado_de_perfil.php).
     *
     * @param PDO $pdo
     * @return array mapa categoría => lista de filas
     */
    function query_baremo_preguntas_por_categoria($pdo)
    {
        $preguntas_por_categoria = [];
        $sql = 'SELECT * FROM baremo_preguntas ORDER BY categoria, orden';
        $stmtBaremo = $pdo->query($sql);
        while ($row = $stmtBaremo->fetch(PDO::FETCH_ASSOC)) {
            $preguntas_por_categoria[$row['categoria']][] = $row;
        }
        return $preguntas_por_categoria;
    }
}
