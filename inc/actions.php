<?php

$handler = new DKP_ActionsHandler;

add_action( 'wp', array($handler, 'wp') );

$new_order_hooks = array(
    'woocommerce_new_order',
    'woocommerce_order_status_completed',
    'woocommerce_order_status_processing',
);

foreach ($new_order_hooks as $new_order_hook) {

    add_action($new_order_hook, array($handler, 'woocommerce_new_order'), 10, 1);

}

add_action('woocommerce_after_checkout_validation', array($handler, 'woocommerce_after_checkout_validation'));


