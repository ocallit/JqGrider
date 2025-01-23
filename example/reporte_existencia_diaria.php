<?php
require_once('../inc/config.php');
if(!usuarioTipoRony())
    require_once('../backoffice/sin_permiso.php');
$bodegas = ia_sqlKeyValue("SELECT DISTINCT bodega, bodega FROM bodega_x_dia ORDER BY bodega_x_dia.bodega");
?><!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Existencia Diaria por Bodega</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <?php global $gIaHeader;$gIaHeader->html_head_add(['jqgrid', 'ia', 'highlightor', 'multiselect']);$gIaHeader->html_head_echo(); ?>
    <style>
        .switch2_container {
            display: inline-block;
            font-family: Arial, sans-serif;
        }

        .switch2_toggle-input {
            display: none;
        }

        .switch2_toggle-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 5px;
            background: #f0f0f0;
            border-radius: 25px;
            position: relative;
            transition: all 0.3s ease;
            user-select: none;
        }

        .switch2_toggle-option {
            padding: 8px 12px;
            z-index: 1;
            color: #666;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .switch2_toggle-slider {
            position: absolute;
            left: 5px;
            width: calc(50% - 5px);
            height: calc(100% - 10px);
            background: #fff;
            border-radius: 20px;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .switch2_toggle-input:checked + .switch2_toggle-label .switch2_toggle-slider {
            transform: translateX(calc(100% + 5px));
        }

        .switch2_toggle-input:checked + .switch2_toggle-label .switch2_toggle-option.switch2_left {
            color: #666;
        }

        .switch2_toggle-input:not(:checked) + .switch2_toggle-label .switch2_toggle-option.switch2_left {
            color: #333;
        }

        .switch2_toggle-input:checked + .switch2_toggle-label .switch2_toggle-option.switch2_right {
            color: #333;
        }

        .switch2_toggle-input:not(:checked) + .switch2_toggle-label .switch2_toggle-option.switch2_right {
            color: #666;
        }

        /* Hover effect */
        .switch2_toggle-label:hover {
            background: #e8e8e8;
        }
    </style>
</head>
<body>
<?php
include_once('../backoffice/header.php');
$f=new iacase_base();
$f->permiso_insert=$f->permiso_update=$f->permiso_delete=$f->permiso_export=false;
$f->label="Existencia Diaria por Bodega";
$f->toolbar_set();
$f->display_toolbar();
?>
<div style="margin:1.1em;">
Existencia por:
<div class="switch2_container">
    <input type="checkbox" id="viewToggle" class="switch2_toggle-input">
    <label for="viewToggle" class="switch2_toggle-label">
        <span class="switch2_toggle-option switch2_left">Por Bodega</span>
        <span class="switch2_toggle-slider"></span>
        <span class="switch2_toggle-option switch2_right">Por Grupo</span>
    </label>
</div>
<script>

    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('viewToggle');
        toggle.addEventListener('change', function() {
            $("#viewToggle").prop("disabled", true);
            const selectedValue = toggle.checked ? 'grupo' : 'bodega';
            let postData = gridhandler.jqGrid('getGridParam', "postData");
            postData.por = selectedValue;
            gridhandler.jqGrid('setGridParam', "postData", postData).trigger('reloadGrid');
        });
    });
