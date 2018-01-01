/*
 * The admin tables javascript
 */

// Document ready
jQuery(function () {

    var $ = jQuery;
    window.amzTables = window.amzTables || {};

    amzTables.baseURL = '../wp-content/plugins/amz-affiliate/';
    amzTables.tablesIds = [];
    amzTables.tables = {};
    amzTables.tableConfig = {
        'paging': false,
        'ordering': false,
        'info': false
    };

    amzTables.setTableId = function () {
        var currentId = 'table_' + Math.random().toString(36).substr(2, 5);
        if (amzTables.tablesIds.indexOf(currentId) > 0) {
            amzTables.setTableId()
        } else {
            amzTables.tablesIds.push(currentId);
            return currentId;
        }
    };

    amzTables.initDataTable = function (currentIdTable) {
        amzTables.tables[currentIdTable] = $('#' + currentIdTable).DataTable(amzTables.tableConfig);
    };

    amzTables.initTableFunctionality = function (currentIdTable) {
        var currentTableBody = $(amzTables.tables[currentIdTable].table().body());
        var currentTableHead = $(amzTables.tables[currentIdTable].table().header());
        var currentTableFoot = $(amzTables.tables[currentIdTable].table().footer());

        currentTableHead.on('click', 'th', function () {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            } else {
                $('th.selected', currentTableHead).removeClass('selected');
                $(this).addClass('selected');
            }
        });

        currentTableHead.on('dblclick', 'th', function () {
            if ($(this).hasClass('edit')) {
                return false;
            } else {
                var value = $(this).html();
                if (value === 'ASIN') {
                    alert('This value can´t modify.');
                    return false;
                }

                $(this).html($('<input>').val(value));
                $('input', $(this)).focus();
                $(this).addClass('edit');
            }
        });
        currentTableHead.on('blur', 'input', function () {
            var value = $(this).val();
            var cellIndex = $(this).parents('th')[0].cellIndex;
            $($('th', currentTableFoot)[cellIndex]).html(value);
            $(this).parents('th').html(value).removeClass('edit');
        });

        currentTableBody.on('click', 'tr', function () {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            } else {
                amzTables.tables[currentIdTable].$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        });

        currentTableBody.on('dblclick', 'td', function () {
            if ($(this).hasClass('edit')) {
                return false;
            } else {
                var value = $(this).html();
                $(this).html($('<textarea cols="40" rows="5">').val(value));
                $('textarea', $(this)).focus();
                $(this).addClass('edit');
            }
        });
        currentTableBody.on('blur', 'textarea', function () {
            var value = $(this).val();
            $(this).parents('td').html(value).removeClass('edit');
            $('#' + currentIdTable).DataTable().destroy();
            amzTables.initDataTable(currentIdTable);
        });
    };

    $('#addNewTable').on('click', function () {
        $.get(amzTables.baseURL + 'templates/basic-table.html').then(function (basicTemplate) {
            var currentTable = $(basicTemplate);
            var currentIdTable = amzTables.setTableId();

            currentTable.find('table').attr('id', currentIdTable);

            $('#tablesContent').prepend(currentTable);

            amzTables.initDataTable(currentIdTable);
            amzTables.initTableFunctionality(currentIdTable);
        })
    });

    amzTables.deleteTable = function (that, tableId) {
        var msg = 'Are you sure do you want to delete this table?';
        if (confirm(msg)) {
            var parentDiv = $(that).closest('.amz-table');
            if (parentDiv.data('state') === 'new') {
                parentDiv.remove();
            } else {
                var parentTable = parentDiv.find('table');
                var currentIdTable = parentTable[0].id;
                var wrapper = $('#' + currentIdTable + '_wrapper');
                parentDiv.find('input[type=button]').attr('disabled', true);
                wrapper.hide();
                parentDiv.find('.loading').show();

                var data = {
                    'action': 'delete_table',
                    'table_id': tableId || parentDiv[0].id
                };

                window.amzCommons.callApi(data, function (response) {
                    window.amzCommons.notice('notice-success', response.message);
                    parentDiv.remove();
                }, function () {
                    parentDiv.find('.loading').hide();
                    wrapper.show();
                    parentDiv.find('input[type=button]').attr('disabled', false);
                });
            }
        }
        return false;
    };

    amzTables.saveTable = function (that, tableId) {
        var parentDiv = $(that).closest('.amz-table');
        var parentTable = parentDiv.find('table');
        var currentIdTable = parentTable[0].id;
        var wrapper = $('#' + currentIdTable + '_wrapper');
        var currentTable = amzTables.tables[currentIdTable];
        var currentTableHeader = currentTable.columns().header();
        var currentTableBody = [];
        var currentTableHead = [];
        var tableName = $('input[name=table_name]', parentDiv).val();

        parentDiv.find('input[type=button]').attr('disabled', true);
        wrapper.hide();
        parentDiv.find('.loading').show();

        currentTable.data().each(function (value) {
            currentTableBody.push(value)
        });

        currentTableHeader.each(function (value) {
            currentTableHead.push(value.innerHTML)
        });

        if (parentDiv[0].id !== '' && !tableId) {
            tableId = parentDiv[0].id
        }

        var data = {
            'action': tableId ? 'update_table' : 'save_table',
            'table_head': currentTableHead,
            'table_body': currentTableBody
        };

        if (tableId) {
            data.table_id = tableId;
        }

        if (tableName !== '') {
            data.table_name = tableName;
        }

        function tableResult(table) {
            $('#' + currentIdTable).DataTable().clear();
            $('#' + currentIdTable).DataTable().rows.add(table.table_body).draw();

            parentDiv.find('.loading').hide();
            wrapper.show();
            parentDiv.find('input[type=button]').attr('disabled', false);
        }

        function setTableId(itemId) {
            $(parentDiv).find('.amz-number').html('[amztable_' + itemId + ']');

            parentDiv.attr('id', itemId);
            if (parentDiv.data('state') === 'new') {
                $('input', parentDiv[0]).filter(function () {
                    return this.value === 'Save Table'
                }).val('Update Table');
            }
        }

        window.amzCommons.callApi(data, function (response) {
            tableResult(response.data);
            $(parentDiv).find('.amz-date').html('Last update: ' + response.data.table_time);
            if (response.data.itemId) {
                setTableId(response.data.itemId);
            }
            window.amzCommons.notice('notice-success', response.message);
        }, function () {
            var dataToReDrawTable = {
                table_head: currentTableHead,
                table_body: currentTableBody
            };
            tableResult(dataToReDrawTable);
        });
    };

    amzTables.addRow = function (that) {
        var parentTable = $(that).closest('.amz-table').find('table').dataTable();
        var countColumns = parentTable.fnSettings().aoColumns.length;
        var currentTable = amzTables.tables[parentTable[0].id];
        var newRows = [];
        newRows.push('ASIN');

        for (var i = 1; i < countColumns; i++) {
            newRows.push('')
        }

        currentTable.row.add(newRows).draw(false);
    };

    amzTables.deleteRow = function (that) {
        var parentTable = $(that).closest('.amz-table').find('table');

        if (amzTables.tables[parentTable[0].id].row('.selected')[0].length === 0) {
            alert('You need select one row to delete.');
        }
        amzTables.tables[parentTable[0].id].row('.selected').remove().draw(false);
    };

    amzTables.deleteColumn = function (that) {
        var parentTable = $(that).closest('.amz-table').find('table');
        var parentTableId = parentTable[0].id;
        var selectedTH = $('#' + parentTableId + ' thead th.selected');

        if (selectedTH.length === 0) {
            alert('You need select one column to delete.');
            return false;
        }

        if (selectedTH[0].innerText === 'ASIN') {
            alert('This column can´t delete.');
            return false;
        }

        $('#' + parentTableId).DataTable().destroy();

        var selectedTF = $('#' + parentTableId + ' tfoot th')[selectedTH[0].cellIndex];
        var selectedTR = $('#' + parentTableId + ' tbody tr');
        for (var i = 0; i < selectedTR.length; i++) {
            $('td', selectedTR[i])[selectedTH[0].cellIndex].remove();
        }
        selectedTH.remove();
        selectedTF.remove();

        amzTables.initDataTable(parentTableId);
    };

    amzTables.addColumn = function (that) {
        var parentTable = $(that).closest('.amz-table').find('table');
        var parentTableId = parentTable[0].id;
        var selectedTH = $('#' + parentTableId + ' thead th.selected');
        if (selectedTH.length === 0) {
            alert('You need select one column to add another on right.');
            return false;
        }
        var selectedTF = $('#' + parentTableId + ' tfoot th')[selectedTH[0].cellIndex];

        var selectedTR = $('#' + parentTableId + ' tbody tr');
        selectedTH.after("<th></th>");
        $(selectedTF).after("<th></th>");
        for (var i = 0; i < selectedTR.length; i++) {
            $($('td', selectedTR[i])[selectedTH[0].cellIndex]).after("<td></td>");
        }

        $('#' + parentTableId).DataTable().destroy();
        amzTables.initDataTable(parentTableId);
    };

    amzTables.showHide = function(that){
        var childTable = $(that).closest('.amz-table');
        var childTableBody = $(that).closest('.amz-table').find('.amz-table-body');
        var childTableFooter = $(that).closest('.amz-table').find('.amz-table-foot');
        var isVisible = childTableBody.is(':visible');

        if (isVisible === true) {
            $(childTable).find('.show-hide').val('Show');
            childTableBody.fadeOut();
            childTableFooter.fadeOut();
        } else {
            $(childTable).find('.show-hide').val('Hide');
            childTableBody.fadeIn();
            childTableFooter.fadeIn();
        }
    };

    $('table').each(function (index, value) {
        var currentTable = $(value);
        var currentIdTable = amzTables.setTableId();

        currentTable[0].id = currentIdTable;

        amzTables.initDataTable(currentIdTable);
        amzTables.initTableFunctionality(currentIdTable);
    });

});
