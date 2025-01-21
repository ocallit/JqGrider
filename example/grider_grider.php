<?php
require_once('../../inc/config.php');
$bodegas = ia_sqlKeyValue("SELECT DISTINCT bodega, bodega FROM bodega_x_dia ORDER BY bodega_x_dia.bodega");
?><!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Grider</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <?php global $gIaHeader;$gIaHeader->html_head_add(['jqgrid', 'ia', 'highlightor', 'multiselect']);$gIaHeader->html_head_echo(); ?>
</head>
<body>
<div id="elGridContainerGrid" style="margin:1em;padding:0 0.3em 0.3em 0; width:fit-content; box-shadow: 3px 3px 0 0 rgba(135, 182, 217, 1), 0 2px 2px rgba(135, 182, 217, 1)">
    <table id="elGridGrid"></table>
    <div id="elGridPager"></div>
</div>
<style>
    .EditTable { width: auto!important; table-layout:auto;border-collapse: collapse;box-shadow: 3px 3px 0 0 rgba(0, 0, 139, 0.6), 0 2px 2px rgba(0, 0, 139, 0.7);margin:0.5em;}
    .ViewTable {width: auto!important; table-layout:autoborder-collapse: collapse;box-shadow: 3px 3px 0 0 rgba(0, 0, 139, 0.6), 0 2px 2px rgba(0, 0, 139, 0.7);margin:0.5em;}
    .CaptionTD {color: darkblue;width:initial;padding:0.3em;}
    .form-view-label {border:1px darkblue solid!important;}
    .DataTD {text-align: right;color:blue;width:initial;padding:0.3em;}
    .form-view-data {border:1px darkblue solid!important;}
</style>
<script src="colmo.js"></script>
<script defer>
    var colModel = [
        {name:'bodega_fecha_id', key:true, hidden:true},

        {name:'Fecha', ...getTemplate('date'), },
        {name:'Grupo', align:'center', width:110, ...getTemplate("Enum('CLAVEL', 'COYUYA')")},
        {name:'Bodega', index:'b.bodega', width:170, ...colmoAddSelect(<?php echo json_encode($bodegas); ?>) },

        {name:'Rolls', ...getTemplate("Mediumint"), width:120, footerValue:'max'},
        {name:'Containers', ...getTemplate("Decimal(10,2)"), footerValue:'max'},
        {name:'Qty_Mts', ...getTemplate("decimal(10,2)"), footerValue:'max'},
        {name:'Qty_Kg', ...getTemplate("decimal(10,2)"),  footerValue:'max'},
        {name:'Kilos', ...getTemplate("decimal(10,2)"),  footerValue:'max'},

    ];
    var gridhandler;
    jQuery(function($){
        var gridId = 'elGridGrid';
        var pagerId = 'elGridPager';
        var $pager = $("#" + pagerId);
        gridhandler =$("#" + gridId ).jqGrid({

            caption:'Existencai Diaria por Bodega',
            datatype: 'json',
            url: 'jqGrider_responder.php',
            method: 'POST',

            colModel: colModel,
            cmTemplate:{search:true, stype:'text',edittype:'text',editable:false, searchoptions:{ clearSearch:true, sopt:['eq','ne','lt','le','gt','ge']} },

            footerrow: true,
            footerrowpos: 'footer',
            //footerClass: 'jaGriderFooterClass',
            userDataOnFooter:false,

            forceFit: false,
            shrinkToFit: false,
            height: window.innerHeight - 300 < 200 ? 200 : window.innerHeight - 300,
            //width: 1600,
            sortable:true,
            autoencode: true,
            ignoreCase: true,

            pager: $pager,

            pginput:true,
            pgbuttons:true,
            // recordtext
            // emptyrecords
            viewrecords:true,
            rowList: [20, 30, 40, 50, 75, 100],
            rowNum: 50,

            rownumbers:true,
            rownumWidth: 60,

            sortname: 'bxd.fecha DESC, b.grupo, b.Bodega',
            sortorder: '',

            gridview: true,
            loadError: function(xhr,st,err) {
                alert("Error inesperado, Intente mas tarde");
                console.error("jqGrid loadError: ", err);
                console.log("              ", arguments);
            }
        })
        .jqGrid('navGrid','#'+pagerId,
            { // Navigator
                edit: false, add: false, del: false, search: false, refresh: false, view: true, position: "left", cloneToTop: false},
            { // Edit
                closeAfterEdit: true, recreateForm: true, checkOnUpdate: true, closeOnEscape: true},
            { // Add
                closeAfterAdd: true, recreateForm: true, checkOnUpdate: true, closeOnEscape: true},
            { // Delete
                closeOnEscape: true},
            { // Search
                multipleSearch: true, closeOnEscape: true, closeAfterSearch: true},
            { // View
                closeOnEscape: true}
        )
        .jqGrid('filterToolbar', {
            defaultSearch: 'cn', searchOperators: true, searchOnEnter: true, autosearch: true,})
        .jqGrid('gridResize', {
            minWidth: 250, maxWidth: 2000, minHeight: 80, maxHeight: 2000})
        .off('jqGridAfterLoadComplete', jqGUtil.footerFormatedUserData).on('jqGridAfterLoadComplete', jqGUtil.footerFormatedUserData)
        // .off('jqGridAfterLoadComplete', jqGUtil.footerColumnTotal).on('jqGridAfterLoadComplete', jqGUtil.footerColumnTotal) // para loadonce
        // jqGUtil.navButtonExportCsv(gridId, pagerId);  // para loadonce
    })
</script>
</body>
</html>