<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!defined('DOING_AJAX') && isset($_REQUEST['connector']) && isset($_REQUEST['page']) && $_REQUEST['page'] == 'mobileassistant') {
    define('DOING_AJAX', true);
}

class MobileAssistantConnectorMain
{
    const PLUGIN_CODE = '51';
    const PLUGIN_VERSION = '2.2.4';

    const RESPONSE_CODE             = 'response_code';
    const RESPONSE_CODE_SUCCESS     = 'success';
    const ERROR_MESSAGE             = 'error_message';
    const RESPONSE_CODE_ERROR       = 'error';
    const DEBUG_MODE                = false;


    public $call_function;
    public $hash;
    protected $registration_id;
    protected $request;
    protected $device_unique_id;


    protected function check_is_woocommerce_activated()
    {
        include_once str_replace(site_url() . '/', ABSPATH, admin_url('includes/plugin.php'));

        $plugin_status = false;
        $path = str_replace('mobile-assistant-connector', '', __DIR__);
        $results = scandir($path, SCANDIR_SORT_ASCENDING);
        foreach ($results as $result) {
            if ($result === '.' || $result === '..') {
                continue;
            }

            $woocommerce_plugin_path = "$path/$result";
            if (is_dir($woocommerce_plugin_path)
                && (bool)preg_match("/^woocommerce((\-\d+)?(\.\d+)?(\.\d+)?)$/", $result) !== false
                && file_exists("$woocommerce_plugin_path/woocommerce.php")
                && (is_plugin_active("$result/woocommerce.php")
                    || in_array(
                        "$result/woocommerce.php",
                        apply_filters('active_plugins', get_option('active_plugins')),
                        true
                    )
                )
            ) {
                $plugin_status = true;
                break;
            }
        }

        if (!$plugin_status) {
            $this->generate_output('module_disabled');
        }
    }


    protected function validate_type($value, $type)
    {
        switch ($type) {
            case 'INT':
                $value = (int)$value;
                break;
            case 'FLOAT':
                $value = (float)$value;
                break;
            case 'STR':
                $value = str_replace(array("\r", "\n"), ' ', addslashes(htmlspecialchars(trim($value))));
                break;
            case 'STR_HTML':
                $value = addslashes(trim($value));
                break;
            default:
        }
        return $value;
    }


    protected function validate_types($array, $names)
    {
        foreach ($names as $name => $type) {
            if (isset($array[(string)$name])) {
                switch ($type) {
                    case 'INT':
                        $array[(string)$name] = (int)$array[(string)$name];
                        break;
                    case 'FLOAT':
                        $array[(string)$name] = (float)$array[(string)$name];
                        break;
                    case 'STR':
                        $array[(string)$name] = str_replace(
                                array("\r", "\n"),
                                ' ',
                                addslashes(htmlspecialchars(trim(urldecode($array[(string)$name]))))
                            );
                        break;
                    case 'STR_HTML':
                        $array[(string)$name] = addslashes(trim(urldecode($array[(string)$name])));
                        break;
                    case 'STR_ARR':
                        $check_arr = array();
                        if (!empty($array[(string)$name]) && is_array($array[(string)$name])) {
                            foreach ($array[(string)$name] as $key => $value) {
                                $check_arr[] = filter_var($value, FILTER_SANITIZE_STRING);
                            }
                        }
                        $array[(string)$name] = $check_arr;
                        break;
                    case 'BOOL':
                        $array[(string)$name] = (bool)$array[(string)$name];
                        break;
                    default:
                        $array[(string)$name] = '';
                }
            } else {
                $array[(string)$name] = '';
            }
        }
        return $array;
    }


    protected function run_self_test()
    {
        $html = '<h2>Mobile Assistant Connector (v. ' . self::PLUGIN_VERSION . ')</h2>
			<div style="margin-top: 15px; font-size: 13px;">Mobile Assistant Connector by <a href="http://emagicone.com" target="_blank"
			style="color: #15428B">eMagicOne</a></div>';

        die($html);
    }

