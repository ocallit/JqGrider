function LookUpManager(categoria, element, options, catego) {
    if (!(this instanceof LookUpManager))
        return new LookUpManager(categoria, element, options, catego);

    var $element = $(element);
    var sortable;

    var settings = {
        reorder: true,
        add: true,
        edit:true,
        delete: true,

        select: null,
        display: null,
        hidden: null,

        url: "catego_responder.php",
        method: 'POST',
        url_reorder: "catego_responder.php",
        url_delete: "catego_responder.php",
        url_update: "catego_responder.php",
        url_add: "catego_responder.php",
        param_reorder:{accion:'reorder'},
        param_delete: {accion:'delete'},
        param_update: {accion:'update'},
        param_add: {accion:'add'},
        method_reorder: 'POST',
        method_delete: 'POST',
        method_update: 'POST',
        method_add: 'POST',
        params: {categoria: categoria || ''},}
    $.extend(true, settings, typeof options === 'undefined' ? {} : options);

    function getValueById(id) {
        for(var c of catego.values)
            if(c.id === id)
                return c;
        return null;
    }

    function _strim(s) { return s === null ? '' : s.replace(/\s(\s+)/gm, ' ').trim(); }
    function _yaExiste(label, id) {
        for(var c of catego.values)
            if(c.label === label && (c.id !== id || id === null))
                return true;
        return false;
    }

    function _createTableRow(value) {
        let $tr = $(`<TR data-categoid="${value.id}">`).data('catego', value).data('categoid', value.id).data("id", value.id);
        if(settings.reorder) {
            $tr.append($(`<td data-categoid="${value.id}" class="catego_reorder"><i data-categoid="${value.id}" class="fas fa-arrows-alt handle catego-sortable-handle"></i></td>`) );
        }

        if(settings.edit || settings.delete) {
            let tdActions = '<td class="catego_action">';
            if(settings.edit)
                tdActions += '<div><i class="fa-solid fa-pencil fa-lg catego-ButtonEdit catego-pointer catego-Button catego-ButtonOff" style="color: #0000ff;"></i><p class="catego-acota">Editar</p></div>';
            if(settings.delete)
                tdActions += `<div data-categoid="${value.id}"><i data-categoid="${value.id}" class="fa-regular fa-trash-can fa-lg catego-ButtonDelete catego-pointer catego-Button catego-ButtonOn" style="color: #800040;"></i><p class="catego-acota">Borrar</p></div>`;
            $tr.append(tdActions);
        }

        $tr.append($(`<td class="catego_cell ${value.activo === 'Inactivo' ? ' catego_inactivo' : ''}" data-categomode="read">${value.label}</td>`))

        return $tr;
    }

    function _createEditableCell(value) {
        if(value === null)
            value = '';

        return $(`
            <td data-categomode="edit">
                <input type="text" maxlength="32" style="width:20em" class="catego_edit_input" required value="${value.label}">
                <select class="catego_select">
                    <option ${value.activo === 'Activo' ? 'selected' : ''}>Activo</option>
                    <option ${value.activo === 'Inactivo' ? 'selected' : ''} class="catego_rojo">Inactivo</option>
                </select>
                |
                <button type="button" class="catego_save catego-pointer"><i class="fa-regular fa-floppy-disk" style="color:darkgreen"></i></button>
                <button type="button" class="catego_cancel catego-pointer"><i class="fa-solid fa-ban" style="color:red"></i></button>
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
                <input type="text" maxlength="32" class="catego_new_catego" style="width:20em"  required placeholder="${catego.label}">
                <button type="button" class="catego_dialog_add"><i class="fa-solid fa-circle-plus fa-lg" style="color: #008800;"></i><span class="catego-acota">Agregar</span></button>
              </div>
                ` :
            "";
        let reorderHelp = settings.reorder ?
            `<div style="font-size:0.8em;color:gray;text-align:center;font-style:italic;font-family:Courier New, Courier, monospace;
                display:flex;flex-direction: row;flex-wrap: wrap;justify-content: space-between;align-items: center;">
                <div><i style="color:black" class="fas fa-arrows-alt handle"></i> Arrastra reordena.</div>
                <div><button type="button" class="catego_sort_alpha" title="Ordenar alfabéticamente" style="cursor: pointer"><i class="fas fa-sort-alpha-down"></i></button></div>
            </div>`
            : "";

        var $dialog = $(`
            <dialog class="catego_dialog" style="z-index:1000;resize: both;min-width: 200px;min-height: 200px;">
                <div class="catego_dialog_title" style="cursor: grab;user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">
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

    function _handleAlphaSort() {
        const $tbody = $('.catego_sortable');

        // Check if any row is being edited
        if($tbody.find('[data-categomode="edit"]').length > 0) {
            showErrorDialog('Por favor termine de editar antes de ordenar');
            return;
        }

        const rows = $tbody.find('tr').get();
        rows.sort((a, b) => {
            const textA = $(a).find('[data-categomode="read"]').text().toLowerCase();
            const textB = $(b).find('[data-categomode="read"]').text().toLowerCase();
            return textA.localeCompare(textB);
        });

        $.each(rows, (index, row) => {
            $tbody.append(row);
        });

        _saveOrder();
    }

    function _saveOrder() {
        if(!settings.reorder)
            return;
        const newOrder = [];
        console.log("SORTABLETOARRAY", sortable.toArray());
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
            dataType: 'json',
            data: params,
            success: function(response) {
                if(response.success) {
                    update_others();
                } else {
                    showErrorDialog(response.error || 'Error al guardar el orden');
                }
            },
            error: function() {
                showErrorDialog('Error al comunicarme con el servidor');
            }
        });
    }

    function _handleDelete($row) {
        if(!settings.delete)
            return;
        var currentValue = getValueById($row.data('categoid'));
        if (confirm('Confirme borrar: ' + currentValue.label )) {

            for(var i=0, iLen = catego.values.length; i < iLen; i++)
                if(catego.values[i].id === currentValue.id) {
                    catego.values.splice(i, 1);
                    break;
                }
            if(settings.url_delete === null) {
                $row.remove();
                return;
            }
            let params = {id: currentValue.id};
            $.extend(true, params,settings.param_delete, settings.params);
            $.ajax({
                url: settings.url_delete,
                method: settings.method_delete,
                dataType: 'json',
                data: params,
                success: function(response) {
                    if (response.success) {
                        $row.remove();
                        $(`#taka option[value="${currentValue.id}"]`).remove();
                        update_others();
                    } else {
                        showErrorDialog(response.error || 'Error al eliminar');
                    }
                },
                error: function() {
                    showErrorDialog('Error communicating with server');
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

        function saveEdit() {
            var $cell = $row.find('[data-categomode="edit"]');
            var newValue = {
                label: _strim($cell.find('input').val()),
                activo: $cell.find('select').val()
            };
            if(!newValue.label) {
                showErrorDialog('Falto el ' + catego.label);
                return;
            }
            if(_yaExiste(newValue.label, $row.data('categoid'))) {
                alert('Ya existe: ' + newValue.label);
                return;
            }

            // actualiza array
            var currentValue = getValueById($row.data('categoid'));
            currentValue.label = newValue.label;
            currentValue.activo = newValue.activo;

            if(settings.url_update === null) {
                return;
            }
            let params = {
                id: currentValue.id,
                label: newValue.label,
                activo: newValue.activo
            };
            $.extend(true, params,settings.param_update, settings.params);
            $.ajax({
                url: settings.url_update,
                method: settings.method_update,
                dataType: 'json',
                data: params,
                success: function(response) {
                    if (response.success) {
                        var $newCell = $(`<td data-categomode="read" ${newValue.activo === 'Inactivo' ? 'class="catego_inactivo"' : ''}>${newValue.label}</td>`);
                        $editCell.replaceWith($newCell);
                        const $option = $(`#taka option[value="${currentValue.id}"]`);
                        if(newValue.activo === 'Inactivo') {
                            $option.remove();
                        } else {
                            $option.text(newValue.label);
                        }
                        update_others();
                    } else {
                        showErrorDialog(response.error || 'Error updating category');
                        _cancelEdit($editCell, currentValue);
                    }
                },
                error: function() {
                    showErrorDialog('Error communicating with server');
                    _cancelEdit($editCell, currentValue);
                }
            });
        }

        $editCell.find('.catego_save').on('click', saveEdit);
        $editCell.find('input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                saveEdit();
            }
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
        if (!newName) {
            showErrorDialog('Falto el ' + catego.label);
            return;
        }
        if(_yaExiste(newName, null)) {
            showErrorDialog('Ya esta registrado: ' + newName);
            return;
        }

        var newValue = {
            id: newName,
            label: newName,
            activo: 'Activo',
        };


        if(settings.url_add === null) {
            catego.values.push(newValue);
            var $newRow = _createTableRow(newValue);
            $dialog.find('.catego_sortable').prepend($newRow);
            $input.val('');
            return;
        }

        let params = {label: newName}
        $.extend(true, params,settings.param_add, settings.params);

        $.ajax({
            url: settings.url_add,
            method: settings.method_add,
            dataType: 'json',
            data: params,
            success: function(response) {
                if (response.success) {
                    var newValue = {
                        id: response.id,
                        label: newName,
                        activo: 'Activo',
                    };
                    catego.values.push(newValue);
                    var $newRow = _createTableRow(newValue);
                    $dialog.find('.catego_sortable').append($newRow);
                    $input.val('');
                    $('#taka').append($('<option>', {
                        value: response.id,
                        text: newName
                    }));
                    update_others();
                } else {
                    showErrorDialog(response.error || 'Error al agregar, intente mas tarde');
                }
            },
            error: function() {
                showErrorDialog('Error communicating with server');
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
    function update() {}
    function update_others() {}

    function manage() {
        var $dialog = _createDialog();
        $('body').append($dialog);
        _initializeSortable($dialog.find('.catego_sortable'));

         $dialog.on('click', '.catego_dialog_close', function() {
             $dialog.off('click', '.catego_dialog_close')
                 .off('click', '.catego-ButtonDelete')
                 .off('click', '.catego-ButtonEdit')
                 .off('click', '.catego_dialog_add')
                 .off('click', '.catego_sort_alpha')
                 .off('keypress', '.catego_new_catego')
                 .off('mousedown', '.catego_dialog_title')
                 .remove();
         });
        $dialog.on('click', '.catego-ButtonDelete', function() {_handleDelete($(this).closest('tr'));});
        $dialog.on('click', '.catego-ButtonEdit', function() {_handleEdit($(this).closest('tr'));});
        $dialog.on('click', '.catego_dialog_add', function() {_handleAdd($dialog);});
        $dialog.on('click', '.catego_sort_alpha', function() {_handleAlphaSort();});
        $dialog.on('keypress', '.catego_new_catego', function(e) {
            if (e.which === 13) { // Enter key code
                _handleAdd($dialog);
            }
        });
        $dialog.on('mousedown', '.catego_dialog_title', function(e) {
            $(this).css('cursor', 'grabbing');
            const $dialogElement = $(this).closest('.catego_dialog');
            const startX = e.pageX // - $dialogElement[0].offsetLeft;
            const startY = e.pageY // - $dialogElement[0].offsetTop;

            function mouseMoveHandler(e) {
                $dialogElement[0].style.left = `${e.pageX - startX}px`;
                $dialogElement[0].style.top = `${e.pageY - startY}px`;
            }

            function mouseUpHandler() {
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
                $('.catego_dialog_title').css('cursor', 'grab');
            }

            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('mouseup', mouseUpHandler);
        });
        $dialog[0].showModal();
    }

    function close() {
        $dialog.off('click', '.catego_dialog_close')
            .off('click', '.catego-ButtonDelete')
            .off('click', '.catego-ButtonEdit')
            .off('click', '.catego_dialog_add')
            .off('click', '.catego_sort_alpha')
            .off('keypress', '.catego_new_catego')
            .off('mousedown', '.catego_dialog_title')
            .remove();
    }
    function fetchData() {
        $.ajax({
            url: settings.url,
            method: settings.method,
            dataType: 'json',
            data: { accion: 'list', categoria: categoria },
            success: function (response) {
                if (response.success) {
                    catego = response;
                } else {
                    showErrorDialog(response.error || 'Error al comunicarme con el servidor');
                }
            },
            error: function (xhr, status, error) {
                showErrorDialog('Error al comunicarme con el servidor: ' + error);
            }
        });
    }

    function showErrorDialog(message) {
        var $dialog = $('<dialog class="catego_dialog"><div class="catego_dialog_title">Error</div><div class="catego_dialog_content">' + message + '</div></dialog>');
        $('body').append($dialog);
        $dialog[0].showModal();
        $dialog.on('click', function () {
            $dialog.remove();
        });
    }

    if(typeof catego === 'undefined') {
        fetchData();
    }

    return {
        manage: manage,
        close: close,
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
