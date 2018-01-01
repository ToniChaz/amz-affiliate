/*
 * The products main javascript
 */

// Document ready
jQuery(function () {
    var $ = jQuery;
    window.amzProducts = window.amzProducts || {};

    // plugin vars
    amzProducts.productASIN = null;

    amzProducts.loadResultContent = function (response) {

        var price;

        if (response.OfferSummary.hasOwnProperty('LowestNewPrice')) {
            price = response.OfferSummary.LowestNewPrice.FormattedPrice;
        } else if (response.OfferSummary.hasOwnProperty('LowestUsedPrice')) {
            price = response.OfferSummary.LowestUsedPrice.FormattedPrice + '<br />(Only used)';
        } else {
            price = 'No disponible';
        }

        $('#productResultImg').attr('src', response.MediumImage.URL);
        $('#productResultTxt').html(response.ItemAttributes.Title);
        $('#productResultLink').attr('href', response.DetailPageURL);
        $('#productResultPrice').html(price);
        $('#productASIN').val(response.ASIN);
        $('#productResult').fadeIn();
        amzProducts.productASIN = response.ASIN;
    };

    amzProducts.update = function (productId) {
        if (!productId) {
            alert('Product ID code is required');
            return false;
        }

        var data = {
            'action': 'update_product',
            'product_asin': productId
        };

        window.amzCommons.callApi(data, function (response) {
            window.amzCommons.notice('notice-success', response.message);
            // TODO fix this shit
            setTimeout(function () {
                window.location.reload();
            }, 1000);
        });
    };

    amzProducts.confirmDelete = function (productId, productASIN) {
        var msg = 'Are you sure you want to delete the product/s: ' + productASIN;
        if (confirm(msg)) {
            if (!productId) {
                alert('Product ID code is required');
                return false;
            }

            var data = {
                'action': 'delete_product',
                'product_id': productId
            };

            window.amzCommons.callApi(data, function (response) {
                window.amzCommons.notice('notice-success', response.message);
                // TODO fix this shit
                setTimeout(function () {
                    window.location.reload();
                }, 1000);
            });
        }

        return false;
    };

    amzProducts.updateIntLink = function (productId) {
        if (!productId) {
            alert('Product ID can&#8217;t be empty!');
            return false;
        }

        var intLink = $('#int_link_' + productId).val(),
            data = {
                'action': 'update_product_int_link',
                'product_id': productId,
                'product_int_link': intLink
            };

        if (intLink === '') {
            var msg = 'The internal link is empty, delete exist!';
            if (confirm(msg)) {
                window.amzCommons.callApi(data, function (response) {
                    window.amzCommons.notice('notice-success', response.message);
                });
            }

            return false;
        } else {
            window.amzCommons.callApi(data, function (response) {
                window.amzCommons.notice('notice-success', response.message);
            });
        }
    };

    amzProducts.findProduct = function () {
        var productASIN = $('#productAsin').val();
        if (!productASIN) {
            alert('Product ASIN code is required');
            return false;
        }

        var data = {
            'action': 'find_product',
            'product_asin': productASIN
        };

        window.amzCommons.callApi(data, function (response) {
            window.amzCommons.notice('notice-success', response.message);
            var parseResponse = JSON.parse(response.data)
            window.amzProducts.loadResultContent(parseResponse);
        });
    };

    amzProducts.saveProduct = function () {
        if (!amzProducts.productASIN) {
            alert('Current product is undefined!');
            return false;
        }

        var data = {
            'action': 'save_product',
            'product_asin': amzProducts.productASIN
        };

        window.amzCommons.callApi(data, function (response) {
            window.amzCommons.notice('notice-success', response.message);
            // TODO fix this shit
            setTimeout(function () {
                window.location.reload();
            }, 1000);
        });
    };

    $('#applyBulkActions').on('click', function () {
        var action = $('#bulk-action-selector-top').val();
        if (action === "null") {
            return false;
        }
        var productsToBulk = [];

        $('.check-column input:checkbox:checked').each(function (idx, productId) {
            if(productId.value !== 'on'){
                productsToBulk.push(productId.value)
            }
        });

        if (productsToBulk.length === 0) {
            alert('You need selected minimum one product!');
            return false;
        }

        if (action === 'delete-selected') {
            amzProducts.confirmDelete(productsToBulk, productsToBulk.join())
        } else {
            //TODO update multiple products
            alert('Coming soon!');
        }
    });
});