    protected function is_v3()
    {
        return version_compare(WooCommerce::instance()->version, '3.0', '>=');
    }

    public function get_qr_code()
    {
        global $wpdb;

        $hash = $this->hash;

        $user = $wpdb->get_results(
            $wpdb->prepare("SELECT `username`, `password` FROM `{$wpdb->prefix}mobileassistant_users` WHERE `qr_code_hash` = %s AND `status` = 1 LIMIT 1",  $hash),
            ARRAY_A
        );

        if ($user) {
            $user = array_shift($user);
            $site_url = get_site_url();
            $config['url'] = get_site_url();
            $config['url'] = str_replace('http://', '', $config['url']);
            $config['url'] = str_replace('https://', '', $config['url']);

            $config['login'] = $user['username'];
            $config['password'] = $user['password'];

            $data_to_qr = base64_encode(json_encode($config));

            echo '<html><head>
            <meta http-equiv="Pragma" content="no-cache">
            <title>QR-code for WooCommerce Mobile Assistant</title>';
            echo wp_get_script_tag(
                array(
                    'src'      => plugins_url('js/qrcode.min.js', __FILE__),
                    'nomodule' => false,
                )
            );
            echo '<style media="screen" type="text/css">
                    img {
                        margin:  auto;
                    }
                </style>
            </head>
                <body>

                    <table width="100%" style="padding: 30px;">
                    <tr><td style="text-align: center;"><h3>Mobile Assistant Connector (v. ' . sanitize_textarea_field(self::PLUGIN_VERSION) . ') </h3></td></tr>
                    <tr><td id="mobassistantconnector_qrcode_img" ></td></tr></table>
                    <input type="hidden" id="mobassistantconnector_base_url_hidden" value="">
                </body>
                <script type="text/javascript">
                        (function() {
                            var qrcode = new QRCode(document.getElementById("mobassistantconnector_qrcode_img"), {
                                width : 300,
                                height : 300
                            });

                            qrcode.makeCode("' . $data_to_qr . '");
                })();
                document.getElementById("mobassistantconnector_base_url_hidden").value="' . $site_url . '"
                </script>
            </html>';
            die();
        } else {
            return 'auth_error';
        }

        return '';
    }


    public function my_json_encode($data)
    {
        if (is_array($data) || is_object($data)) {
            $islist = is_array($data) && (empty($data) || array_keys($data) === range(0, count($data) - 1));

            if ($islist) {
                $json = '[' . implode(',', array_map('my_json_encode', $data)) . ']';
            } else {
                $items = Array();
                foreach ($data as $key => $value) {
                    $items[] = $this->my_json_encode((string)$key) . ':' . $this->my_json_encode($value);
                }
                $json = '{' . implode(',', $items) . '}';
            }
        } elseif (is_string($data)) {
            // Escape non-printable or Non-ASCII characters.
            $string = '"' . addcslashes($data, "\\\"\n\r\t/" . chr(8) . chr(12)) . '"';
            $json = '';
            $len = strlen($string);
            // Convert UTF-8 to Hexadecimal Codepoints.
            for ($i = 0; $i < $len; $i++) {

                $char = $string[$i];
                $c1 = ord($char);

                // Single byte;
                if ($c1 < 128) {
                    $json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1);
                    continue;
                }

                // Double byte
                $c2 = ord($string[++$i]);
                if (($c1 & 32) === 0) {
                    $json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128);
                    continue;
                }

                // Triple
                $c3 = ord($string[++$i]);
                if (($c1 & 16) === 0) {
                    $json .= sprintf("\\u%04x", (($c1 - 224) << 12) + (($c2 - 128) << 6) + ($c3 - 128));
                    continue;
                }

                // Quadruple
                $c4 = ord($string[++$i]);
                if (($c1 & 8) === 0) {
                    $u = (($c1 & 15) << 2) + (($c2 >> 4) & 3) - 1;

                    $w1 = (54 << 10) + ($u << 6) + (($c2 & 15) << 2) + (($c3 >> 4) & 3);
                    $w2 = (55 << 10) + (($c3 & 15) << 6) + ($c4 - 128);
                    $json .= sprintf("\\u%04x\\u%04x", $w1, $w2);
                }
            }
        } else {
            // int, floats, bools, null
            $json = strtolower(var_export($data, true));
        }
        return $json;
    }
}


