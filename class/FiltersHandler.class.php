<?php

class DKP_FiltersHandler
{

    public static function woocommerce_add_to_cart_validation($true, $product_id, $quantity, $variation_id = null)
    {

        $DKPlus = new DKP_DKPlus;

        $cart_items = $DKPlus::getCartProducts();
        if ($variation_id) {
            $product_id = $variation_id;
        }
        $validate = $DKPlus::validateAddToCart($product_id, $quantity);

        $true = $validate['valid'];

        if (!$true) {

            $product = wc_get_product($product_id);

            $vars_vals = array(
                "{MAX_QTY}" => $validate['stock'],
                "{PRODUCT_NAME}" => $product->get_name(),
            );

            $notice = str_replace(array_keys($vars_vals), array_values($vars_vals), DKP_PRODUCT_NOTICE);

            wc_add_notice($notice, 'error');

        }

        return $true;

    }

    function woocommerce_update_cart_validation($true, $cart_item_key, $values, $quantity)
    {

        $cart = WC()->cart->get_cart();

        if (isset($cart[$cart_item_key])) {

            $DKPlus = new DKP_DKPlus;

            $product_id = $cart[$cart_item_key]['product_id'];
            $variation_id = $cart[$cart_item_key]['variation_id'];
            if ($variation_id) {
                $product_id = $variation_id;
            }
            $validate = $DKPlus::validateAddToCart($product_id, $quantity, true);
            $true = $validate['valid'];


            if (!$true) {
                $product_id = $cart[$cart_item_key]['product_id'];
                if ($variation_id) {
                    $product_id = $cart[$cart_item_key]['variation_id'];
                }
                $product = wc_get_product($product_id);

                $vars_vals = array(
                    "{MAX_QTY}" => $validate['stock'],
                    "{PRODUCT_NAME}" => $product->get_name(),
                );

                $notice = str_replace(array_keys($vars_vals), array_values($vars_vals), DKP_PRODUCT_NOTICE);

                wc_add_notice($notice, 'error');

            }

        }

        return $true;

    }

    function page_template($page_template)
    {

        if (is_page('dplus-api')) {

            $page_template = dirname(__FILE__) . '/dplus-api-template.php';

        }

        return $page_template;

    }




}

