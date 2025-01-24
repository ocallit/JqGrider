<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>Catego</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.min.js" integrity="sha512-csIng5zcB+XpulRUa+ev1zKo7zRNGpEaVfNB9On1no9KYTEY/rLGAEEpvgdw6nim1WdTuihZY1eqZ31K7/fZjw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style id="general-css-styles">
        BUTTON {cursor: pointer;}
        SELECT {background-color: white}
        .pointer {cursor: pointer;}
    </style>
    <style id="catego-css-styles">
        .catego-acota {font-size:10px;color:gray;text-align:center;font-style:italic;font-family:Courier New, Courier, monospace;line-height:0.9em;padding:0;margin:0.3em 0 0 0}
        .catego_rojo {color:red}
        .catego_inactivo {color: red; text-decoration: line-through;}
        .catego_manage {cursor:pointer;}
        .catego_dialog {
            border:7px groove maroon;border-image: linear-gradient(to right,  #ed2756, blue, darkblue, maroon) 1;padding:0;max-height:80vh;overflow:auto;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
        }
        .catego_dialog::backdrop {background-color: rgba(245, 245, 245, 0.2);}
        .catego_dialog_title {
            background-color:maroon;background: linear-gradient(to right,  #ed2756, blue, darkblue, maroon); font-weight:bold;margin:0;padding:5px;
            display:flex;justify-content:space-between;align-items:center;
        }
        .catego_dialog_title_frase {font-size:1.4em;font-weight:bold;color:white}
        .catego_dialog_close {}
        .catego_dialog_content {margin:0.3em;padding:0.3em}
        .catego_dialog_new_catego {border-bottom:1px solid maroon;padding:0.3em;margin:0.3em}
        .catego_dialog_add {}
        .catego_reorder {vertical-align: bottom;text-align:center;cursor:grab}
        .catego_action {display:flex;justify-content:space-between;align-items:center;gap:1em; padding:0.3em}
        .catego_cell {width:28em}
        TABLE.catego-tabler {border:1px darkblue solid; border-collapse: collapse;box-shadow:darkblue 4px 4px 0 0}
        TABLE.catego-tabler {border:1px darkblue solid; border-collapse: collapse;box-shadow: 3px 3px 0 0 rgba(0, 0, 139, 0.6), 0 2px 2px rgba(0, 0, 139, 0.7)}
        TABLE.catego-tabler CAPTION {border:1px darkblue solid; font-weight: 800; font-size:1.3em }
        TABLE.catego-tabler TD {border:1px darkblue solid; text-align: left; vertical-align: top; padding:0.4em; }
        TABLE.catego-tabler TH {border:1px darkblue solid; text-align: center; vertical-align: middle; padding:0.4em; font-weight: bold;color:#00F}
        TABLE.catego-tabler TD.izq {text-align: left; }
        TABLE.catego-tabler TD.lft {text-align: left;}
        TABLE.catego-tabler TD.cen {text-align: center;}
        TABLE.catego-tabler TD.der {text-align: right; }
        TABLE.catego-tabler TD.rgt {text-align: right; }
        TABLE.catego-tabler TR.totalAbove TD {font-weight: bold;border-bottom: 8px double darkgray!important;}
        TABLE.catego-tabler TR.totalBelow TD {font-weight: bold;border-TOP: 8px double darkgray!important;}
    </style>
    <style id="sortable-css-styles">
        .sortable-ghost {
            opacity: .5;
            background: #C8EBFB;
        }
        .sortable-chosen {
            opacity: 0.5;
            border: 2px dashed #000;
        }
        .sortable-drag {
            position: absolute;
            pointer-events: none;
            z-index: 9999;
        }
        .sortable-fallback {
            background-color: #eee;
            border: 2px dashed #000;
        }
        .catego_sort_handle {
            cursor: move;
            padding: 5px;
            border-radius: 2px;
        }
    </style>
</head>
<body>
<h1>Catego</h1>
<script>
    function Catego_manager(categoria, catego, element, options) {
        if(!(this instanceof Catego_manager))
            return new Catego_manager(categoria, catego, element, options);
        var categoria = categoria
        var $element = $(element);
        var settings = {
            reorder: true,
            add: true,
            edit:true,
            delete: true,

            'select': null,
            'display': null,
            'hidden': null,
            'url': "../backoffice/ajax/catego_acciones.php",
            method: 'POST',

            url_reorder: "../backoffice/ajax/catego_acciones.php",
            url_delete: "../backoffice/ajax/catego_acciones.php",
            url_update: "../backoffice/ajax/catego_acciones.php",
            url_add: "../backoffice/ajax/catego_acciones.php",
            param_reorder:{accion:'reorder'},
            param_delete: {accion:'delete'},
            param_update: {accion:'update'},
            param_add: {accion:'add'},
            method_reorder: 'POST',
            method_delete: 'POST',
            method_update: 'POST',
            method_add: 'POST',
            'params': {categoria: catego.categoria || ''},}

        var sortable;
        $.extend(true, settings, typeof options === 'undefined' ? {} : options);

        function getValueById(catego_id) {
            for(var c of catego.values)
                if(c.catego_id == catego_id)
                    return c;
            return null;
        }

        function _strim(s) { console.log("strim", s); return s === null ? '' : s.replace(/\s(\s+)/gm, ' ').trim(); }

        function _getZIndex(element) {
            const computedStyle = window.getComputedStyle(element);
            const zIndex = computedStyle.getPropertyValue('z-index');
            return parseInt(zIndex, 10) || 1000;
        }

        function _yaExiste(label, catego_id) {
            for(var c of catego.values)
                if(c.label == label && (c.catego_id != catego_id || catego_id === null))
                    return true;
            return false;
        }

        function _createTableRow(value) {
            let tdActions = "";
            if(settings.edit || settings.delete) {
                tdActions = '<td class="catego_action">';
                if(settings.edit)
                    tdActions += '<div><i class="fa-solid fa-pencil fa-lg catego-ButtonEdit pointer catego-Button catego-ButtonOff" style="color: #0000ff;"></i><p class="catego-acota">Editar</p></div>';
                if(settings.delete)
                    tdActions += '<div><i class="fa-regular fa-trash-can fa-lg catego-ButtonDelete pointer catego-Button catego-ButtonOn" style="color: #800040;"></i><p class="catego-acota">Borrar</p></div>';
            }
            let tdReorder = settings.reorder ? '<td class="catego_reorder"><i class="fas fa-arrows-alt handle catego-sortable-handle"></i></td>' : "";
            return $(`
                <tr data-categoid="${value.catego_id}"">
                    ${tdReorder}
                    ${tdActions}
                    <td class="catego_cell ${value.activo === 'Inactivo' ? ' catego_inactivo' : ''}" data-categomode="read">${value.label}</td>
                </tr>`
            );
        }

        function _createEditableCell(value) {
            return $(`
            <td data-categomode="edit">
                <input type="text" maxlength="32" style="width:20em" required value="${value.label}">
                <select class="catego_select">
                    <option ${value.activo === 'Activo' ? 'selected' : ''}>Activo</option>
                    <option ${value.activo === 'Inactivo' ? 'selected' : ''} class="catego_rojo">Inactivo</option>
                </select>
                |
                <button type="button" class="catego_save"><i class="fa-regular fa-floppy-disk" style="color:darkgreen"></i></button>
                <button type="button" class="catego_cancel"><i class="fa-solid fa-ban" style="color:red"></i></button>
            </td>
        `);
        }

        function _createTable() {
            var $table = $('<table class="catego-tabler"><tbody class="catego_sortable"></tbody></table>');
            var $tbody = $table.find('tbody');
            var values = catego.values;
            for(var c in values)
                if(values.hasOwnProperty(c))
                    $tbody.append(_createTableRow(values[c]));
            return $table;
        }

        function _createDialog() {
            let addDiv =  settings.add ?
                ` <div class="catego_dialog_new_catego">
                        <input type="text" maxlength="32" class="catego_new_catego" style="width:20em"  required placeholder="${catego.label_singular}">
                        <button type="button" class="catego_dialog_add"><i class="fa-solid fa-circle-plus fa-lg" style="color: #008800;"></i><span class="catego-acota">Agregar</span></button>
                  </div>
                ` :
                "";
            let reorderHelp = settings.reorder ?
                `<div style="font-size:0.8em;color:gray;text-align:center;font-style:italic;font-family:Courier New, Courier, monospace;">
                        <div><i style="color:black" class="fas fa-arrows-alt handle"></i> Arrastra reordena.</div>
                    </div>` : "";
            var $dialog = $(`
            <dialog class="catego_dialog" style="z-index:1000">
                <div class="catego_dialog_title">
                    <div><span class="catego_dialog_title_frase">${catego.label_plural}</span></div>
                    <div><button type="button"  class="catego_dialog_close">×</button></div>
                </div>
                <div class="catego_dialog_content">
                    ${addDiv}
                    ${reorderHelp}
                </div>
            </dialog>
        `);
        $dialog.find('.catego_dialog_content').append(_createTable());
        return $dialog;
        }

        function _initializeSortable($tbody) {
            sortable = new Sortable($tbody[0], {
                handle: '.catego-sortable-handle',
                animation: 150,
                dataIdAttr: 'data-categoid',
                onEnd: function() {
                    _saveOrder();
                }
            });
        }

        function _saveOrder() {
            if(!settings.reorder)
                return;
            const newOrder = [];
            for(c of sortable.toArray())
                newOrder.push(getValueById(c));
            catego.values = newOrder;
            if(settings.url_reorder === null) {
                return;
            }
            let params = {order: sortable.toArray()};
            $.extend(true, params,settings.param_reorder, settings.params);
            $.ajax({
                url: settings.url_reorder,
                method: settings.method_reorder,
                data: params,
                success: function(response) {
                    if(response.ok) {
                        update_others();
                    } else {
                        alert(response.error || 'Error al guardar el orden');
                    }
                },
                error: function() {
                    alert('Error al comunicarme con el servidor');
                }
            });
        }

        function _handleDelete($row) {
            if(!settings.delete)
                return;
            var currentValue = getValueById($row.data('categoid'));
            if (confirm('Confirme borrar: ' + currentValue.label )) {
                $row.remove();
                for(var i=0, iLen = catego.values.length; i < iLen; i++)
                    if(catego.values[i].catego_id == currentValue.catego_id) {
                        catego.values.splice(i, 1);
                        break;
                    }
                if(settings.url_delete === null) {
                    return;
                }
                let params = {catego_id: $row.data('categoid'), categoria: categoria};
                $.extend(true, params,settings.param_delete, settings.params);
                $.ajax({
                    url: settings.url_delete,
                    method: settings.method_delete,
                    data: params,
                    success: function(response) {
                        if (response.success) {
                            $row.remove();
                            $(`#taka option[value="${currentValue.catego_id}"]`).remove();
                            update_others();
                        } else {
                            alert(response.error || 'Error al eliminar');
                        }
                    },
                    error: function() {
                        alert('Error communicating with server');
                    }
                });
            }
        }

        function _handleEdit($row) {
            if(!settings.edit)
                return;
            let $cellToEdit = $row.find('[data-categomode="read"]');
            if($cellToEdit.length === 0)
                return;
            var currentValue = getValueById($row.data('categoid'));
            var $editCell = _createEditableCell(currentValue);
            $cellToEdit.replaceWith($editCell);
            _selectApplySelectedClass(".catego_select");
            $editCell.find('.catego_save').on('click', function() {
                var $cell = $row.find('[data-categomode="edit"]');
                var newValue = {
                    label: _strim( $cell.find('input').val() ),
                    activo: $cell.find('select').val()
                };
                if(!newValue.label) {
                    dialoger().alert('Falto el ' + catego.label_singular);
                    return;
                }
                if(_yaExiste(newValue.label, $row.data('categoid'))) {
                    dialoger().alert('Ya existe: ' + newValue.label);
                    return;
                }

                // actualiza array
                var currentValue = getValueById($row.data('categoid'));
                currentValue.label = newValue.label;
                currentValue.activo = newValue.activo;
                // actualiza la tabla
                var $newCell = $(`<td data-categomode="read" ${newValue.activo === 'Inactivo' ? 'class="catego_inactivo"' : ''}>${newValue.label}</td>`);
                $editCell.replaceWith($newCell);
                if(settings.url_update === null) {
                    return;
                }
                let params = {
                    catego_id: currentValue.catego_id,
                    label: newValue.label,
                    activo:  newValue.activo
                };
                $.extend(true, params,settings.param_update, settings.params);
                $.ajax({
                    url: settings.url_update,
                    method: settings.method_update,
                    data: params,
                    success: function(response) {
                        if (response.success) {
                            var $newCell = $(`<td data-categomode="read" ${newValue.activo === 'Inactivo' ? 'class="catego_inactivo"' : ''}>${newValue.label}</td>`);
                            $editCell.replaceWith($newCell);
                            const $option = $(`#taka option[value="${currentValue.catego_id}"]`);
                            if(newValue.activo === 'Inactivo') {
                                $option.remove();
                            } else {
                                $option.text(newValue.label);
                            }
                            update_others();
                        } else {
                            alert(response.error || 'Error updating category');
                            _cancelEdit($editCell, currentValue);
                        }
                    },
                    error: function() {
                        alert('Error communicating with server');
                        _cancelEdit($editCell, currentValue);
                    }
                });
            });

            $editCell.find('.catego_cancel').on('click', function() {
                _cancelEdit($editCell, currentValue);
            });
        }

        function _cancelEdit($editCell, originalValue) {
            var $readCell = $(`<td data-categomode="read" ${originalValue.activo === 'Inactivo' ? 'class="catego_inactivo"' : ''}>${originalValue.label}</td>`);
            $editCell.replaceWith($readCell);
        }

        function _handleAdd($dialog) {
            if(!settings.add)
                return;
            var $input = $dialog.find('input.catego_new_catego');
            var newName = _strim($input.val());
            console.log("-x-x-x ADD", _getZIndex($input[0]));
            if (!newName) {
                dialoger().alert('Falto el ' + catego.label_singular);
                return;
            }
            if(_yaExiste(newName, null)) {
                dialoger().alert('Ya existe: ' + newName);
                return;
            }

            var newValue = {
                catego_id: newName,
                label: newName,
                activo: 'Activo',
            };
            catego.values.push(newValue);
            var $newRow = _createTableRow(newValue);
            $dialog.find('.catego_sortable').prepend($newRow);
            $input.val('');
            if(settings.url_add === null) {
                return;
            }
            let params = {label: newName}
            $.extend(true, params,settings.param_add, settings.params);

            $.ajax({
                url: settings.url_add,
                method: settings.method_add,
                data: params,
                success: function(response) {
                    if (response.success) {
                        var newValue = {
                            catego_id: response.catego_id,
                            label: newName,
                            activo: 'Activo',
                        };
                        var $newRow = _createTableRow(newValue);
                        $dialog.find('.catego_sortable').append($newRow);
                        $input.val('');
                        $('#taka').append($('<option>', {
                            value: response.catego_id,
                            text: newName
                        }));
                        update_others();
                    } else {
                        alert(response.error || 'Error adding category');
                    }
                },
                error: function() {
                    alert('Error communicating with server');
                }
            });
        }

        function _selectApplySelectedClass(selector) {
            $(selector).each(function(){
                var e=$(this);
                if(!e.prop("multiple")) {
                    e.off("change",selectTextAddSelectedClass).on("change",selectTextAddSelectedClass);
                    var select = $(this), optionClass = select.children(":selected").prop("class");
                    select.removeClass(select.data("iaprevoptionclass")).addClass(optionClass).data("iaprevoptionclass", optionClass);
                }
            });
            function selectTextAddSelectedClass() {
                var select = $(this), optionClass = select.children(":selected").prop("class");
                select.removeClass(select.data("iaprevoptionclass")).addClass(optionClass).data("iaprevoptionclass", optionClass);
            }
        }

        function update_local() {
            // esta toma la tabla y la pasa a catego.values
            var newValues = [];
            $('.catego_sortable tr').each(function(index) {
                var $row = $(this);
                newValues.push({
                    activo: $row.find('[data-categomode="read"]').hasClass('catego_inactivo') ? 'Inactivo' : 'Activo',
                    orden: index + 1
                });
            });
            catego.values = newValues;
            // @TODO reemplazar select options
            // @TODO reemplazar hidden values
            // @TODO reemplazar readonly span
            // hace un trigger
            $element.trigger('catego_change', this);
        }

        function manage() {
            var $dialog = _createDialog();
            $('body').append($dialog);
            _initializeSortable($dialog.find('.catego_sortable'));
            // Event handlers
            $dialog.on('click', '.catego_dialog_close', function() {$dialog.remove();});
            $dialog.on('click', '.catego-ButtonDelete', function() {_handleDelete($(this).closest('tr'));});
            $dialog.on('click', '.catego-ButtonEdit', function() {_handleEdit($(this).closest('tr'));});
            $dialog.on('click', '.catego_dialog_add', function() {_handleAdd($dialog);});

            $dialog[0].showModal();
        }
        
        function update() {}

        return {
            manage: manage,
            getValueById: getValueById,
            //   reload: reload,
            //   refresh: refresh,
            //   get_catego: get_catego,
            //   get_values: get_values,
            //   set_catego: set_catego,
            //   set_values: set_values,
            //   me: me
        };
    }
</script>
<script>
    var catego_value = {
        "categoria":"productCat",
        "label_singular":"Uso",
        "label_plural":"Usos del Producto",
        "min_selected":1,
        "max_selected":1,
        "values": [
            {catego_id:"2", label:"label #2","activo":"Activo"},
            {catego_id:"3", label:"label #3", "activo":"Activo"},
            {catego_id:"4", label:"label #4", "activo":"Activo"},
            {catego_id:"5", label:"label #5", "activo":"Activo"},
            {catego_id:"6", label:"label #6", "activo":"Activo"},
            {catego_id:"7", label:"label #7", "activo":"Activo"},
            {catego_id:"8", label:"label #8", "activo":"Activo"},
            {catego_id:"9", label:"label #9", "activo":"Activo"},
            {catego_id:"10", label:"label #10", "activo":"Activo"},

            {catego_id:"12", label:"label #12","activo":"Activo"},
            {catego_id:"13", label:"label #13", "activo":"Activo"},
            {catego_id:"14", label:"label #14", "activo":"Activo"},
            {catego_id:"15", label:"label #15", "activo":"Activo"},
            {catego_id:"16", label:"label #16", "activo":"Activo"},
            {catego_id:"17", label:"label #17", "activo":"Activo"},
            {catego_id:"18", label:"label #18", "activo":"Activo"},
            {catego_id:"19", label:"label #19", "activo":"Activo"},
            {catego_id:"20", label:"label #20", "activo":"Activo"},
        ]
    }
    var a = new Catego_manager("productCat", catego_value, '#taka');
</script>
<script>
    if(false)
        $(document).ready(function() {
            $.ajax({
                url: 'catego_responder.php',
                method: 'POST',
                data: {
                    accion: 'getCatalog',
                    categoria: 'productCat'
                },
                success: function(response) {
                    var catego_value = response;
                    var a = new Catego_manager("productCat", catego_value, '#cat');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching catalog:', error);
                }
            });
        });
</script>
<select id="taka"></select> <button type="button" onclick="a.manage()"><i class="fa-regular fa-shelves"></i> Usos</button>
</body>
</html>