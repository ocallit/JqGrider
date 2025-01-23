// noinspection JSUnusedGlobalSymbols
// cm_iajqgridbodega=[{name: "iacsel",index:"iacsel", label:"&Sigma;" ,colmenu: false ,template:iacJqGridSelect().iacselColTemplate,ctrl:true,ctrlId:'#iactoolbarselect',ctrlButtonset:true,sumCols:true}

var jqGridTemplates = {

    darkColor: '#87b6d9', // `rgba(135, 182, 217, 1)`
    lightColor: '#ebf4fd',

    tinyint: {
        align: 'right',
        width: 40,
        sorttype: 'integer',
        formatter: 'integer',
        formatoptions: {thousandsSeparator: ',', defaultValue: '0'},
        cellAttributes: function(rowId, cellValue, rawObject, cm, rdata) {return cellValue < 0 ? 'class="negativo"' : '';},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules:{integer:true, maxValue: 127, minValue: -128}
    },

    tinyint_unsigned: {
        align: 'right',
        width: 40,

        sorttype: 'integer',
        formatter: 'integer',
        formatoptions: {thousandsSeparator: ',', defaultValue: '0'},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules:{integer:true, maxValue: 255, minValue: 0}
    },

    smallint: {
        align: 'right',
        width: 90,
        firstsortorder:'desc',
        sorttype: 'integer',
        formatter: 'integer',
        formatoptions: {thousandsSeparator: ',', defaultValue: '0'},
        cellAttributes: function(rowId, cellValue, rawObject, cm, rdata) {return cellValue < 0 ? 'class="negativo"' : '';},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules:{integer:true, maxValue: 32767, minValue: -32768}
    },

    smallint_unsigned: {
        align: 'right',
        width: 90,
        firstsortorder:'desc',
        sorttype: 'integer',
        formatter: 'integer',
        formatoptions: {thousandsSeparator: ',', defaultValue: '0'},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules:{integer:true, maxValue: 65535, minValue: 0}
    },

    mediumint: {
        align: 'right',
        width: 120,
        sorttype: 'integer',
        firstsortorder:'desc',
        formatter: 'integer',
        formatoptions: {thousandsSeparator: ',', defaultValue: '0'},
        cellAttributes: function(rowId, cellValue, rawObject, cm, rdata) {return cellValue < 0 ? 'class="negativo"' : '';},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules:{integer:true, maxValue: 8388607, minValue: -8388608}
    },

    mediumint_unsigned: {
        align: 'right',
        width: 120,
        sorttype: 'integer',
        firstsortorder:'desc',
        formatter: 'integer',
        formatoptions: {thousandsSeparator: ',', defaultValue: '0'},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules:{integer:true, maxValue: 16777215, minValue: 0}
    },

    int: {
        align: 'right',
        width: 120,
        sorttype: 'integer',
        firstsortorder:'desc',
        formatter: 'integer',
        formatoptions: {thousandsSeparator: ',', defaultValue: '0'},
        cellAttributes: function(rowId, cellValue, rawObject, cm, rdata) {return cellValue < 0 ? 'class="negativo"' : '';},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules:{integer:true, maxValue: 2147483647, minValue: -2147483648}
    },

    int_unsigned: {
        align: 'right',
        width: 120,
        sorttype: 'integer',
        firstsortorder:'desc',
        formatter: 'integer',
        formatoptions: {thousandsSeparator: ',', defaultValue: '0'},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules:{integer:true, maxValue: 4294967295, minValue: 0}
    },

    bigint: {
        align: 'right',
        width: 120,
        sorttype: 'integer',
        firstsortorder:'desc',
        formatter: 'integer',
        formatoptions: {thousandsSeparator: ',', defaultValue: '0'},
        cellAttributes: function(rowId, cellValue, rawObject, cm, rdata) {return cellValue < 0 ? 'class="negativo"' : '';},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules:{integer:true, maxValue: 9223372036854775807, minValue: -9223372036854775808}
    },

    bigint_unsigned: {
        align: 'right',
        width: 120,
        sorttype: 'integer',
        firstsortorder:'desc',
        formatter: 'integer',
        formatoptions: {thousandsSeparator: ',', defaultValue: '0'},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules:{integer:true, maxValue: 18446744073709551615, minValue: 0}
    },

    // Decimal Types
    decimal_10_2: {
        align: 'right',
        width: 140,
        sorttype: 'number',
        firstsortorder:'desc',
        formatter: 'number',
        formatoptions: {decimalSeparator: '.', thousandsSeparator: ',', decimalPlaces: 2, defaultValue: '0.00', prefix: "", sufix: ""},
        cellAttributes: function(rowId, cellValue, rawObject, cm, rdata) {return cellValue < 0 ? 'class="negativo"' : '';},
        searchoptions: { sopt: ['ge','eq','le','ne','gt','lt'] },
        editrules: {number:true,maxValue: 99999999.99, minValue: -99999999.99, step: 0.01}
    },

    // Date & Time Types
    date: {
        align: 'center',
        width:125,
        sorttype: 'date',
        formatter: 'date',
        formatoptions: {srcformat: 'Y-m-d', newformat: 'j/M/y'},
        stype: 'text',
        searchoptions: {
            sopt: ['eq', 'ne', 'lt', 'le', 'gt', 'ge'],
            dataInit: function(element) {
                var gridId = $(this).attr('id');
                $(element).datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeYear: true,
                    changeMonth: true,
                    showButtonPanel: true,
                    showOn: 'focus',
                    onSelect: function(dateText, inst) {$("#" + gridId)[0].triggerToolbar();}
                });
            }
        },
        editrules:{date:true}
    },

    datetime: {
        align: 'center',
        width:180,
        sorttype: 'date',
        formatter: 'date',
        formatoptions: {srcformat: 'Y-m-d H:i:s', newformat: 'j/M/y H:i:s'},
        searchoptions: {sopt: ['eq','ne','lt','le','gt','ge'],
            dataInit: function(element) {
                $(element).datetimepicker({
                    dateFormat: 'yy-mm-dd',
                    timeFormat: 'HH:mm:ss',
                    changeYear: true,
                    changeMonth: true,
                    showButtonPanel: true
                });
            }
        }
    },

    // String Types
    varchar: {
        align: 'left',
        sorttype: 'text',
        searchoptions: {sopt: ['cn','nc','bw','bn','ew','en','eq','ne']}
    },

    // Enum Type (Yes/No example)
    enum_yesno: {
        align: 'center',
        stype: 'select',
        sorttype: 'text',
        formatter: 'select',
        edittype: 'select',
        editoptions: {
            value: 'Yes:Yes;No:No'
        },
        searchoptions: {
            sopt: ['eq','ne'],
            value: ':;Yes:Yes;No:No'
        }
    },

    // Set Type (Colors example)
    set_colors: {
        align: 'left',
        sorttype: 'text',
        formatter: 'select',
        edittype: 'select',
        editoptions: {
            multiple: true,
            value: 'Red:Red;Green:Green;Blue:Blue',
        },
        searchoptions: {
            sopt: ['cn','nc','bw','bn','ew','en','eq','ne']
        }
    }
};

