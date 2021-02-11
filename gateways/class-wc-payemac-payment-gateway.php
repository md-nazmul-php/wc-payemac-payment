<?php

/**
 * The gateway-facing functionality of the plugin.
 *
 * @link       https://deshonlineit.com
 * @since      1.0.0
 *
 * @package    Wc_Payemac_Payment
 * @subpackage Wc_Payemac_Payment/gateway
 */

/**
 * The gateway-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the gateway-facing stylesheet and JavaScript.
 *
 * @package    Wc_Payemac_Payment
 * @subpackage Wc_Payemac_Payment/gateway
 * @author     Md Nazmul <php673500@gmail.com>
 */


// =============Start Plugin Class=================


/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'wcpayemax_add_gateway_class' );
function wcpayemax_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Wcpayemax_Gateway'; // your class name is here
	return $gateways;
}
 
/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'wcpayemax_init_gateway_class' );

function wcpayemax_init_gateway_class() {

 
	class WC_Wcpayemax_Gateway extends WC_Payment_Gateway {
 
 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 public function __construct() {
 $this->id = 'wcpayemax'; // payment gateway plugin ID
	$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
	$this->has_fields = false; // in case you need a custom credit card form
	$this->method_title = 'WcpayeMax Gateway';
	$this->method_description = 'Description of WcpayeMax payment gateway'; // will be displayed on the options page
 
	// gateways can support subscriptions, refunds, saved payment methods,
	// but in this tutorial we begin with simple payments
	$this->supports = array(
		'products'
	);
 
	// Method with all the options fields
	$this->init_form_fields();
 
	// Load the settings.
	$this->init_settings();
	$this->title = $this->get_option( 'title' );
	$this->description = $this->get_option( 'description' );
	$this->enabled = $this->get_option( 'enabled' );
	$this->testmode = 'yes' === $this->get_option( 'testmode' );
	$this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
	$this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
 
	// This action hook saves the settings
	add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
 
	// We need custom JavaScript to obtain a token
	 add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
 
	// You can also register a webhook here
	 add_action( 'woocommerce_api_return_response_cbl', array( $this, 'webhook' ) );
 }
 
 
		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
 public function init_form_fields(){
 $this->form_fields = array(
		'enabled' => array(
			'title'       => 'Enable/Disable',
			'label'       => 'Enable Misha Gateway',
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no'
		),
		'title' => array(
			'title'       => 'Title',
			'type'        => 'text',
			'description' => 'This controls the title which the user sees during checkout.',
			'default'     => 'Credit Card',
			'desc_tip'    => true,
		),
		'description' => array(
			'title'       => 'Description',
			'type'        => 'textarea',
			'description' => 'This controls the description which the user sees during checkout.',
			'default'     => 'Pay with your credit card via our super-cool payment gateway.',
		),
		'testmode' => array(
			'title'       => 'Test mode',
			'label'       => 'Enable Test Mode',
			'type'        => 'checkbox',
			'description' => 'Place the payment gateway in test mode using test API keys.',
			'default'     => 'yes',
			'desc_tip'    => true,
		),
		'test_publishable_key' => array(
			'title'       => 'Test Publishable Key',
			'type'        => 'text'
		),
		'test_private_key' => array(
			'title'       => 'Test Private Key',
			'type'        => 'password',
		),
		'publishable_key' => array(
			'title'       => 'Live Publishable Key',
			'type'        => 'text'
		),
		'private_key' => array(
			'title'       => 'Live Private Key',
			'type'        => 'password'
		)
	);
 
	 	}

 
	  public function payment_scripts() {

    // we need JavaScript to process a token only on cart/checkout pages, right?
    if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
        return;
    }

    // if our payment gateway is disabled, we do not have to enqueue JS too
    if ( 'no' === $this->enabled ) {
        return;
    }

    // no reason to enqueue JavaScript if API keys are not set
    if ( empty( $this->private_key ) || empty( $this->publishable_key ) ) {
        return;
    }

    // do not work with card detailes without SSL unless your website is in a test mode
    if ( ! $this->testmode && ! is_ssl() ) {
        return;
    }

    // let's suppose it is our payment processor JavaScript that allows to obtain a token
    wp_enqueue_script( 'misha_js', 'https://www.mishapayments.com/api/token.js' );

    // and this is our custom JS in your plugin directory that works with token.js
    wp_register_script( 'woocommerce_misha', plugins_url( 'misha.js', __FILE__ ), array( 'jquery', 'misha_js' ) );

    // in most payment processors you have to use PUBLIC KEY to obtain a token
    wp_localize_script( 'woocommerce_misha', 'misha_params', array(
        'publishableKey' => $this->publishable_key
    ) );

    wp_enqueue_script( 'woocommerce_misha' );

}
 
		/*
		 * We're processing the payments here, everything about it is in Step 5
		 */
public function process_payment( $order_id ) {
 
	global $woocommerce;
 
	// we need it to get any order detailes
	$order = wc_get_order( $order_id );
	$totalamount= $order->get_total();
	
// ===============  Start Payemax API functions=================

		// Tools API sectiond==========


/**
 * curl post util
 *
 * @param string $url
 * @param array $post_data
 * @return bool|mixed
 */
function request_post($url = '', $post_data = array())
{
    if (empty($url) || empty($post_data)) {
        return "param is empty";
    }
    $postUrl = $url;
    $curlPost = $post_data;
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL, $postUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Content-Length: ' . strlen($curlPost)
    ));
    //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //post提交方式
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    //运行curl
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/**
 * Get sign Utils
 *
 * @param string $key merchant secret key
 * @param array $post_data
 * @return sign
 */
function signForMd5($key, $param)
{
    $preStr = createLinkString($param) . '&key=' . $key;
    //输出签名加签后字符串并转大写
    return strtoupper(md5($preStr));
}

/**
 * verify sign Utils
 *
 * @param string $key
 * @param array $param
 * @param string $sign
 * @return bool
 */
function verifyForMd5($key, $param, $sign)
{
    $newSign = signForMd5($key, $param, $sign);
    return $newSign === $sign;
}

function createLinkString($param)
{
    $arg = "";
    //数组排序
    ksort($param);
    reset($param);
    while (list ($key, $val) = each($param)) {
        if($key == "sign") continue;
        if(!empty($key)){
            $arg .= $key . "=";
        }
        if (is_array($val)) {
            $arg .= createLinkString($val) . "&";
        } else {
            $arg .= $val . "&";
        }
    }
    //去掉最后一个&字符
    $arg = substr($arg, 0, strlen($arg) - 1);
    return $arg;
}

// end API tools


// main API Calling here

$url = 'https://pay-gate-uat.payermax.com/aggregate-pay-gate/api/gateway';

//1 Assemble request parameters according to the guide
//TODO Replace them with your value
$post_data = array(
    'merchantId' => 'SP13591468',//change to your merchant Id from SHAREit pay
    'bizType' => 'IN_CB',//default value without modify
    'version' => '2.1',//default value without modify
    'orderId' => $order_id,//change to your own order id
    'userId' => 'test_ZNW3e',//change to the end-customer id
    'subject' => 'test_product_info',//change to your own product info
    'countryCode' => 'IN',//change to the transaction country code
    'currency' => 'INR',//change to the transaction currency code
    'totalAmount' => $totalamount,//change to the transaction amount
    'frontCallBackUrl' => 'http://localhost/wpweb/wc-api/return_response_cbl',//change to your own front callback url
    'showResult' => '5',//default value
    'expireTime' => '1800',//change to your own transaction expire time
    'description' => 'user description',//change to the end-customer description
    'reference' => 'reference',//change to the reference
    'language' => 'en',//default value
    'paymentDetail' => '{\"paymentType\":\"19\",\"accountNo\":\"\"}',//change to the paymentType and accountNo
    'userDetail' => '{\"name\":\"raoxw\",\"email\":\"raoxw_sh@qq.com\",\"phoneNumber\":\"4567890134342\",\"citizenIdNo\":\"12341292\",\"displaySave\":1,\"deviceId\":\"dadba887ba0b6b57c9d1abef46864113\",\"ip\":\"\"}',//
    'callbackUrl' => 'http://localhost/wpweb/wc-api/return_response_cbl',//change to your own server callback url
);


$sign = signForMd5('38565022dc179928', $post_data);//change to your merchant secret key from SHAREit pay
$post_data['sign'] = $sign;

//2 Request SHAREit Pay
$request = json_encode($post_data);
$res = request_post($url, $request);
$response = json_decode($res, true);

//3 Process the response from SHAREit pay
$bizCode = $response['bizCode'];
if (!empty($bizCode) && '0000' == $bizCode) {
    $data = $response['data'];

    $responseSign = $data['sign'];
    if (verifyForMd5('38565022dc179928', $data, $responseSign)) {//change to your merchant secret key from SHAREit pay
        echo 'success';

$paytn = $data['requestUrl']; // this is url where user redirect after place order to custom gateway website


    } else {
        echo 'sign error';
    }
} else {
    echo 'response is failure';
  
   
}


$order = wc_get_order( $order_id );

// Mark as on-hold (we're awaiting the payment)
$order->update_status( 'on-hold', __( 'Awaiting for gateway Confirmation', 'woocommerce' ) );

// Reduce stock levels
$order->reduce_order_stock();

// Remove cart
WC()->cart->empty_cart();

// Return thankyou redirect
return array(
'result'    => 'success',
'redirect'  => $paytn
);

}
 

//===================== web hook====================


public function webhook() {
global $woocommerce;

$order_id= $_GET['orderId'];
$order_status= $_GET['status'];

if ($order_status == 1) {


 $order = wc_get_order( $order_id );

$order->update_status( 'processing', __( 'payment completed', 'woocommerce' ) );


return wp_redirect($this->get_return_url( $order )); 

}



elseif ($order_status == 0) {
	$order = wc_get_order( $order_id );

$order->update_status( 'failed', __( 'payment not sucess some reason', 'woocommerce' ) );
$order->save();
return wp_redirect($this->get_return_url( $order ));


}
else{
$order = wc_get_order( $order_id );

$order->update_status( 'pending', __( 'Order pending some reason try again or contact support.', 'woocommerce' ) );
$order->save();
return wp_redirect($this->get_return_url( $order ));


}
	 	}
 	}
}

