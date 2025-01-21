<?php
require_once('../../inc/config.php');
require_once('jqGrider.php');
$sql = "
    SELECT bxd.bodega_fecha_id, bxd.Fecha, IFNULL(b.grupo, '-') as Grupo, b.Bodega, 
        bxd.Existencia_Rollos as 'Rolls', 
        bxd.Existencia_Containers as 'Containers', 
        bxd.Existencia_Quantity_Mts as 'Qty_Mts',
        bxd.Existencia_Quantity_Kg as 'Qty_Kg', 
        bxd.Existencia_Kg as 'Kilos'
    FROM bodega_x_dia bxd
        LEFT OUTER JOIN bodega b ON bxd.Bodega = b.Bodega";

$grid = new jqGrider();
echo json_encode($grid->readQuery(
  $sql,
  "bxd.fecha > '2022-08-31' AND bxd.fecha < CURRENT_DATE",
    sumCols: ['Rolls', 'Containers', 'Qty_Mts', 'Qty_Kg', 'Kilos']
));