function getTemplate(mysqlType, options) {
    var template = {};

    var processedType = mysqlType.replace(/([^(]+)(\([^)]*\))?/i, function(match, type, params) {
        return type.toLowerCase() + (params || '');
    });

    var varcharMatch = processedType.match(/varchar\s*\((\d+)\)/i);
    if (varcharMatch) {
        template = $.extend(true, {}, jqGridTemplates.varchar);
        template.editrules = {
            maxlength: parseInt(varcharMatch[1], 10)
        };
        return $.extend(true, template, options || {});
    }

    var charMatch = mysqlType.match(/char\s*\((\d+)\)/i);
    if (charMatch) {
        template = $.extend(true, {}, jqGridTemplates.varchar);
        template.editrules = {
            maxlength: parseInt(charMatch[1], 10)
        };
        return $.extend(true, template, options || {});
    }

    var enumMatch = processedType.match(/enum\s*\((.*?)\)/i);
    if (enumMatch) {
        template = $.extend(true, {}, jqGridTemplates.enum_yesno);
        var valuesObj = {};
        enumMatch[1].split(',')
            .forEach(function(v) {
                var val = v.trim().replace(/^['"]|['"]$/g, '');
                valuesObj[val] = val;
            });

        template.editoptions = {
            value: valuesObj
        };
        var searchObj = { '': '' };
        $.extend(searchObj, valuesObj);
        template.searchoptions.value = searchObj;

        return $.extend(true, template, options || {});
    }

    var setMatch = processedType.match(/set\s*\((.*?)\)/i);
    if (setMatch) {
        template = $.extend(true, {}, jqGridTemplates.set_colors);
        var valuesObj = {};
        setMatch[1].split(',')
            .forEach(function(v) {
                var val = v.trim().replace(/^['"]|['"]$/g, '');
                valuesObj[val] = val;
            });

        template.editoptions = {
            multiple: true,
            value: valuesObj,
            separator: ','
        };
        var searchObj = { '': '' };
        $.extend(searchObj, valuesObj);
        template.searchoptions.value = searchObj;
        return $.extend(true, template, options || {});
    }

    var decimalMatch = mysqlType.match(/decimal\s*\((.*?)\)/i);
    if(decimalMatch) {
        template = $.extend(true, {}, jqGridTemplates.decimal_10_2);
        if (decimalMatch) {
            var nums = decimalMatch[1].split(",")
            var precision = parseInt(nums[0] || 10, 10);
            var scale = parseInt(nums[1] || 0, 10);
            if(isNaN(precision) || precision < 1) precision = 10;
            if(isNaN(scale) || scale < 0) scale = 2;
            var maxDigits = precision - scale;

            template.formatoptions.decimalPlaces = scale;
            template.formatoptions.defaultValue = '0.' + '0'.repeat(scale);

            // Calculate max/min values based on precision and scale
            var maxInt = '9'.repeat(maxDigits);
            var maxDec = '9'.repeat(scale);
            var maxValue = parseFloat(maxInt + '.' + maxDec);

            template.editrules = {
                maxValue: maxValue,
                minValue: -maxValue,
                step: Math.pow(0.1, scale)
            };
        }
        return $.extend(true, template, options || {});
    }

    if(jqGridTemplates[processedType])
        template = $.extend(true, {}, jqGridTemplates[processedType]);
    else if(jqGridTemplates[mysqlType])
        template = $.extend(true, {}, jqGridTemplates[mysqlType]);


    return $.extend(true, template, options || {});
}

function colmoAddSelect(keyValue) {
    return {
        formatter: 'select', edittype: 'select',stype: "select",
        editoptions: {value: keyValue},
        searchoptions: {sopt: ['eq','ne'], value: {'':'', ...keyValue}}
    }
}

var jqGUtil = {
    version: '1.0.0',

    addSearch: function (data, selectColumns) {
    var colLength = selectColumns.length;
    var selectOptions = {};
    var unique = {};
    for(var iData=0, len=data.length; iData < len; ++iData) {
        var d = data[iData];
        for(var iCol=0; iCol < colLength; ++iCol) {
            var col = selectColumns[iCol];
            if(!unique.hasOwnProperty(col)) {
                unique[col] = {};
                selectOptions[col] = "";
            }
            if(!unique[col].hasOwnProperty(d[col])) {
                unique[d[col]] = true;
                selectOptions[col] += col + ":" + col + ";";
            }
        }
    }
    },

     addSelectSearch: function(gridId, columnName, options) {
        options = options || {};
        var $grid = $('#' + gridId);

        var colValues = $grid.jqGrid('getCol', columnName);

        var uniqueValues = Array.from(new Set(colValues.filter(function(val) {
            return val != null && val !== '';
        })));

        uniqueValues.sort();

        var selectOptions = ':' + (options.allText || 'All') + ';' +
            uniqueValues.map(function(val) {
                return val + ':' + val;
            }).join(';');

        var colModel = $grid.jqGrid('getGridParam', 'colModel');

        for (var i = 0; i < colModel.length; i++) {
            if (colModel[i].name === columnName) {

                var searchoptions = $.extend({}, colModel[i].searchoptions || {}, {
                    sopt: ['eq', 'ne'],  // Set search operators to equals/not equals
                    value: selectOptions,
                    clearSearch: true
                });

                if (options.searchOptions) {
                    $.extend(searchoptions, options.searchOptions);
                }

                $grid.jqGrid('setColProp', columnName, {
                    stype: 'select',
                    searchoptions: searchoptions
                });

                break;
            }
        }

        // Trigger search toolbar reload if it exists
        if ($grid[0].ftoolbar) {
            var currentToolbarOptions = $grid.jqGrid('getGridParam', 'filterToolbar') || {}
            $grid.jqGrid('destroyFilterToolbar');
            var toolbarOptions = $.extend({},
                {
                    stringResult: true,
                    searchOnEnter: false,
                    defaultSearch: 'cn'
                },
                currentToolbarOptions,
                options.toolbarOptions || {}
            );
            $grid.jqGrid('filterToolbar', toolbarOptions);
        }
    },

    navButtonExportCsv: function(gridId, pagerId) {
        var $grid = $("#" + gridId);
        var datatype = $grid.jqGrid('getGridParam', 'datatype');
        var loadonce = $grid.jqGrid('getGridParam', 'loadonce');
        if (datatype !== 'local' && !loadonce) {
            return;
        }
        var exportOptions = {
        caption: "CSV",
        buttonicon: "ui-icon ui-icon-arrowthickstop-1-s",
        title: "Exporta UNICAMENTE LO VISIBLE a Excel simple, plano, sin formato, en *.csv",
        onClickButton: function() {
            var grid = $("#" + gridId);
            grid.jqGrid('exportToCsv', {
                separator: ",",
                separatorReplace: "",
                quote: '"',
                escquote: '"',
                newLine: "\r\n",
                replaceNewLine: " ",
                includeCaption: true,
                includeLabels: true,
                includeGroupHeader: true,
                includeFooter: true,
                includeHeader: true,
                fileName: gridId + "_export.csv",
                loadIndicator: true,
                onBeforeExport: function(csvData) {
                    // Get only visible rows and columns
                    var visibleData = [];
                    var visibleCols = [];
                    var colModel = grid.jqGrid('getGridParam', 'colModel');

                    // Get visible columns
                    colModel.forEach(function(col) {
                        if (!col.hidden && col.name !== 'rn' && col.name !== 'cb') {
                            visibleCols.push(col.name);
                        }
                    });

                    // Get visible rows and filter columns
                    var rows = csvData.split("\n");
                    rows.forEach(function(row, idx) {
                        if (row) {
                            var cols = row.split(",");
                            var newRow = [];
                            visibleCols.forEach(function(colName, colIdx) {
                                newRow.push(cols[colIdx]);
                            });
                            visibleData.push(newRow.join(","));
                        }
                    });

                    return visibleData.join("\n");
                }
            });
        },
        position: "last",
        cursor: "pointer",
        id: "export_" + gridId
    };
        if(typeof pagerId === 'undefined')
            pagerId = gridId + "Pager";
        $grid.jqGrid('navButtonAdd', "#" + pagerId, exportOptions);
    },

    footerMaxAvgMin: function(ev) {
        var $grid = $(ev.target);
        var userData = {};
        var colModel = $grid.jqGrid('getGridParam', 'colModel')
        colModel.forEach(function(col) {
            if(col.footerValue)
                userData[col.name] = $grid.jqGrid('getCol', col.name, false, col.footerValue.toLowerCase());
        });
        $grid.jqGrid('footerData', 'set', {row1:footerData, row2:footerData});
    },

    footerColumnTotal: function(ev) {
        // este raro https://stackoverflow.com/questions/13697523/how-to-create-two-footer-rows-in-jqgrid
        // userDataOnFooter option you would find that in case of usage datatype: "json" it's only one line of code:
        // https://stackoverflow.com/a/13703037/315935 https://stackoverflow.com/questions/19767339/how-to-add-two-footer-row-in-jqgrid-using-userdata
        // if(ts.p.userDataOnFooter) { self.jqGrid("footerData","set",ts.p.userData,true); }
        /*
        @usage in grid options: footerrow: true,
               in colModel: colModel: [ {name: 'qty', footerValue: 'sum'}, {name: 'price', footerValue: 'max'} ]
               in $grid.off('jqGridAfterLoadComplete', jqGUtil.footerColumnTotal).on('jqGridAfterLoadComplete', jqGUtil.footerColumnTotal)
        */
        var $grid = $(ev.target);
        var footerData = {};
        var colModel = $grid.jqGrid('getGridParam', 'colModel')
        colModel.forEach(function(col) {
            if(col.footerValue) {
                footerData[col.name] = $grid.jqGrid('getCol', col.name, false, col.footerValue.toLowerCase());
            }
        });
        $grid.jqGrid('footerData', 'set', footerData, false, 0);
        $grid.jqGrid('footerData', 'set', footerData, false, 1);
    },

    footerFormatedUserData: function(ev) {
        var $grid = $(ev.target);
        var userData = $grid.jqGrid('getGridParam', 'userData');
        if(userData) {
            if(!Array.isArray(userData)) {
                $grid.jqGrid('footerData', 'set', userData, true, 0);
                return;
            }
            let footerTable = $(".ui-jqgrid-ftable", $("#gview_elGridGrid")).find("TBODY");
            while(footerTable.children().length > 1)
                footerTable.children().first().remove();
            let setFirst = true;
            for(let u of userData) {
                if(setFirst) {
                    $grid.jqGrid('footerData', 'set', u, true, 0);
                    setFirst = false;
                } else {
                    $grid.jqGrid('footerData', 'addrow', u, true, 0);
                }
            }
        }
    }

}
