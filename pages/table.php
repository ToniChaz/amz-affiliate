<?php
/**
 * Table page
 *
 * @package Amazon affiliate products
 * @subpackage table
 * @author Toni Chaz
 * @since 1.0.7
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

$amz_table_api = new Amz_Table_Api();
$tables        = $amz_table_api->find_tables();
?>

<div id="wpbody" role="main">
    <div id="wpbody-content" aria-label="Contenido principal" tabindex="0">
        <div class="wrap">
            <h1>Amazon Tables</h1>
            <div id="noticeDialog"></div>
            <div class="tablenav top">
                <input type="button" id="addNewTable" class="button button-primary" value="Add new table">
                <input type="button" class="button show-hide right" onClick="amzTables.reOrder()" value="Re order">
                <input type="button" class="button show-hide right" onClick="amzTables.showHideAll(this)" value="Hide all">
                <br class="clear">
            </div>
            <div id="tablesContent">
				<?php if ( $tables ) {
                    if (isset($_GET['re-order'])) {
                        rsort($tables);
                    }
					foreach ( $tables as $table ) { ?>
                        <div class="amz-table widefat">
                            <div class="amz-table-head">
                                <div class="amz-number"><p><?php echo '[amztable_' . $table->table_id . ']'; ?></p></div>
                                <div class="amz-actions">
                                    <input type="button" class="button button-danger" onClick="amzTables.deleteRow(this)" value="- Row">
                                    <input type="button" class="button button-primary" onClick="amzTables.addRow(this)" value="+ Row">
                                    <input type="button" class="button button-danger" onClick="amzTables.deleteColumn(this)" value="- Column">
                                    <input type="button" class="button button-primary" onClick="amzTables.addColumn(this)" value="+ Column">
                                    <input name="table_name" value="<?php echo $table->table_name; ?>" />
                                    <p class="amz-date">Last update: <?php echo $table->table_time; ?></p>
                                </div>
                                <div class="amz-buttons">
                                    <input type="button" class="button button-danger" onClick="amzTables.deleteTable(this, <?php echo $table->table_id; ?>)"
                                           value="Delete">
                                    <input type="button" class="button button-primary" onClick="amzTables.saveTable(this, <?php echo $table->table_id; ?>)"
                                           value="Update">
                                    <input type="button" class="button show-hide" onClick="amzTables.showHide(this)" value="Hide">
                                </div>
                            </div>
                            <br class="clear">
                            <div class="loading"></div>
                            <div class="amz-table-body">
                                <table class="display cell-border" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
										<?php foreach ( $table->table_head as $cell ) { ?>
                                            <th><?php echo $cell; ?></th>
										<?php } ?>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
	                                    <?php foreach ( $table->table_head as $cell ) { ?>
                                            <th><?php echo $cell; ?></th>
	                                    <?php } ?>
                                    </tr>
                                    </tfoot>
                                    <tbody>
									<?php foreach ( $table->table_body as $row ) { ?>
                                        <tr>
											<?php foreach ( $row as $cell ) { ?>
                                                <td><?php echo $cell; ?></td>
											<?php } ?>
                                        </tr>
									<?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <br class="clear">
                            <div class="amz-table-foot">
                                <div class="amz-number"><p><?php echo '[amztable_' . $table->table_id . ']'; ?></p></div>
                                <div class="amz-actions">
                                    <input type="button" class="button button-danger" onClick="amzTables.deleteRow(this)" value="- Row">
                                    <input type="button" class="button button-primary" onClick="amzTables.addRow(this)" value="+ Row">
                                    <input type="button" class="button button-danger" onClick="amzTables.deleteColumn(this)" value="- Column">
                                    <input type="button" class="button button-primary" onClick="amzTables.addColumn(this)" value="+ Column">
                                </div>
                                <div class="amz-buttons">
                                    <input type="button" class="button button-danger" onClick="amzTables.deleteTable(this, <?php echo $table->table_id; ?>)"
                                           value="Delete">
                                    <input type="button" class="button button-primary" onClick="amzTables.saveTable(this, <?php echo $table->table_id; ?>)"
                                           value="Update">
                                    <input type="button" class="button show-hide" onClick="amzTables.showHide(this)" value="Hide">
                                </div>
                            </div>
                        </div>
					<?php }
				} else { ?>
                    <h1>You don´t have any table.</h1>
				<?php } ?>
            </div>
            <div class="clear"></div>
        </div><!-- wpbody-content -->
        <div class="clear"></div>
    </div>
</div>
