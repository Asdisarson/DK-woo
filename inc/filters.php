<?php

$handler = new DKP_FiltersHandler;

add_filter('woocommerce_add_to_cart_validation', array($handler, 'woocommerce_add_to_cart_validation'), 10, 4);

add_filter('woocommerce_update_cart_validation', array($handler, 'woocommerce_update_cart_validation'), 10, 4);

/*
add_filter( 'woocommerce_cart_item_quantity', 'wc_cart_item_quantity', 10, 3 );
function wc_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item )
{
    if (is_cart()) {
        $product_quantity = sprintf('%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity']);
    }
    return $product_quantity;

}*/
// add_filter('woocommerce_product_get_stock_quantity' ,'custom_get_stock_quantity', 10, 2);
// add_filter('woocommerce_product_variation_get_stock_quantity' ,'custom_get_stock_quantity', 10, 2);

// add_filter( 'page_template', array($handler, 'page_template') );

