<?php

class DKP_DKPlus {

	function __construct() {

	}

	public static function request($endpoint, $params = array(), $method = 'GET') {

		$response = array();

		$curl = curl_init();

		$headers = array(
			'Content-Type: application/json',
			'Authorization: bearer ' . DKP_API_AUTH,
		);

		$url = DKP_API_URL . $endpoint;

		$curl_options = array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => $method,
		  CURLOPT_HTTPHEADER => $headers,
		);

		$methods = array(
			'PUT',
			'POST',
			'DELETE',
		);

		if( in_array($method, $methods) && count($params) > 0 ) {

			$postfields = $params;
			$postfields_json = json_encode($postfields);

			// $postfields_json = addslashes($postfields_json);

			// $postfields_json = "{\n\t\"Number\":\"1906083010\",\n\t\"Name\":\"Jón Hafdal\",\n\t\"Address1\":\"Some Location\"\n}";

			$curl_options[CURLOPT_POSTFIELDS] = $postfields_json;

			self::logData('request_data.txt', $postfields_json);

		}

		curl_setopt_array($curl, $curl_options);

		$result = curl_exec($curl);

		$response['result'] = json_decode($result, true);

		if ( curl_errno($curl) ) {

	    $response['error'] = curl_error($curl);

		} else {

			$response['HTTP_CODE'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		}

		curl_close($curl);

		return $response;

	}

	public static function logData($file, $data) {

		$file = dkp_dir("/static/{$file}");

		if( is_object($data) || is_array($data) ) {

			$data = json_encode($data);

		}

		file_put_contents($file, $data);

	}

	public static function getProduct($item_code, $field = false) {

		$product = false;
		$response = self::request("/Product/{$item_code}");
		// $response = self::request("/Product/{$item_code}?isBase64=false");

		if( $field && is_array($response['result']) && isset($response['result'][$field]) ) {

			$product = $response['result'][$field];

		} else if( is_array($response['result']) && isset($response['result']) ) {

			$product = $response['result'];

		}

		return $product;

	}

	public static function getProductWarehouses($item_code) {

		$warehouses = array();
		$product = self::getProduct($item_code);

		if( is_array($product) && isset($product['Warehouses']) ) {

			$warehouses = $product['Warehouses'];

		}

		return $warehouses;

	}

	public static function getProductWarehouse($item_code, $warehouse_name) {

		$warehouse = false;
		$warehouses = self::getProductWarehouses($item_code);

		foreach ($warehouses as $w) {

			if($w['Warehouse'] == $warehouse_name) {

				$warehouse = $w;

			}

		}

		return $warehouse;

	}

	public static function getProductStock($item_code, $warehouse_name) {

		$stock = 0;
		$warehouse = self::getProductWarehouse($item_code, $warehouse_name);

		if( is_array($warehouse) && isset($warehouse['QuantityInStock']) ) {

			$stock = $warehouse['QuantityInStock'];

		}

		return $stock;

	}

	public static function validateAddToCart($product_id, $quantity, $updating_cart = false) {

		$validate = array(
			'valid' => true,
		);

		$item_code = get_post_meta($product_id, 'item_code', true);

		if($item_code) {

			$cart_items = self::getCartProducts();
			$product_stock = self::getProductStock($item_code, 'bg1');

			$validate['stock'] = $product_stock;

			$cart_qty = isset($cart_items[$product_id]) ? $cart_items[$product_id]['qty'] : 0;
			// $quantity2 = $quantity + $cart_qty;

			$quantity2 = ($updating_cart ? $quantity : ($quantity + $cart_qty));

			if( $quantity2 > $product_stock ) {

				if( isset($cart_items[$product_id]) ) {

					WC()->cart->set_quantity($cart_items[$product_id]['key'], $product_stock);

				}

				$validate['valid'] = false;

			}

		}

    return $validate;

	}

	public static function getCartProducts() {

		$cart = WC()->cart->get_cart();
		$cart_items = array();

		foreach ($cart as $cart_item) {

			// $cart_items[] = $cart_item;

			$cart_items[$cart_item['product_id']] = array(
				'key' => $cart_item['key'],
				'qty' => $cart_item['quantity'],
			);

		}

		return $cart_items;

	}