</script>
</div>
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
<script src="../js2/colmo.js"></script>
<script defer>
    var colModel,gridhandler;
    var elGridIacSel;
    function enablePorGrupoBodega() {$("#viewToggle").prop("disabled", false);}
    jQuery(function($) {

        colModel = [
            {
                name: "iacsel",
                index: "iacsel",
                label: "&Sigma;",
                colmenu: false,
                template: iacJqGridSelect().iacselColTemplate,
                ctrl: true,
                ctrlId: '#iactoolbarselect',
                ctrlButtonset: true,

                sumCols: {
                    'Containers': {'f': 'sum'},
                    'Rolls': {'f': 'sum'},
                    'Kilos': {'f': 'sum'},
                    'Qty_Kg': {'f': 'sum'},
                    'Qty_Mts': {'f': 'sum'},
                }
            },
            {name: 'bodega_fecha_id', key: true, hidden: true},

            {name: 'Fecha', ...getTemplate('date'), formoptions: {rowpos: 2, colpos: 1}},
            {
                name: 'Grupo',
                align: 'center',
                width: 110, ...getTemplate("Enum('CLAVEL', 'COYUYA')"),
                formoptions: {rowpos: 3, colpos: 1}
            },
            {
                name: 'Bodega',
                index: 'b.bodega',
                width: 170, ...colmoAddSelect(<?php echo json_encode($bodegas); ?>),
                formoptions: {rowpos: 3, colpos: 2}
            },


            {
                name: 'Containers', index:'Existencia_Containers',  ...getTemplate("Decimal(10,2)"),
                footerValue: 'max',
                formoptions: {rowpos: 4, colpos: 1}
            },
            {
                name: 'Rolls', index:'Existencia_Rollos', ...getTemplate("Mediumint"),
                width: 120,
                footerValue: 'max',
                formoptions: {rowpos: 5, colpos: 1}
            },
            {name: 'Kilos', index:'Existencia_Kg', ...getTemplate("decimal(10,2)"), footerValue: 'max', formoptions: {rowpos: 6, colpos: 1}},
            {name: 'Qty_Kg', index:'Existencia_Quantity_Kg', ...getTemplate("decimal(10,2)"), footerValue: 'max', formoptions: {rowpos: 7, colpos: 1}},
            {name: 'Qty_Mts', index:'Existencia_Quantity_Mts', ...getTemplate("decimal(10,2)"), footerValue: 'max', formoptions: {rowpos: 7, colpos: 2}},


        ];
        var gridId = 'elGridGrid';
        var pagerId = 'elGridPager';
        var $pager = $("#" + pagerId);
        gridhandler = $("#" + gridId).jqGrid({

            caption: '',
            datatype: 'json',
            url: '../backoffice/ajax/jqGrider_bodega_existencia_diaria.php',
            method: 'POST',

            colModel: colModel,
            cmTemplate: {
                search: true,
                stype: 'text',
                edittype: 'text',
                editable: false,
                searchoptions: {clearSearch: true, sopt: ['eq', 'ne', 'lt', 'le', 'gt', 'ge']}
            },

            footerrow: true,
            footerrowpos: 'footer',
            //footerClass: 'jaGriderFooterClass',
            userDataOnFooter: false,

            forceFit: false,
            shrinkToFit: false,
            height: window.innerHeight - 300 < 200 ? 200 : window.innerHeight - 300,
            //width: 1600,
            sortable: true,
            autoencode: true,
            ignoreCase: true,

            pager: $pager,

            pginput: true,
            pgbuttons: true,
            // recordtext
            // emptyrecords
            viewrecords: true,
            rowList: [20, 30, 40, 50, 75, 100],
            rowNum: 50,

            rownumbers: true,
            rownumWidth: 60,
            storeNavOptions: true,

            sortname: 'bxd.fecha DESC, b.grupo, b.Bodega',
            sortorder: '',

            gridview: true,
            loadError: function(xhr, st, err) {
                alert("Error inesperado, Intente mas tarde");
                console.error("jqGrid loadError: ", err);
                console.log("              ", arguments);
            },

        })
        .jqGrid('navGrid', '#' + pagerId,
                { // Navigator
                    edit: false,
                    add: false,
                    del: false,
                    search: false,
                    refresh: false,
                    view: true,
                    position: "left",
                    cloneToTop: false
                },

                { // Edit
                    closeAfterEdit: true, recreateForm: true, checkOnUpdate: true, closeOnEscape: true
                },
                { // Add
                    closeAfterAdd: true, recreateForm: true, checkOnUpdate: true, closeOnEscape: true
                },
                { // Delete
                    closeOnEscape: true
                },
                { // Search
                    multipleSearch: true, closeOnEscape: true, closeAfterSearch: true
                },
                { // View
                    closeOnEscape: true, caption: 'Existencia Diaria por Bodega'
                },
            )
            .jqGrid('filterToolbar', {
                defaultSearch: 'cn', searchOperators: true, searchOnEnter: true, autosearch: true,
            })
            .jqGrid('gridResize', {
                minWidth: 250, maxWidth: 2000, minHeight: 80, maxHeight: 2000
            })
            .off('jqGridAfterLoadComplete', jqGUtil.footerFormatedUserData).on('jqGridAfterLoadComplete', jqGUtil.footerFormatedUserData)
            .off('jqGridAfterLoadComplete', enablePorGrupoBodega).on('jqGridAfterLoadComplete', enablePorGrupoBodega)
        // .off('jqGridAfterLoadComplete', jqGUtil.footerColumnTotal).on('jqGridAfterLoadComplete', jqGUtil.footerColumnTotal) // para loadonce
        // jqGUtil.navButtonExportCsv(gridId, pagerId);  // para loadonce

    });
</script>
<i>@ToDo:<ul>
    <li>Terminar de revisar</li>
    <li>Exportar</li>
    <li>Link a gráfica</li>
    <li>Link de gráfica</li>
    <li>CIF china/report</li>
</ul></i>
<?php include_once('../backoffice/footer.php'); ?>
</body>
</html>