function mobassist_get_order_status_name($order_id, $post_status)
{
    if (function_exists('wc_get_order_status_name')) {
        return wc_get_order_status_name($post_status);
    }

    if ($order_id > 0) {
        $terms = wp_get_object_terms($order_id, 'shop_order_status', array('fields' => 'slugs'));
        $status = isset($terms[0]) ? $terms[0] : apply_filters('woocommerce_default_order_status', 'pending');
    } else {
        $status = $post_status;
    }


    $statuses = mobassist_get_order_statuses();

    return $statuses[$status];
}

function mobassist_get_order_statuses()
{
    if (function_exists('wc_get_order_statuses')) {
        return wc_get_order_statuses();
    }

    $statuses = (array)get_terms('shop_order_status', array('hide_empty' => 0, 'orderby' => 'id'));

    $statuses_arr = array();
    foreach ($statuses as $status) {
        $statuses_arr[$status->slug] = $status->name;
    }

    return $statuses_arr;
}


//== PUSH ===========================================================================

function mobassist_push_new_order($order_id)
{
    $order_id = mobassist_validate_post($order_id, 'shop_order');
    if (!$order_id || empty($order_id)) {
        return false;
    }

    $order = new WC_Order($order_id);

    sendOrderPushMessage($order, PUSH_TYPE_NEW_ORDER);
}


function mobassist_push_change_status($order_id)
{
    $order_id = mobassist_validate_post($order_id, 'shop_order');
    if (!$order_id || empty($order_id)) {
        return false;
    }

    $order = new WC_Order($order_id);

    sendOrderPushMessage($order, PUSH_TYPE_CHANGE_ORDER_STATUS);
}

function mobassist_push_new_customer($customer_id)
{
    $customer_id = mobassist_validate_post($customer_id, 'customer');

    if (!$customer_id || empty($customer_id)) {
        return false;
    }

    $customer = new WP_User($customer_id);

    sendCustomerPushMessage($customer);
}