	public static function validateCartProducts() {

		$cart_items = self::getCartProducts();

		if( is_singular('product') && count($_POST) == 0 ) {

			$product_id = get_the_ID();
			$item_code = get_post_meta($product_id, 'item_code', true);

			if( $item_code && isset($cart_items[$product_id]) ) {

				$qty = $cart_items[$product_id]['qty'];
				$product_stock = self::getProductStock($item_code, 'bg1');

				if( $qty > $product_stock ) {

					$product = wc_get_product($product_id);

					wc_add_notice('Maximum <b>' . $product_stock . '</b> of <b>"' . $product->get_name() . '"</b> is allowed per order', 'error');

					WC()->cart->set_quantity($cart_items[$product_id]['key'], $product_stock);

				}

			}

		}

	}



	public static function createPurchase($wc_order) {

		$order = false;
		$lines = array();

		foreach ($wc_order->get_items() as $item_id => $item) {

			$product_id = $item->get_product_id();
			$product = $item->get_product();
			$quantity = $item->get_quantity();
			$allmeta = $item->get_meta_data();
			$somemeta = $item->get_meta( '_whatever', true );

			$item_code = get_post_meta($product_id, 'item_code', true);

			if( $item_code ) {

				$lines[] = array(
					'Warehouse' => DKP_WAREHOUSE,
					'Code' => $item_code,
					'CodeType' => 'ItemCode',
					'Quantity' => $quantity,
					'Reference' => uniqid(),
					// 'Reference' => $product->get_name(),
				);

			}

		}

		if(count($lines) > 0) {

			$data = array(
				// 'Vendor' => array(
				// 	'Number' => '5206922829'
				// ),
				'Lines' => $lines,
			);

			$data = $data;
			// $data = array($data);

			$response = self::request('/purchase', $data, 'POST');

			$file = dkp_dir('/static/response.txt');
			$file2 = dkp_dir('/static/request.txt');

			file_put_contents($file, json_encode($response));
			file_put_contents($file2, json_encode($data));

		}

	}

	public static function createVendor() {

		// $vendor_id = uniqid();
		// $vendor_id = 'WC' . uniqid();

		$vendor_id = '1232123';
		$data = array(
			'Number' => $vendor_id,
			'SSNumber' => $vendor_id,
			'Name' => 'AHMAD2 KARIM2',
			'PaymentMode' => 'BM',
			'LedgerCode' => '0001',
		);

		$data = array($data);

		$response = self::request('/vendor', $data, 'POST');

		self::logData('create_vendor_request.txt', $data);
		self::logData('create_vendor_response.txt', $response);

		return $response;

	}

	public static function updateVendor($vendor_id) {

		$data = array(
			'Address1' => '',
		);

		$response = self::request("/vendor/{$vendor_id}", $data, 'PUT');

		self::logData('vendor_update.txt', $response);

	}

	public static function createCustomerID($wc_user_id, $length = 10) {

		$uniqid = uniqid();
		$uniqid = strrev($uniqid);
		$customer_id = "WC{$wc_user_id}{$uniqid}";
		$customer_id = substr($customer_id, 0, $length);
		$customer_id = strtolower($customer_id);

		return $customer_id;

	}

	public static function createCustomer($wc_user_id) {

		$dkplus_customer_id = get_user_meta($wc_user_id, 'dkplus_customer_id', true);

		if( empty($dkplus_customer_id) ) {

			$customer_id = self::createCustomerID($wc_user_id, 10);

			$user_info = get_userdata($wc_user_id);

			$customer_name = $user_info->first_name . ' ' . $user_info->last_name;
			$customer_email = $user_info->user_email;
			$address1 = get_user_meta($wc_user_id, 'shipping_address_1', true);

			$data = array(
				"Number"   => $customer_id,
				"Name"     => $customer_name,
				"Email"    => $customer_email,
				"Address1" => $address1,
			);

			$response = self::request("/customer", $data, "POST");

			if( isset($response['HTTP_CODE']) && $response['HTTP_CODE'] == 200 ) {

				$dkplus_customer_id = $customer_id;

				update_user_meta($wc_user_id, 'dkplus_customer_id', $dkplus_customer_id);

			}

			self::logData('createCustomer_request.txt', $data);

		}

		return $dkplus_customer_id;

	}

