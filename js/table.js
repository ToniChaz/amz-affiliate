/*
 * The admin tables javascript
 */

// Document ready
jQuery(function () {

    const $ = jQuery;
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
        let currentId = 'table_' + Math.random().toString(36).substr(2, 5);
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
        let currentTableBody = $(amzTables.tables[currentIdTable].table().body());
        let currentTableHead = $(amzTables.tables[currentIdTable].table().header());
        let currentTableFoot = $(amzTables.tables[currentIdTable].table().footer());

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
                let value = $(this).html();
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
            let value = $(this).val();
            let cellIndex = $(this).parents('th')[0].cellIndex;
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
                let value = $(this).html();
                $(this).html($('<textarea cols="40" rows="5">').val(value));
                $('textarea', $(this)).focus();
                $(this).addClass('edit');
            }
        });
        currentTableBody.on('blur', 'textarea', function () {
            let value = $(this).val();
            let parentTD = $(this).parents('td');
            parentTD.html(value).removeClass('edit');
            $('#' + currentIdTable).DataTable().destroy();
            amzTables.initDataTable(currentIdTable);
            if ( parentTD.index() === 0){
                amzTables.saveTable(parentTD)
            }
        });
    };

    $('#addNewTable').on('click', function () {
        $.get(amzTables.baseURL + 'templates/basic-table.html').then(function (basicTemplate) {
            let currentTable = $(basicTemplate);
            let currentIdTable = amzTables.setTableId();

            currentTable.find('table').attr('id', currentIdTable);

            $('#tablesContent').prepend(currentTable);

            amzTables.initDataTable(currentIdTable);
            amzTables.initTableFunctionality(currentIdTable);
        })
    });

    amzTables.deleteTable = function (that, tableId) {
        const msg = 'Are you sure do you want to delete this table?';
        if (confirm(msg)) {
            let parentDiv = $(that).closest('.amz-table');
            if (parentDiv.data('state') === 'new') {
                parentDiv.remove();
            } else {
                let parentTable = parentDiv.find('table');
                let currentIdTable = parentTable[0].id;
                let wrapper = $('#' + currentIdTable + '_wrapper');
                parentDiv.find('input[type=button]').attr('disabled', true);
                wrapper.hide();
                parentDiv.find('.loading').show();

                let data = {
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
        let parentDiv = $(that).closest('.amz-table');
        let parentTable = parentDiv.find('table');
        let currentIdTable = parentTable[0].id;
        let wrapper = $('#' + currentIdTable + '_wrapper');
        let currentTable = amzTables.tables[currentIdTable];
        let currentTableHeader = currentTable.columns().header();
        let currentTableBody = [];
        let currentTableHead = [];
        let tableName = $('input[name=table_name]', parentDiv).val();

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

        let data = {
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
                    return this.value === 'Save'
                }).val('Update');
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
            let dataToReDrawTable = {
                table_head: currentTableHead,
                table_body: currentTableBody
            };
            tableResult(dataToReDrawTable);
        });
    };

    amzTables.addRow = function (that) {
        let parentTable = $(that).closest('.amz-table').find('table').dataTable();
        let countColumns = parentTable.fnSettings().aoColumns.length;
        let currentTable = amzTables.tables[parentTable[0].id];
        let newRows = [];
        newRows.push('ASIN');

        for (let i = 1; i < countColumns; i++) {
            newRows.push('')
        }

        currentTable.row.add(newRows).draw(false);
    };

    amzTables.deleteRow = function (that) {
        let parentTable = $(that).closest('.amz-table').find('table');

        if (amzTables.tables[parentTable[0].id].row('.selected')[0].length === 0) {
            alert('You need select one row to delete.');
        }
        amzTables.tables[parentTable[0].id].row('.selected').remove().draw(false);
    };

    amzTables.deleteColumn = function (that) {
        let parentTable = $(that).closest('.amz-table').find('table');
        let parentTableId = parentTable[0].id;
        let selectedTH = $('#' + parentTableId + ' thead th.selected');

        if (selectedTH.length === 0) {
            alert('You need select one column to delete.');
            return false;
        }

        if (selectedTH[0].innerText === 'ASIN') {
            alert('This column can´t delete.');
            return false;
        }

        $('#' + parentTableId).DataTable().destroy();

        let selectedTF = $('#' + parentTableId + ' tfoot th')[selectedTH[0].cellIndex];
        let selectedTR = $('#' + parentTableId + ' tbody tr');
        for (let i = 0; i < selectedTR.length; i++) {
            $('td', selectedTR[i])[selectedTH[0].cellIndex].remove();
        }
        selectedTH.remove();
        selectedTF.remove();

        amzTables.initDataTable(parentTableId);
    };

    amzTables.addColumn = function (that) {
        let parentTable = $(that).closest('.amz-table').find('table');
        let parentTableId = parentTable[0].id;
        let selectedTH = $('#' + parentTableId + ' thead th.selected');
        if (selectedTH.length === 0) {
            alert('You need select one column to add another on right.');
            return false;
        }
        let selectedTF = $('#' + parentTableId + ' tfoot th')[selectedTH[0].cellIndex];

        let selectedTR = $('#' + parentTableId + ' tbody tr');
        selectedTH.after("<th></th>");
        $(selectedTF).after("<th></th>");
        for (let i = 0; i < selectedTR.length; i++) {
            $($('td', selectedTR[i])[selectedTH[0].cellIndex]).after("<td></td>");
        }

        $('#' + parentTableId).DataTable().destroy();
        amzTables.initDataTable(parentTableId);
    };

    amzTables.showHide = function(that){
        let childTable = $(that).closest('.amz-table');
        let childTableBody = $(that).closest('.amz-table').find('.amz-table-body');
        let childTableFooter = $(that).closest('.amz-table').find('.amz-table-foot');
        let isVisible = childTableBody.is(':visible');

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

    amzTables.showHideAll = function(that){
        let thisState = that.value;

        if (thisState === 'Hide all') {
            $('.amz-table-body').fadeOut();
            $('.amz-table-foot').fadeOut();
            that.value = 'Show all';
        } else {
            $('.amz-table-body').fadeIn();
            $('.amz-table-foot').fadeIn();
            that.value = 'Hide all';
        }
    };

    amzTables.reOrder = function(that){
        let url = new URL(window.location.href);
        if (url.searchParams.get('re-order')) {
            let query_string = url.search;
            let search_params = new URLSearchParams(query_string);
            search_params.delete('re-order');
            url.search = search_params.toString();
            window.location.href = url.toString();
        } else {
            window.location.href = window.location.href + '&re-order=true'
        }
    };

    $('table').each(function (index, value) {
        let currentTable = $(value);
        let currentIdTable = amzTables.setTableId();

        currentTable[0].id = currentIdTable;

        amzTables.initDataTable(currentIdTable);
        amzTables.initTableFunctionality(currentIdTable);
    });

});
