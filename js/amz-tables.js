/*
 * The amz-tables tables javascript
 */

// Document ready
jQuery(function () {
    var $ = jQuery;
    window.amzTables = window.amzTables || {};
    amzTables.baseURL = '../wp-content/plugins/amz-affiliate/';
    amzTables.tablesIds = [];
    amzTables.tables = {};

    amzTables.setTableId = function() {
        var currentId = 'table_' + Math.random().toString(36).substr(2, 5);
        if (amzTables.tablesIds.indexOf(currentId) > 0) {
            amzTables.setTableId()
        } else {
            amzTables.tablesIds.push(currentId);
            return currentId;
        }
    };

    amzTables.initDataTable = function(currentIdTable) {
        amzTables.tables[currentIdTable] = $('#' + currentIdTable).DataTable({
            'paging': false,
            'ordering': true,
            'info': false
        });
    };

    $('table').each(function (index, value) {
        var currentTable = $(value);
        var currentIdTable = amzTables.setTableId();

        currentTable[0].id = currentIdTable;

        amzTables.initDataTable(currentIdTable);
    });

});
