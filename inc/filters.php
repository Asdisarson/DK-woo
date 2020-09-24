<?php

$handler = new DKP_FiltersHandler;

add_filter('woocommerce_add_to_cart_validation', array($handler, 'woocommerce_add_to_cart_validation'), 10, 4);

add_filter('woocommerce_update_cart_validation', array($handler, 'woocommerce_update_cart_validation'), 10, 4);