function sendOrderPushMessage($order, $type)
{
    $data = array('type' => $type);

    $old_type = $type;
    if ($type == PUSH_TYPE_CHANGE_ORDER_STATUS) {
        $old_type = 'order_changed';
        $data['status'] = version_compare(WooCommerce::instance()->version, '3.0', '>=')
            ? 'wc-' . $order->get_status()
            : $order->post_status;
    }

    $push_devices = getPushDevices($data);

    if (!$push_devices || count($push_devices) <= 0) {
        return;
    }

    $url = get_site_url();
    $url = str_replace(array('http://', 'https://'), '', $url);

    foreach ($push_devices as $push_device) {
        if (!empty($push_device['registration_id'])) {
            $currency_code = get_woocommerce_currency();

            if (function_exists('wc_get_order_status_name')) {
                $order_status_code = version_compare(WooCommerce::instance()->version, '3.0', '>=')
                    ? 'wc-' . $order->get_status()
                    : $order->post_status;
            } else {
                $order_status_code = $order->status;
            }

            $order_id = version_compare(WooCommerce::instance()->version, '3.0', '>=')
                ? $order->get_id()
                : $order->id;

            $billing_first_name = version_compare(WooCommerce::instance()->version, '3.0', '>=')
                ? $order->get_billing_first_name()
                : $order->billing_first_name;

            $billing_last_name = version_compare(WooCommerce::instance()->version, '3.0', '>=')
                ? $order->get_billing_last_name()
                : $order->billing_last_name;

            $billing_email = version_compare(WooCommerce::instance()->version, '3.0', '>=')
                ? $order->get_billing_email()
                : $order->billing_email;

            $order_status = version_compare(WooCommerce::instance()->version, '3.0', '>=')
                ? $order->get_status()
                : $order->status;

            $order_items = $order->get_items();
            $order_items_count = count($order_items);

            $order_date = version_compare(WooCommerce::instance()->version, '3.0', '>=')
                ? $order->get_date_created() ? $order->get_date_created()->getOffsetTimestamp() : 0
                : $order->order_date;
            $order_date = ((int)strtotime($order_date)) * 1000;

            $message = array(
                'push_notif_type'       => $old_type, // for old app
                'customer_name'         => $billing_first_name . ' ' . $billing_last_name, // for old app
                'email'                 => $billing_email, // for old app
                'new_status'            => mobassist_get_order_status_name($order_id, $order_status), // for old app
                'new_status_code'       => $order_status_code, // for old app
                'store_url'             => $url, // for old app
                'app_connection_id'     => (string)$push_device['app_connection_id'],
                'order_id'              => (int)$order_id,
                'customer_id'           =>  $order->get_user_id(),
                'customer_email'        => (string)$billing_email,
                'customer_first_name'   => (string)$billing_first_name,
                'customer_last_name'    => (string)$billing_last_name,
                'status_id'             => $order_status_code,
                'order_status_name'     => $order_status,
                'total'                 => round($order->get_total(), 6),
                'formatted_total'       => (string)mobassist_nice_price($order->get_total(), $currency_code, false, false, true),
                'date_add'              => $order_date,
                'products_count'        => $order_items_count
            );

            sendFCM($push_device['setting_id'], $push_device['registration_id'], $message);
        }
    }
}

function sendCustomerPushMessage($customer)
{
    $type = PUSH_TYPE_NEW_CUSTOMER;
    $data = array('type' => $type);

    $push_devices = getPushDevices($data);

    if (!$push_devices || count($push_devices) <= 0) {
        return;
    }

    $url = get_site_url();
    $url = str_replace(array('http://', 'https://'), '', $url);

    $first_name = trim($customer->first_name);
    $last_name = trim($customer->last_name);
    if (empty($first_name) && empty($last_name)) {
        $first_name = trim($customer->data->display_name);
    }

    foreach ($push_devices as $push_device) {
        if (!empty($push_device['registration_id'])) {
            $message = array(
                'push_notif_type'           => $type,
                'customer_name'             => trim($first_name .  ' ' . $last_name), // for old app
                'store_url'                 => $url, // for old app
                'app_connection_id'         => (string)$push_device['app_connection_id'],
                'customer_id'               => (int)$customer->ID,
                'email'                     => (string)$customer->user_email,
                'first_name'                => (string)$first_name,
                'last_name'                 => (string)$last_name,
                'date_add'                  => (int)strtotime($customer->user_registered),
                'orders_count'              => 0,
                'orders_total'              => 0,
                'formatted_orders_total'    => 0
            );

            sendFCM($push_device['setting_id'], $push_device['registration_id'], $message);
        }
    }
}

function sendFCM($settingId, $registrationId, $message)
{
    $data = array(
        'to'            => $registrationId,
        // If we ever will work with notification title/body.
        // Should be fixed firstly from firebase side - https://github.com/firebase/quickstart-android/issues/4
        // 'notification'  => array(
        //     'body'          => $notificationTitle,
        //     'title'         => $notificationBody,
        //     'icon'          => 'ic_launcher',
        //     'sound'         => 'default',
        //     'badge'         => '1'
        // ),
        'data'          => array('message' => wp_json_encode($message)),
        'priority'      => 'high'
    );
    $data = wp_json_encode($data);

    $url = 'https://fcm.googleapis.com/fcm/send';

    $options = [
        'body'        => $data,
        'headers'     => [
            'Authorization' => 'key=AAAAbmFVl48:APA91bEIhDft-aWShrMCsv4Vhfa8BTdZrkc4WcjH31zck2j-'
                . 'mU6gNPzZNUe9mxYqislR2iYo5oCgMdiSzCAHlbPn1L15MIXi8hrDqmSP8EGvGYGCWUyaB7x3Nt_XQUza4sX7Pl4dY6Tv',
            'Content-Type' => 'application/json',
        ],
        'sslverify'   => false,
        'data_format' => 'body',
    ];

    $result = wp_remote_post($url, $options);
    $info = wp_remote_get($url);

    onResponse($settingId, $result, $info);
}

