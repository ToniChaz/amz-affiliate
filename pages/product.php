<?php
/**
 * Product page
 *
 * @package Amazon affiliate products
 * @subpackage product
 * @author Toni Chaz
 * @since 1.0.5
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

$amz_product_api = new Amz_Product_Api();
$products = $amz_product_api->find_products();

?>

<div id="wpbody" role="main">
    <div id="wpbody-content" aria-label="Contenido principal" tabindex="0">
        <div class="wrap">
            <h1>Amazon products</h1>
            <div id="noticeDialog"></div>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label for="product_asin">Add a new amazon product</label></th>
                    <td>
                        <input name="product_asin" id="productAsin" type="text" class="regular-text" placeholder="Product ASIN">
                        <input type="button" name="submit" onclick="amzProducts.findProduct();" class="button button-primary" value="Add product">
                    </td>
                </tr>
                </tbody>
            </table>
            <br/>
            <br/>
            <table class="wp-list-table widefat hidden" id="productResult" style="margin-bottom: 20px">
                <tbody>
                <tr>
                    <td><img id="productResultImg" src=""></td>
                    <td>
                        <h2 id="productResultTxt"></h2>
                        <a href="" id="productResultLink" target="_blank" id="productResultLink">View in Amazon</a>
                        <br/>
                        <br/>
                        <input type="button" name="save_product" onclick="amzProducts.saveProduct();" class="button button-primary" value="Save product">
                    </td>
                    <td><h1 class="price" id="productResultPrice"></h1></td>
                    <td></td>
                </tr>
                </tbody>
            </table>
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top" class="screen-reader-text">Select a bulk action</label>
                    <select name="action" id="bulk-action-selector-top">
                        <option value="null">Bulk actions</option>
                        <option value="update-selected">Update</option>
                        <option value="delete-selected">Delete</option>
                    </select>
                    <input type="submit" id="applyBulkActions" class="button action" value="Apply">
                </div>
                <div class="tablenav-pages one-page">
                    <span class="displaying-num">You have <?php echo count($products); ?> products in your Database</span>
                </div>
                <br class="clear">
            </div>
            <table class="wp-list-table widefat striped">
                <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1">Select all</label>
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th scope="col" id="image" class="manage-column column-name column-primary">Image</th>
                    <th scope="col" id="asin" class="manage-column column-name column-primary">ASIN</th>
                    <th scope="col" id="title" class="manage-column column-description">Title</th>
                    <th scope="col" id="time" class="manage-column column-description">Last update</th>
                    <th scope="col" id="price" class="manage-column column-description">Price</th>
                </tr>
                </thead>
                <tbody id="tableList">
                <?php if ($products) {
                    foreach ($products as $product) { ?>
                        <tr data-id="<?php echo $product->id; ?>">
                            <th scope="row" class="check-column">
                                <label class="screen-reader-text" for="checkbox_<?php echo $product->id; ?>">Elige Add Meta Tags</label>
                                <input type="checkbox" name="checked[]" value="<?php echo $product->id; ?>" id="checkbox_<?php echo $product->id; ?>">
                            </th>
                            <td>
                                <img src="<?php echo $product->data['SmallImage']['URL']; ?>"/>
                            </td>
                            <td>
                                <strong class="has-media-icon">
                                    <?php echo $product->asin; ?>
                                </strong>
                            </td>
                            <td>
                                <div>
                                    <p><?php echo $product->data['ItemAttributes']['Title']; ?></p>
                                </div>
                                <div class="row-actions">
                                    <span class="update">
                                        <a href="#"
                                           onclick="amzProducts.update('<?php echo $product->asin; ?>');"
                                           aria-label="Update">Update</a> |
                                    </span>
                                    <span class="delete">
                                        <a href="" class="submitdelete aria-button-if-js"
                                           onclick="amzProducts.confirmDelete(<?php echo $product->id; ?>, '<?php echo $product->asin; ?>');"
                                           aria-label="Delete" role="button">Delete</a> |
                                    </span>
                                    <span class="view">
                                        <a href="<?php echo $product->data['DetailPageURL'] ?>"
                                           aria-label="View in Amazon"
                                           rel="permalink" target="_blank">View in Amazon</a>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <strong class="has-media-icon">
                                    <?php echo $product->time; ?>
                                </strong>
                            </td>
                            <td>
                                <strong class="has-media-icon">
                                    <?php echo $product->price; ?>
                                </strong>
                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td></td>
                        <td>
                            You don't have any product
                        </td>
                        <td></td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-2">Select all</label>
                        <input id="cb-select-all-2" type="checkbox">
                    </td>
                    <th scope="col" class="manage-column column-name column-primary">Image</th>
                    <th scope="col" class="manage-column column-name column-primary">ASIN</th>
                    <th scope="col" class="manage-column column-description">Title</th>
                    <th scope="col" class="manage-column column-description">Last update</th>
                    <th scope="col" class="manage-column column-description">Price</th>
                </tr>
                </tfoot>
            </table>
            <div class="clear"></div>
        </div><!-- wpbody-content -->
        <div class="clear"></div>
    </div>
</div>
