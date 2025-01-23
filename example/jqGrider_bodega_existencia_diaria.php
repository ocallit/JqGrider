<?php
require_once('../../inc/config.php');
require_once('jqGrider.php');

    if(param('por', 'bodega') === 'bodega') {
        $sqlReadRows = "
            SELECT bxd.bodega_fecha_id, bxd.Fecha, IFNULL(b.grupo, '-') AS Grupo, b.Bodega, 
                bxd.Existencia_Rollos AS 'Rolls', 
                bxd.Existencia_Containers AS 'Containers', 
                bxd.Existencia_Quantity_Mts AS 'Qty_Mts',
                bxd.Existencia_Quantity_Kg AS 'Qty_Kg', 
                bxd.Existencia_Kg AS 'Kilos'
            FROM bodega_x_dia bxd
                LEFT OUTER JOIN bodega b ON bxd.Bodega = b.Bodega";
        $grid = new Ocallit\jqGrider\JqGridReader();
        echo json_encode($grid->readQuery(
          $sqlReadRows,
          "bxd.fecha > '2022-08-31' AND bxd.fecha <= CURRENT_DATE",
          sumColumns: ['Rolls'=>'all', 'Containers'=>'all', 'Qty_Mts'=>'all', 'Qty_Kg'=>'all', 'Kilos'=>'all']
        ));
        ia_errores_a_dime();
        file_debug_reporte();
        die();
    }

    $grid = new Ocallit\jqGrider\JqGridReader();

    $where = $grid->buildWhereClause();
    $where = "WHERE bxd.fecha > '2022-08-31' AND bxd.fecha <= CURRENT_DATE " . (empty($where) ? "" : " AND ($where)" );

    $sqlReadRows = "
        SELECT CONCAT(bxd.Fecha, '_', IF(b.grupo='', b.bodega, b.grupo)) as bodega_fecha_id, bxd.Fecha, IFNULL(b.grupo, '-') AS Grupo, IF(b.grupo='', b.bodega, b.grupo) AS Bodega, 
            SUM(bxd.Existencia_Rollos) AS 'Rolls', 
            SUM(bxd.Existencia_Containers) AS 'Containers', 
            SUM(bxd.Existencia_Quantity_Mts) AS 'Qty_Mts',
            SUM(bxd.Existencia_Quantity_Kg) AS 'Qty_Kg', 
            SUM(bxd.Existencia_Kg) AS 'Kilos'
        FROM bodega_x_dia bxd
            LEFT OUTER JOIN bodega b ON bxd.Bodega = b.Bodega
        $where
        GROUP BY CONCAT(bxd.Fecha, '_', IF(b.grupo='', b.bodega, b.grupo)), bxd.Fecha, IFNULL(b.grupo, '-'), IF(b.grupo='', b.bodega, b.grupo)
        ";

    [ 'toSum' => $toSum, 'multipleFooter' => $multipleFooter ] =
        $grid->sumColumns(['Rolls'=>'all', 'Containers'=>'all', 'Qty_Mts'=>'all', 'Qty_Kg'=>'all', 'Kilos'=>'all']);
    $sqlTotals = "WITH JqGrider_cnt AS ($sqlReadRows) SELECT $toSum FROM JqGrider_cnt";

    echo json_encode($grid->read($sqlReadRows, $sqlTotals, $multipleFooter));

    ia_errores_a_dime();
    file_debug_reporte();