function onResponse($settingId, $response, $info)
{
    $code = $info !== null && isset($info['http_code']) ? $info['http_code'] : 0;

    $codeGroup = (int)($code/100);
    if ($codeGroup === 5) {
        mobassist_log_me('PUSH RESPONSE: code: ' . $code . ' :: GCM server not available');
        return;
    }

    if ($code !== 200) {
        mobassist_log_me('PUSH RESPONSE: code: ' . $code);
        return;
    }

    if (!$response || strlen(trim($response)) == null) {
        mobassist_log_me('PUSH RESPONSE: null response');
        return;
    }

    $json = array();
    if ($response) {
        $json = json_decode($response, true);
        if (!$json) {
            mobassist_log_me('PUSH RESPONSE: json decode error');
        }
    }

    $failure = isset($json['failure']) ? $json['failure'] : null;
    $canonicalIds = isset($json['canonical_ids']) ? $json['canonical_ids'] : null;

    if ($failure || $canonicalIds) {
        $results = isset($json['results']) ? $json['results'] : array();
        foreach ($results as $result) {
            $newRegId = isset($result['registration_id']) ? $result['registration_id'] : null;
            $error = isset($result['error']) ? $result['error'] : null;
            if ($newRegId) {
                updatePushRegId($settingId, $newRegId);

            } elseif ($error) {
                if ($error == 'NotRegistered' || $error == 'InvalidRegistration') {
                    deletePushRegId($settingId);
                }
                mobassist_log_me('PUSH RESPONSE: error: ' . $error);
            }
        }
    }
}


function updatePushRegId($setting_id, $new_reg_id)
{
    global $wpdb;

    $sql = "UPDATE `{$wpdb->prefix}mobileassistant_push_settings` SET registration_id = '%s' WHERE setting_id = '%d'";
    $sql = sprintf($sql, $new_reg_id, $setting_id);
    $wpdb->query($sql);
}


function deletePushRegId($setting_id)
{
    global $wpdb;

    $sql = "DELETE FROM `{$wpdb->prefix}mobileassistant_push_settings`
            WHERE setting_id = '%d'";
    $sql = sprintf($sql, $setting_id);
    $wpdb->query($sql);
}


function getPushDevices($data = array())
{
    global $wpdb;

    $sql = "SELECT ms.`setting_id`, ms.`registration_id`, ms.`app_connection_id`, ms.`push_currency_code`,
                   ms.`push_order_statuses`, ms.`not_notified_order_statuses_ids`
            FROM `{$wpdb->prefix}mobileassistant_push_settings` ms
              LEFT JOIN `{$wpdb->prefix}mobileassistant_users` mu ON ms.`user_id` = mu.`user_id`
    ";

    $query_where[] = ' ms.`status` = 1';
    $query_where[] = ' mu.`status` = 1 OR mu.`status` IS NULL';

    if (!empty($query_where)) {
        $sql .= ' WHERE ' . implode(' AND ', $query_where);
    }

    $results = $wpdb->get_results($sql, ARRAY_A);


    switch ($data['type']) {
        case PUSH_TYPE_NEW_ORDER:
            $query_where[] = " ms.`push_new_order` = '1' ";
            break;

        case PUSH_TYPE_CHANGE_ORDER_STATUS:
            $query_where[] = sprintf(
                " (ms.`push_order_statuses` = '%s' OR ms.`push_order_statuses` LIKE '%%|%s' OR ms.`push_order_statuses` LIKE '%s|%%' OR ms.`push_order_statuses` LIKE '%%|%s|%%' OR ms.`push_order_statuses` = '-1') ",
                $data['status'], $data['status'], $data['status'], $data['status']
            );
            break;

        case PUSH_TYPE_NEW_CUSTOMER:
            $query_where[] = " ms.`push_new_customer` = '1' ";
            break;

        default:
            return false;
    }

    return $results;
}