	public static function createSalesOrder($wc_order) {

		$order = false;
		$lines = array();

		$order_data = $wc_order->get_data();

		foreach ($wc_order->get_items() as $item_id => $item) {

			$product_id = $item->get_product_id();
			$product    = $item->get_product();
			$quantity   = $item->get_quantity();
			$allmeta    = $item->get_meta_data();
			$somemeta   = $item->get_meta( '_whatever', true );

			$item_code = get_post_meta($product_id, 'item_code', true);

			if( $item_code ) {

				$lines[] = array(
					'ItemCode' => $item_code,
					'Quantity' => $quantity,
				);

			}

		}

		$order_customer_id = $order_data['customer_id'];

		if( count($lines) > 0 && $order_customer_id > 0 ) {

			$dkplus_customer_id = self::createCustomer($order_customer_id);

			$customer_name = $order_data['shipping']['first_name'] . ' ' . $order_data['shipping']['last_name'];
			$order_date = $order_data['date_created']->date('Y-m-d H:i:s');
			$order_shipping_address_1 = $order_data['shipping']['address_1'];

			$customer = array(
				"Name" => $customer_name,
				"Number" => $dkplus_customer_id,
				"Address1" => $order_shipping_address_1,
			);

			$data = array(
				"Lines" => $lines,
				"Customer" => $customer,
				"OrderDate" => $order_date,
				"Currency" => DKP_CURRENCY,
				"SalePerson" => "WooCommerce",
			);

			$response = self::request("/sales/order", $data, "POST");

			if( isset($response['HTTP_CODE']) && $response['HTTP_CODE'] == 200 ) {

				update_post_meta($order_data['id'], 'sent_to_dkplus', 'yes');

			}

			self::logData('createSalesOrder_request.txt', $data);
			self::logData('createSalesOrder_response.txt', $response);

		}

	}

	public static function createSalesInvoice($wc_order) {

		$order = false;
		$lines = array();

		$order_data = $wc_order->get_data();

		foreach ($wc_order->get_items() as $item_id => $item) {

			// print_are($item);exit;

			$product_id = $item->get_product_id();
			$product    = $item->get_product();
			$price      = $item->get_total();
			$quantity   = $item->get_quantity();
			$allmeta    = $item->get_meta_data();
			$somemeta   = $item->get_meta( '_whatever', true );

			$item_code = get_post_meta($product_id, 'item_code', true);

			if( $item_code ) {

				$lines[] = array(
					'ItemCode' => $item_code,
					'Quantity' => $quantity,
					'Price' => $price,
				);

			}

		}

		$order_customer_id = $order_data['customer_id'];

		if( count($lines) > 0 && $order_customer_id > 0 ) {

			/*$lines[] = array(
				"DoPost" => true,
			);*/

			$dkplus_customer_id = self::createCustomer($order_customer_id);

			$customer_name = $order_data['shipping']['first_name'] . ' ' . $order_data['shipping']['last_name'];
			$order_date = $order_data['date_created']->date('Y-m-d H:i:s');
			$order_shipping_address_1 = $order_data['shipping']['address_1'];

			$customer = array(
				"Name" => $customer_name,
				"Number" => $dkplus_customer_id,
				// "Number" => "wc01aad8048",
				"Address1" => $order_shipping_address_1,
			);

			$contact = array(
				"Name" => $customer_name,
				"Number" => $dkplus_customer_id,
			);

			$receiver = array(
				"Name" => $customer_name,
				"Number" => $dkplus_customer_id,
			);

			$data = array(
				"Lines" => $lines,
				"DoPost" => true,
				"Customer" => $customer,
				"SalesPerson" => DKP_SALESPERSON,
				"Date" => $order_date,
				"Currency" => DKP_CURRENCY,
				"Options" => array(
					"OriginalPrices" => false,
				),
				// "SalePerson" => "WEB",
				// "SalePerson" => "WooCommerce",
			);

			self::logData('createSalesInvoice_request.txt', $data);

			$response = self::request("/sales/invoice", $data, "POST");

			self::logData('createSalesInvoice_response.txt', $response);

			if( isset($response['HTTP_CODE']) && $response['HTTP_CODE'] == 200 ) {

				update_post_meta($order_data['id'], 'sent_to_dkplus', 'yes');

			}

			return $response;

		}

	}

	public static function getPermission() {

		$response = self::request("/permission");

		self::logData('permission.txt', $response['result']);

		return $response;

	}

	public static function getSalesPerson() {

		$response = self::request("/sales/person/page/1/100");

		self::logData('getSalesPerson.txt', $response);

		return $response;

	}


}

