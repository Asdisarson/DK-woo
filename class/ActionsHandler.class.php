<?php

class DKP_ActionsHandler {

	public static function init() {
		
	}

	public static function wp() {

		$DKPlus = new DKP_DKPlus;

		// $DKPlus::validateCartProducts();

	}

	public static function woocommerce_new_order($order_id) {

		$sent_to_dkplus = get_post_meta($order_id, 'sent_to_dkplus', true);

		if($sent_to_dkplus != 'yes') {

			$DKPlus = new DKP_DKPlus;
			$order = wc_get_order($order_id);

			$DKPlus::createSalesInvoice($order);

		}

	}

	public static function woocommerce_after_checkout_validation($posted) {

		$DKPlus = new DKP_DKPlus;
		$cart = WC()->cart->get_cart();

		foreach ($cart as $key => $value) {

			$product_id = $value['product_id'];
			$variation_id = $value['variation_id'];
			if ($variation_id) {
				$product_id = $variation_id;
			}
			$quantity = $value['quantity'];

            $product = wc_get_product($product_id);

            $item_code = $product->get_sku();

			if($item_code) {

				$product_stock = $DKPlus::getProductStock($item_code, DKP_WAREHOUSE);

				if( $quantity > $product_stock ) {


					wc_add_notice('Maximum <b>' . $product_stock . '</b> of <b>"' . $product->get_name() . '"</b> is allowed per order', 'error');

					break;

				}

			}

		}

	}




}