function mobassist_validate_post($id, $type)
{
    $id = absint($id);

    // validate ID
    if (empty($id)) {
        return false;
    }

    // only custom post types have per-post type/permission checks
    if ('customer' !== $type) {

        $post = get_post($id);
        if(!$post) return false;

        // for checking permissions, product variations are the same as the product post type
        $post_type = ('product_variation' === $post->post_type) ? 'product' : $post->post_type;

        // validate post type
        if ($type !== $post_type) {
            return false;
        }
    }

    if ('customer' == $type) {
        $customer = new WP_User($id);

        if (0 === $customer->ID) {
            return false;
        }
    }

    return $id;
}

function mobassist_nice_count($n)
{
    return mobassist_nice_price($n, '', true);
}

function mobassist_nice_price($n, $currency = false, $is_count = false, $full_price = false, $notification = false)
{
    if ($n == 0 || !$full_price) {
        $n = (float)$n;
    }

    if(!$currency) $currency = get_woocommerce_currency();

    if ($n < 0) {
        $n = $n * -1;
        $negative = true;
    } else {
        $negative = false;
    }

    $final_number = trim($n);
    $final_number = str_replace(' ', '', $final_number);
    $suf = '';

    if (!$full_price) {
        if ($n > 1000000000000000) {
            $final_number = round($n / 1000000000000000, 2);
            $suf = 'P';

        } else {
            if ($n > 1000000000000) {
                $final_number = round($n / 1000000000000, 2);
                $suf = 'T';

            } else {
                if ($n > 1000000000) {
                    $final_number = round($n / 1000000000, 2);
                    $suf = 'G';

                } else {
                    if ($n > 1000000) {
                        $final_number = round($n / 1000000, 2);
                        $suf = 'M';

                    } else {
                        if ($n > 10000 && $is_count) {
                            $final_number = number_format($n, 0, '', ' ');
                        }
                    }
                }
            }
        }
    }


    if ($is_count) {
        $final_number = ($negative ? '-' : '') . (int)$final_number . $suf;
    } else {
        $num_decimals = absint(get_option('woocommerce_price_num_decimals'));
        $currency_symbol = get_woocommerce_currency_symbol($currency);
        if ($notification) {
//            $currency_symbol = html_entity_decode(htmlspecialchars_decode($currency_symbol));
            $currency_symbol = html_entity_decode($currency_symbol);
        }

        $decimal_sep = wp_specialchars_decode(stripslashes(get_option('woocommerce_price_decimal_sep')), ENT_QUOTES);
        $thousands_sep = wp_specialchars_decode(stripslashes(get_option('woocommerce_price_thousand_sep')), ENT_QUOTES);

        $final_number = number_format($final_number, $num_decimals, $decimal_sep, $thousands_sep);

        $final_number = $final_number . $suf;
        $final_number = ($negative ? '-' : '') . sprintf(get_woocommerce_price_format(), $currency_symbol, $final_number);
        $final_number = str_replace("&nbsp;", ' ', $final_number);
    }

    return $final_number;
}

function mobassist_log_me($message)
{
    if (MobileAssistantConnectorMain::DEBUG_MODE === true) {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }

        error_log('Mobile Assistant LOG: ' . $message);
    }
}

function get_image_url($attachment_id, $size = 'thumbnail')
{
    if($attachment_id <= 0) return "";

    $image_details = wp_get_attachment_image_src($attachment_id, $size);
    $base_ulr = get_site_url();

    return strpos($image_details[0], 'http://') === false && strpos($image_details[0], 'https://') === false
        ? $base_ulr . $image_details[0]
        : $image_details[0];
}