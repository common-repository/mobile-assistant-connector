<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!defined('DOING_AJAX') && isset($_REQUEST['connector']) && isset($_REQUEST['page']) && $_REQUEST['page'] == 'mobileassistant') {
    define('DOING_AJAX', true);
}

class MobileAssistantConnector extends MobileAssistantConnectorMain
{
    protected $sDBHost = '';
    protected $sDBUser = '';
    protected $sDBPwd = '';
    protected $sDBName = '';
    protected $sDBPrefix = '';
    protected $site_url = '';
    protected $CartType = -1;
    protected $status_list_hide = array('auto-draft', 'trash');

    private $session_key;
    private $device_name;
    private $show;
    private $page;
    private $search_order_id;
    private $orders_from;
    private $orders_to;
    private $customers_from;
    private $customers_to;
    private $graph_from;
    private $graph_to;
    private $stats_from;
    private $stats_to;
    private $products_to;
    private $products_from;
    private $order_id;
    private $user_id;
    private $params;
    private $val;
    private $search_val;
    private $statuses;
    private $sort_by;
    private $order_by;
    private $group_by_product_id;
    private $without_thumbnails;
    private $only_items;
    private $product_id;
    private $get_statuses;
    private $cust_with_orders;
    private $data_for_widget;
    private $registration_id_old;
    private $api_key;
    private $push_new_order;
    private $push_order_statuses;
    private $push_new_customer;
    private $app_connection_id;
    private $action;
    private $custom_period;
    private $new_status;
    private $change_order_status_comment;
    private $account_email;
    private $check_permission;



    public function __construct()
    {
        global $wpdb;

        if (!ini_get('date.timezone') || ini_get('date.timezone') == '') {
            @date_default_timezone_set(@date_default_timezone_get());
        }

        Mobassistantconnector_Access::clear_old_data();
        $this->check_is_woocommerce_activated();

        $wpdb->query('SET SQL_BIG_SELECTS=1;');

        $this->request = json_decode(
            (fopen('php://input', 'rb') !== false
                ? file_get_contents('php://input')
                : '{}'
            ),
            true
        );
        if(!$this->request) $this->request = array();
        $this->request = array_merge ($this->request, $_REQUEST);

        if (isset($this->request['call_function'])) {
            $this->call_function = $this->validate_type($this->request['call_function'], 'STR');
        }

        if (isset($this->request['hash'])) {
            $this->hash = $this->validate_type($this->request['hash'], 'STR');
        }
        if (isset($this->request['key'])) {
            $this->session_key = $this->validate_type($this->request['key'], 'STR');
        }
        if (isset($this->request['registration_id'])) {
            $this->registration_id = $this->validate_type($this->request['registration_id'], 'STR');
        }
        if (isset($this->request['device_unique_id'])) {
            $this->device_unique_id = $this->validate_type($this->request['device_unique_id'], 'STR');
        }

        if (empty($this->call_function)) {
            $this->run_self_test();
        }

//        $this->map_push_notification_to_device();
//        $this->update_device_last_activity();

        if ($this->call_function == 'get_qr_code' && $this->hash) {
            $this->get_qr_code();
        }

        if ($this->call_function == 'get_version') {
            $this->get_version();
        }

        if ($this->hash) {
            $key = Mobassistantconnector_Access::get_session_key($this->hash);

            if (!$key) {
                $this->generate_output('auth_error');
            }

            $this->generate_output(array('session_key' => $key));
        } elseif ($this->session_key || $this->session_key === '') {
            if (!Mobassistantconnector_Access::check_session_key($this->session_key)) {
                $this->generate_output(array('bad_session_key' => true));
            }
        } else {
            Mobassistantconnector_Access::add_failed_attempt();
            $this->generate_output('auth_error');
        }

        $params = $this->validate_types($this->request, array(
            'show' => 'INT',
            'page' => 'INT',
            'search_order_id' => 'STR',
            'orders_from' => 'STR',
            'orders_to' => 'STR',
            'customers_from' => 'STR',
            'customers_to' => 'STR',
            'date_from' => 'STR',
            'date_to' => 'STR',
            'graph_from' => 'STR',
            'graph_to' => 'STR',
            'stats_from' => 'STR',
            'stats_to' => 'STR',
            'products_to' => 'STR',
            'products_from' => 'STR',
            'order_id' => 'INT',
            'user_id' => 'INT',
            'params' => 'STR',
            'val' => 'STR',
            'search_val' => 'STR',
            'statuses' => 'STR',
            'sort_by' => 'STR',
            'order_by' => 'STR',
            'group_by_product_id' => 'STR',
            'without_thumbnails' => 'STR',
            'only_items' => 'INT',
            'last_order_id' => 'STR',
            'product_id' => 'INT',
            'get_statuses' => 'INT',
            'cust_with_orders' => 'INT',
            'data_for_widget' => 'INT',
            'registration_id' => 'STR',
            'registration_id_old' => 'STR',
            'device_unique_id' => 'STR',
            'api_key' => 'STR',
            'push_new_order' => 'INT',
            'push_order_statuses' => 'STR',
            'push_new_customer' => 'INT',
            'app_connection_id' => 'STR',
            'action' => 'STR',
            'carrier_code' => 'STR',
            'custom_period' => 'INT',
            'store_id' => 'STR',
            'new_status' => 'STR',
            'notify_customer' => 'INT',
            'currency_code' => 'STR',
            'change_order_status_comment' => 'STR',
            'account_email' => 'STR',
            'check_permission' => 'STR'
        )
        );

        foreach ($params as $k => $value) {
            $this->{$k} = $value;
        }
        $this->currency = get_woocommerce_currency();

        if ($this->call_function == 'test_config') {
            $result = array('test' => 1);
            
            if (isset($this->check_permission) && !empty($this->check_permission)) {
                $this->call_function = $this->check_permission;
                $result['permission_granted'] = $this->_is_action_allowed() ? '1' : '0';
            }

            $this->generate_output($result);
        }

        $this->_check_allowed_actions();

        $this->site_url = get_site_url();
    }

    public function generate_output($data)
    {
        $add_connector_version = false;
        if (is_array($data)
            && $data != 'auth_error'
            && $data != 'connection_error'
            && $data != 'old_module'
            && in_array(
                $this->call_function,
                array(
                    'test_config',
                    'get_store_title',
                    'get_store_stats',
                    'get_data_graphs',
                    'get_version'
                )
            )
        ) {
            $add_connector_version = true;
        }

        function reset_null($item, $key)
        {
            if (empty($item) && $item != 0) {
                $item = '';
            }
            if (!is_array($item) && !is_object($item)) {
                $item = trim($item);
            }
        }

        if (!is_array($data)) {
            $data = array($data);
        } else {
            $data['module_response'] = '1';
        }

        if (is_array($data)) {
            array_walk_recursive($data, 'reset_null');
        }

        if ($add_connector_version) {
            $data['module_version'] = self::PLUGIN_CODE;
        }

        $data = wp_json_encode($data);

        status_header(200);
        die($data);
    }

    private function get_version()
    {
        $session_key = '';

        if ($this->hash) {
            $user_data = Mobassistantconnector_Access::check_auth($this->hash);
            if ($user_data) {
                if ($this->session_key) {
                    if (Mobassistantconnector_Access::check_session_key($this->session_key, $user_data['user_id'])) {
                        $session_key = $this->session_key;
                    } else {
                        $session_key = Mobassistantconnector_Access::get_session_key($this->hash, $user_data['user_id']);
                    }
                } else {
                    $session_key = Mobassistantconnector_Access::get_session_key($this->hash, $user_data['user_id']);
                }
            } else {
                $this->generate_output('auth_error');
            }
        } elseif ($this->session_key && Mobassistantconnector_Access::check_session_key($this->session_key)) {
            $session_key = $this->session_key;
        }

        $this->generate_output(array('session_key' => $session_key));
    }

    private function _is_action_allowed()
    {
        $is_allowed = false;

        $allowed_functions_always = array(
            'run_self_test',
            'get_stores',
            'get_currencies',
            'get_store_title',
            'get_orders_statuses',
            'get_carriers',
            'push_notification_settings',
            'get_qr_code',
            'get_order_invoice_pdf',
        );

        if (in_array($this->call_function, $allowed_functions_always)) {
            return true;
        }

        $user_allowed_actions = Mobassistantconnector_Access::get_allowed_actions_by_session_key($this->session_key);

        $all_actions = Mobassistantconnector_Functions::get_default_actions();

        if ($this->call_function == 'set_order_action') {
            if ($this->action == 'change_status' && in_array('update_order_status', $user_allowed_actions)) {
                $is_allowed = true;
            } elseif ($this->action == 'update_track_number'
                && in_array('update_order_tracking_number', $user_allowed_actions)) {
                $is_allowed = true;
            }
        } else {
            foreach ($all_actions as $action_group) {
                foreach ($action_group as $action) {
                    if (in_array($this->call_function, $action['functions'])) {
                        if (in_array($action['code'], $user_allowed_actions)) {
                            $is_allowed = true;
                        }

                        break 2;
                    }
                }
            }
        }

        return $is_allowed;
    }

    private function _check_allowed_actions()
    {
        if (!$this->_is_action_allowed()) {
            $this->generate_output('action_forbidden');
        }
    }

    public function get_order_pdf()
    {
        global $wpo_wcpdf;

        if (!in_array(
            'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php',
            apply_filters('active_plugins', get_option('active_plugins'))
        )
        ) {
            return;
        }
        // Load main plugin class
        if (!is_object($wpo_wcpdf)) {
            $this->generate_output('No PDF Invoices Packing Slips plugin installed!');
        }


        $pdf_data = $wpo_wcpdf->export->get_pdf('invoice', (array)$this->order_id);
        if (!$pdf_data) {
            // something went wrong, continue trying with other documents
            $this->generate_output('Can\'t generate PDF Invoice!');
        }

        header('Content-type: application/pdf');

        // output PDF data
        echo $pdf_data;
        exit;
    }

    public function get_currencies()
    {
        $all_currencies = array();

        $currency_code_options = get_woocommerce_currencies();

        foreach ($currency_code_options as $code => $name) {
            $all_currencies[] = array('code' => $code, 'name' => $name);
        }

        return $all_currencies;
    }

    public function get_store_title()
    {
        $title = get_option('blogname');

        return array('test' => 1, 'title' => $title);
    }

    public function get_store_stats()
    {
        $data_graphs = '';
        $order_status_stats = array();
        $store_stats = array(
            'count_orders' => '0',
            'total_sales' => '0',
            'count_customers' => '0',
            'count_products' => '0',
            'last_order_id' => '0',
            'new_orders' => '0'
        );
        $today = date('Y-m-d');
        $date_from = $date_to = $today;

        $data = array();

        if (!empty($this->stats_from)) {
            $date_from = $this->stats_from;
        }

        if (!empty($this->stats_to)) {
            $date_to = $this->stats_to;
        }

        if (!empty($this->custom_period) && strlen($this->custom_period) > 0) {
            $custom_period = $this->get_custom_period($this->custom_period);

            $date_from = $custom_period['start_date'];
            $date_to = $custom_period['end_date'];
        }

        if (!empty($date_from)) {
            $data['date_from'] = $date_from . ' 00:00:00';
        }

        if (!empty($date_to)) {
            $data['date_to'] = $date_to . ' 23:59:59';
        }

        if (!empty($this->statuses)) {
            $data['statuses'] = $this->get_filter_statuses($this->statuses);
        }

        $orders_stats = $this->_get_total_orders_i_products($data);
        $store_stats = array_merge($store_stats, $orders_stats);

        $customers_stats = $this->_get_total_customers($data);
        $store_stats = array_merge($store_stats, $customers_stats);


        if (!isset($this->data_for_widget) || empty($this->data_for_widget) || $this->data_for_widget != 1) {
            $data_graphs = $this->get_data_graphs();
            $order_status_stats = $this->get_status_stats();
        }

        $result = array_merge(
            $store_stats, array('data_graphs' => $data_graphs),
            array('order_status_stats' => $order_status_stats)
        );

        return $result;
    }

    protected function get_custom_period($period)
    {
        $custom_period = array('start_date' => '', 'end_date' => '');
        $format = 'm/d/Y';

        switch ($period) {
            //@todo summertime time manipulations should be redo with DateTime & DatePeriod objects
            case 0: //3 days
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date('m'), date('d'), date('Y')));
                break;

            case 1: //7 days
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date('m'), date('d') - 6, date('Y')));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date('m'), date('d'), date('Y')));
                break;

            case 2: //Prev week
//                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date("n"), date("j") - 6, date("Y")) - ((date("N")) * 3600 * 24));
//                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date("n"), date("j"), date("Y")) - ((date("N")) * 3600 * 24));
                break;

            case 3: //Prev month
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
                $custom_period['end_date'] = date(
                    $format,
                    mktime(23, 59, 59, date('m'), date('d') - date('j'), date('Y'))
                );
                break;

            case 4: //This quarter
                $m = date('n');
                $start_m = 1;
                $end_m = 3;

                if ($m <= 3) {
                    $start_m = 1;
                    $end_m = 3;
                } else {
                    if ($m >= 4 && $m <= 6) {
                        $start_m = 4;
                        $end_m = 6;
                    } else {
                        if ($m >= 7 && $m <= 9) {
                            $start_m = 7;
                            $end_m = 9;
                        } else {
                            if ($m >= 10) {
                                $start_m = 10;
                                $end_m = 12;
                            }
                        }
                    }
                }

                $custom_period['start_date'] = date($format, mktime(0, 0, 0, $start_m, 1, date('Y')));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, $end_m + 1, date(1) - 1, date('Y')));
                break;

            case 5: //This year
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date(1), date(1), date('Y')));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date(1), date(1) - 1, date('Y') + 1));
                break;

            case 6: //Last year
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date(1), date(1), date('Y') - 1));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date(1), date(1) - 1, date('Y')));
                break;

            case 7: //All time
                $custom_period['start_date'] = null;
                $custom_period['end_date'] = null;
                break;

            case 8: //Last quarter
                $m = date('n');
                $start_m = 1;
                $end_m = 3;
                $year_offset = 0;

                if ($m <= 3) {
                    $start_m = 10;
                    $end_m = 12;
                    $year_offset = -1;
                } else {
                    if ($m >= 4 && $m <= 6) {
                        $start_m = 1;
                        $end_m = 3;
                    } else {
                        if ($m >= 7 && $m <= 9) {
                            $start_m = 4;
                            $end_m = 6;
                        } else {
                            if ($m >= 10) {
                                $start_m = 7;
                                $end_m = 9;
                            }
                        }
                    }
                }

                $custom_period['start_date'] = date($format, mktime(0, 0, 0, $start_m, 1, date('Y') + $year_offset));
                $custom_period['end_date'] = date(
                    $format,
                    mktime(23, 59, 59, $end_m + 1, date(1) + $year_offset, date('Y') + $year_offset)
                );
                break;
        }

        return $custom_period;
    }

    private function get_filter_statuses($statuses)
    {
        $statuses = explode('|', $statuses);
        if (!empty($statuses)) {
            $stat = array();
            foreach ($statuses as $status) {
                if ($status != '') {
                    $stat[] = $status;
                }
            }
            $parse_statuses = implode("','", $stat);
            return $parse_statuses;
        }

        return $statuses;
    }

    private function _get_total_orders_i_products($data)
    {
        global $wpdb;
        $query_where_parts = array();

        $query_orders = "SELECT
              COUNT(posts.ID) AS count_orders,
              SUM(meta_order_total.meta_value) AS total_sales
            FROM `{$wpdb->posts}` AS posts
            LEFT JOIN `{$wpdb->postmeta}` AS meta_order_total ON meta_order_total.post_id = posts.ID AND meta_order_total.meta_key = '_order_total'";

        $query_products = "SELECT
              SUM(meta_items_qty.meta_value) AS count_products
            FROM `{$wpdb->posts}` AS posts
            LEFT JOIN `{$wpdb->prefix}woocommerce_order_items` AS order_items ON order_items.order_id = posts.ID AND order_items.order_item_type = 'line_item'
            LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_items_qty ON meta_items_qty.order_item_id = order_items.order_item_id AND meta_items_qty.meta_key = '_qty'";

        if (isset($this->show_all_customers) && !$this->show_all_customers) {
            $query_for_registered_customers = " LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts.ID = meta.post_id AND meta.meta_key = '_customer_user' 
                               LEFT JOIN `{$wpdb->users}` AS c ON c.ID = meta.meta_value
                               LEFT JOIN `{$wpdb->usermeta}` AS cap ON cap.user_id = c.ID ";
            $query_orders .= $query_for_registered_customers;
            $query_products .= $query_for_registered_customers;
            $query_where_parts[] = " (cap.meta_key = '{$wpdb->prefix}capabilities' AND cap.meta_value LIKE '%customer%') ";
        }

        if (!function_exists('wc_get_order_status_name')) {
            $query = " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts.ID
                            AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                        LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
            $query_orders .= $query;
            $query_products .= $query;
        }

        $query_where_parts[] = " posts.post_type = 'shop_order' ";

        if (isset($data['date_from'])) {
            $query_where_parts[] = sprintf(
                " UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) >= '%d'",
                strtotime($data['date_from'])
            );
        }

        if (isset($data['date_to'])) {
            $query_where_parts[] = sprintf(
                " UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) <= '%d'",
                strtotime($data['date_to'])
            );
        }

        if (isset($data['statuses'])) {
            if (function_exists('wc_get_order_status_name')) {
                $query_where_parts[] = sprintf(
                    " posts.post_status IN ('%s')",
                    $this->get_filter_statuses($data['statuses'])
                );
            } else {
                $query_where_parts[] = sprintf(
                    " status_terms.slug IN ('%s')",
                    $this->get_filter_statuses($data['statuses'])
                );
            }
        }

        if (!empty($this->status_list_hide)) {
            $query_where_parts[] = " posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        if (!empty($query_where_parts)) {
            $query_orders .= ' WHERE ' . implode(' AND ', $query_where_parts);
            $query_products .= ' WHERE ' . implode(' AND ', $query_where_parts);
        }

        $orders_stat = $wpdb->get_results($query_orders, ARRAY_A);
        $orders_stat = array_shift($orders_stat);

        $products_stat = $wpdb->get_results($query_products, ARRAY_A);
        $products_stat = array_shift($products_stat);

        $totals['count_orders'] = mobassist_nice_count($orders_stat['count_orders']);
        $totals['total_sales'] = mobassist_nice_price($orders_stat['total_sales'], $this->currency, false, true);
        $totals['count_products'] = mobassist_nice_count($products_stat['count_products']);

        return $totals;
    }

    private function _get_total_customers($data)
    {
        global $wpdb;

        $query = "SELECT c.user_email AS customer_email FROM `{$wpdb->users}` AS c 
          LEFT JOIN `{$wpdb->usermeta}` AS usermeta ON usermeta.user_id = c.ID ";

        if (!function_exists('wc_get_order_status_name')) {
            $query .= " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts.ID
                                AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` 
                                WHERE taxonomy = 'shop_order_status')
                            LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
        }

        if (isset($this->show_all_customers)) {
            $query .= " LEFT JOIN 
                            (SELECT COUNT(DISTINCT (posts.ID)) AS ID,
                                meta.meta_value AS id_customer,
                                posts.post_status
                            FROM `{$wpdb->posts}` AS posts
                                LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts.ID = meta.post_id
                            WHERE
                                meta.meta_key = '_customer_user'
                                AND posts.post_type = 'shop_order' ";

            if (!empty($this->status_list_hide)) {
                $query_post_status_not = " AND posts.post_status NOT IN('" . implode("','", $this->status_list_hide) . "')";
                $query .= $query_post_status_not;
            }

            if (!empty($this->statuses) && $this->statuses != -1) {
                if (function_exists('wc_get_order_status_name')) {
                    $query_post_status_in = sprintf(" AND posts.post_status IN ('%s')",
                        $this->get_filter_statuses($this->statuses)
                    );
                } else {
                    $query_post_status_in = sprintf(" AND status_terms.slug IN ('%s')",
                        $this->get_filter_statuses($this->statuses)
                    );
                }
                $query .= $query_post_status_in;
            }

            $query .= ' GROUP BY meta.meta_value) AS tot ON tot.id_customer = c.ID ';

            // Get total count for not registered customers
            if ($this->show_all_customers) {
                $query_not_register = "SELECT count_not_register.email AS guest_email
                      FROM (SELECT COUNT(posts.ID), meta_email.meta_value AS email
                        FROM `{$wpdb->posts}` AS posts
                          LEFT JOIN `{$wpdb->postmeta}` AS meta_email ON meta_email.post_id = posts.ID 
                            AND meta_email.meta_key = '_billing_email'
                          LEFT JOIN `{$wpdb->postmeta}` AS meta_customer ON meta_customer.post_id = posts.ID 
                            AND meta_customer.meta_key = '_customer_user'
                        WHERE posts.post_type = 'shop_order' AND meta_customer.meta_value = 0
                        ";

                if (!empty($query_post_status_not)) {
                    $query_not_register .= $query_post_status_not;
                }

                if ($this->cust_with_orders && $this->statuses == -1) {
                    $query_not_register .= ' AND posts.ID IS NULL ';
                }

                if (!empty($query_post_status_in)) {
                    $query_not_register .= $query_post_status_in;
                }

                if (!empty($data['date_from'])) {
                    $query_not_register .= sprintf(" AND UNIX_TIMESTAMP(posts.post_date) >= '%d'",
                        strtotime($data['date_from'])
                    );
                }

                if (!empty($data['date_to'])) {
                    $query_not_register .= sprintf(" AND UNIX_TIMESTAMP(posts.post_date) <= '%d'",
                        strtotime($data['date_to'])
                    );
                }
            }
        }

        $query .= " WHERE (usermeta.meta_key = '{$wpdb->prefix}capabilities' AND usermeta.meta_value LIKE '%customer%')";

        if ($this->cust_with_orders && $this->statuses == -1 && isset($this->show_all_customers)) {
            $query .= ' AND tot.ID is NULL ';
        }

        if (!empty($data['date_from'])) {
            $query .= sprintf(" AND UNIX_TIMESTAMP(c.user_registered) >= '%d'", strtotime($data['date_from']));
        }

        if (!empty($data['date_to'])) {
            $query .= sprintf(" AND UNIX_TIMESTAMP(c.user_registered) <= '%d'", strtotime($data['date_to']));
        }

        if ((!empty($this->cust_with_orders) && !isset($this->show_all_customers)) ||
            (isset($this->show_all_customers) && !$this->cust_with_orders)) {
            $query .= ' AND tot.ID > 0 ';

            if (!empty($query_not_register)) {
                $query_not_register .= ' AND posts.ID > 0 ';
            }
        }

        if (isset($this->show_all_customers) && (int)$this->show_all_customers && !empty($query_not_register)) {
            $query_not_register .= ' GROUP BY meta_email.meta_value';
            $query = $query_not_register . ' ) AS count_not_register UNION ' . $query;
        }

        $total_count = count($wpdb->get_col($query, 0));
        $totals['count_customers'] = mobassist_nice_count($total_count);

        return $totals;
    }

    public function get_data_graphs()
    {
        global $wpdb;

        $orders = array();
        $customers = array();
        $average = array(
            'avg_sum_orders' => 0,
            'avg_orders' => 0,
            'avg_customers' => 0,
            'avg_cust_order' => '0.00',
            'tot_orders' => 0,
            'sum_orders' => '0.00',
            'tot_customers' => 0,
            'currency_symbol' => ''
        );


        if (empty($this->graph_from)) {
            $this->graph_from = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 7, date('Y')));
        }
        $startDate = $this->graph_from . ' 00:00:00';

        if (empty($this->graph_to)) {
            if (!empty($this->stats_to)) {
                $this->graph_to = $this->stats_to;
            } else {
                $this->graph_to = date('Y-m-d');
            }
        }
        $endDate = $this->graph_to . ' 23:59:59';

        $plus_date = '+1 day';
        $group_by_period = 'day';
        if (!empty($this->custom_period) && strlen($this->custom_period) > 0) {
            $custom_period_date = $this->get_custom_period($this->custom_period);

            if ($this->custom_period == 3) {
                $plus_date = '+3 day';
            } elseif ($this->custom_period == 4 || $this->custom_period == 8) {
                $plus_date = '+1 week';
                $group_by_period = 'week';
            } elseif ($this->custom_period == 5 || $this->custom_period == 6 || $this->custom_period == 7) {
                $plus_date = '+1 month';
                $group_by_period = 'month';
            }

            if ($this->custom_period == 7) {
                $sql = "SELECT MIN(post_date) AS min_date_add, MAX(post_date) AS max_date_add FROM `{$wpdb->posts}` WHERE post_type = 'shop_order'";
                if (!empty($this->status_list_hide)) {
                    $sql .= " AND post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
                }

                if ($max_date = $wpdb->get_row($sql, ARRAY_A)) {
                    $startDate = $max_date['min_date_add'];
                    $endDate = $max_date['max_date_add'];
                }

            } else {
                $startDate = $custom_period_date['start_date'] . ' 00:00:00';
                $endDate = $custom_period_date['end_date'] . ' 23:59:59';
            }
        }

        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);

        $date = $startDate;
        $d = 0;
        $customers_email = array();
        while ($date <= $endDate) {
            $d++;
            $query = "SELECT COUNT(DISTINCT(posts.ID)) AS tot_orders, SUM(meta_order_total.meta_value) AS value
                    FROM `{$wpdb->posts}` AS posts";

            if (!function_exists('wc_get_order_status_name')) {
                $query .= " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts.ID
                                AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                            LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
            }

            $query .= " LEFT JOIN `{$wpdb->postmeta}` AS meta_order_total ON meta_order_total.post_id = posts.ID AND meta_order_total.meta_key = '_order_total'
                        WHERE posts.post_type = 'shop_order'
                            AND UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) >= '%d'
                            AND UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) < '%d' ";
            if (!empty($this->status_list_hide)) {
                $query .= " AND posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
            }

            $query = sprintf($query, $date, strtotime($plus_date, $date));

            if (!empty($this->statuses)) {
                if (function_exists('wc_get_order_status_name')) {
                    $query .= sprintf(" AND posts.post_status IN ('%s')", $this->get_filter_statuses($this->statuses));
                } else {
                    $query .= sprintf(" AND status_terms.slug IN ('%s')", $this->get_filter_statuses($this->statuses));
                }
            }

            $query .= ' GROUP BY DATE(posts.post_date) ORDER BY posts.post_date';

            $total_order_per_day = 0;
            if ($results = $wpdb->get_results($query, ARRAY_A)) {
                foreach ($results as $row) {
                    $total_order_per_day += $row['value'];

                    $average['tot_orders'] += $row['tot_orders'];
                    $average['sum_orders'] += $row['value'];
                }
            }

            $orders[] = array($date * 1000, $total_order_per_day);

            $query = "SELECT COALESCE(NULLIF(user.user_email, ''), meta_email.meta_value) AS email
                        FROM `{$wpdb->posts}` AS posts
                          LEFT JOIN `{$wpdb->postmeta}` AS meta_email ON meta_email.post_id = posts.ID 
                               AND meta_email.meta_key = '%s'
                          LEFT JOIN `{$wpdb->postmeta}` AS meta_customer ON meta_customer.post_id = posts.ID 
                               AND meta_customer.meta_key = '%s'
                          LEFT JOIN `{$wpdb->users}` AS user ON user.ID = meta_customer.meta_value
                        WHERE posts.post_type = 'shop_order'
                          AND UNIX_TIMESTAMP(posts.post_date) >= '%d'
                          AND UNIX_TIMESTAMP(posts.post_date) < '%d'
                          AND posts.post_status NOT IN ('auto-draft', 'trash')
                        GROUP BY (email)";

            $query = sprintf($query, '_billing_email', '_customer_user', $date, strtotime($plus_date, $date));

            $total_customer_per_day = 0;
            $customers_email_per_day = array();

            if ($results = $wpdb->get_results($query, ARRAY_A)) {
                foreach ($results as $row) {
                    if (!in_array($row['email'], $customers_email)) {
                        array_push($customers_email, $row['email']);
                        array_push($customers_email_per_day, $row['email']);
                    }
                }
            }

            $total_customer_per_day = count($customers_email_per_day);
            $average['tot_customers'] += $total_customer_per_day;
            $customers[] = array($date * 1000, $total_customer_per_day);

            $date = strtotime($plus_date, $date);
        }

        // Add 2 additional element into array of orders for graph in mobile application
        if (count($orders) == 1) {
            $orders_tmp = $orders[0];
            $orders = array();
            $orders[0][] = strtotime(date('Y-m-d', $orders_tmp[0] / 1000) . '-1 month') * 1000;
            $orders[0][] = 0;
            $orders[1] = $orders_tmp;
            $orders[2][] = strtotime(date('Y-m-d', $orders_tmp[0] / 1000) . '+1 month') * 1000;
            $orders[2][] = 0;
        }

        // Add 2 additional element into array of customers for graph in mobile application
        if (count($customers) == 1) {
            $customers_tmp = $customers[0];
            $customers = array();
            $customers[0][] = strtotime(date('Y-m-d', $customers_tmp[0] / 1000) . '-1 month') * 1000;
            $customers[0][] = 0;
            $customers[1] = $customers_tmp;
            $customers[2][] = strtotime(date('Y-m-d', $customers_tmp[0] / 1000) . '+1 month') * 1000;
            $customers[2][] = 0;
        }

        if ($d <= 0) {
            $d = 1;
        }
        $average['avg_sum_orders'] = mobassist_nice_price(number_format($average['sum_orders'] / $d, 2, '.', ' '),
            $this->currency, false, true);
        $average['avg_orders'] = number_format($average['tot_orders'] / $d, 1, '.', ' ');
        $average['avg_customers'] = number_format($average['tot_customers'] / $d, 1, '.', ' ');

        if ($average['tot_customers'] > 0) {
            $average['avg_cust_order'] = mobassist_nice_price(number_format($average['sum_orders'] / $average['tot_customers'], 1,
                '.', ' '), $this->currency, false, true);
        }

        // Returned customers list
        $query = "SELECT COUNT(*) AS returned_customers
                  FROM (
                      SELECT COALESCE(NULLIF(user.user_email, ''), meta_email.meta_value) AS email
                        FROM `{$wpdb->posts}` AS posts
                          LEFT JOIN `{$wpdb->postmeta}` AS meta_email ON meta_email.post_id = posts.ID 
                               AND meta_email.meta_key = '%s'
                          LEFT JOIN `{$wpdb->postmeta}` AS meta_customer ON meta_customer.post_id = posts.ID 
                               AND meta_customer.meta_key = '%s'
                          LEFT JOIN `{$wpdb->users}` AS user ON user.ID = meta_customer.meta_value
                        WHERE posts.post_type = 'shop_order' 
                          AND UNIX_TIMESTAMP(posts.post_date) >= '%d'
                          AND UNIX_TIMESTAMP(posts.post_date) < '%d' 
                          AND posts.post_status NOT IN ('auto-draft', 'trash')
                        GROUP BY (email)
                        HAVING COUNT(*) > 1)
                    AS email_counter";
        $query = sprintf($query, '_billing_email', '_customer_user', $startDate, $endDate);

        $average['return_customers'] = $wpdb->get_var($query);
        $average['sum_orders'] = number_format($average['sum_orders'], 2, '.', ' ');
        $average['tot_customers'] = number_format($average['tot_customers'], 1, '.', ' ');
        $average['tot_orders'] = number_format($average['tot_orders'], 1, '.', ' ');
        $average['group_by'] = $group_by_period;

        return array('orders' => $orders, 'customers' => $customers, 'average' => $average);
    }

    public function get_status_stats()
    {
        global $wpdb;

        $order_statuses = array();

        if (function_exists('wc_get_order_status_name')) {
            $query = "SELECT COUNT(DISTINCT(posts.ID)) AS count, SUM(meta_order_total.meta_value) AS total, posts.post_status AS code
                        FROM `{$wpdb->posts}` AS posts
                          LEFT JOIN `{$wpdb->postmeta}` AS meta_order_total ON meta_order_total.post_id = posts.ID AND meta_order_total.meta_key = '_order_total'";
        } else {
            $query = "SELECT COUNT(DISTINCT(posts.ID)) AS count, SUM(meta_order_total.meta_value) AS total, status_terms.slug AS code
                        FROM `{$wpdb->posts}` AS posts
                        LEFT JOIN `{$wpdb->postmeta}` AS meta_order_total ON meta_order_total.post_id = posts.ID AND meta_order_total.meta_key = '_order_total'
                        LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts.ID
                                  AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                        LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
        }

        $today = date('Y-m-d');
        $date_from = $date_to = $today;

        if (!empty($this->stats_from)) {
            $date_from = $this->stats_from;
        }

        if (!empty($this->stats_to)) {
            $date_to = $this->stats_to;
        }

        if (!empty($this->custom_period) && strlen($this->custom_period) > 0) {
            $custom_period = $this->get_custom_period($this->custom_period);

            $date_from = $custom_period['start_date'];
            $date_to = $custom_period['end_date'];
        }


        $query_where_parts[] = " posts.post_type = 'shop_order' ";
        if (!empty($date_from)) {
            $query_where_parts[] = sprintf(" UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) >= '%d'",
                strtotime($date_from . ' 00:00:00'));
        }

        if (!empty($date_to)) {
            $query_where_parts[] = sprintf(" UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) <= '%d'",
                strtotime($date_to . ' 23:59:59'));
        }

        if (!empty($this->status_list_hide)) {
            $query_where_parts[] = " posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        if (!empty($query_where_parts)) {
            $query .= ' WHERE ' . implode(' AND ', $query_where_parts);
        }

        if (function_exists('wc_get_order_status_name')) {
            $query .= ' GROUP BY posts.post_status ORDER BY total';
        } else {
            $query .= ' GROUP BY order_status_terms.term_taxonomy_id ORDER BY total';
        }

        if ($results = $wpdb->get_results($query, ARRAY_A)) {
            foreach ($results as $row) {
                if ($row['count'] == 0) {
                    continue;
                }

                $row['total'] = mobassist_nice_price($row['total'], $this->currency, false, true);
                $row['name'] = mobassist_get_order_status_name(0, $row['code']);

                $order_statuses[] = $row;
            }
        }

        return $order_statuses;
    }

    public function get_orders()
    {
        global $wpdb;

        $sql_total_products = "SELECT SUM(meta_items_qty.meta_value)
            FROM `{$wpdb->prefix}woocommerce_order_items` AS order_items
            LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_items_qty ON meta_items_qty.order_item_id = order_items.order_item_id AND meta_items_qty.meta_key = '_qty'
            WHERE order_items.order_item_type = 'line_item' AND order_items.order_id = posts.ID";

        if (function_exists('wc_get_order_status_name')) {
            $status_code_field = 'posts.post_status';
        } else {
            $status_code_field = 'status_terms.slug';
        }

        $fields = "SELECT
                    posts.ID AS id_order,
                    posts.post_date AS date_add,
                    meta_order_total.meta_value AS total_paid,
                    meta_order_currency.meta_value AS currency_code,
                    $status_code_field AS status_code,
                    first_name.meta_value AS first_name,
                    last_name.meta_value AS last_name,
                    CONCAT(first_name.meta_value, ' ', last_name.meta_value) AS customer,
                    users.display_name,
                    customer_id.meta_value AS customer_id,
                    ( $sql_total_products ) AS count_prods,
                    billing_first_name.meta_value AS billing_first_name,
                    billing_last_name.meta_value AS billing_last_name,
                    customer_email.meta_value AS customer_email";

        $total_fields = 'SELECT COUNT(DISTINCT(posts.ID)) AS total_orders, SUM(meta_order_total.meta_value) AS total_sales';

        $sql = " FROM `{$wpdb->posts}` AS posts
            LEFT JOIN `{$wpdb->postmeta}` AS meta_order_total ON meta_order_total.post_id = posts.ID AND meta_order_total.meta_key = '_order_total'
            LEFT JOIN `{$wpdb->postmeta}` AS meta_order_currency ON meta_order_currency.post_id = posts.ID AND meta_order_currency.meta_key = '_order_currency'
            LEFT JOIN `{$wpdb->postmeta}` AS customer_id ON customer_id.post_id = posts.ID AND customer_id.meta_key = '_customer_user'
            LEFT JOIN `{$wpdb->usermeta}` AS first_name ON first_name.user_id = customer_id.meta_value AND first_name.meta_key = 'first_name'
            LEFT JOIN `{$wpdb->usermeta}` AS last_name ON last_name.user_id = customer_id.meta_value AND last_name.meta_key = 'last_name'
            LEFT JOIN `{$wpdb->users}` AS users ON users.ID = customer_id.meta_value
            LEFT JOIN `{$wpdb->postmeta}` AS billing_first_name ON billing_first_name.post_id = posts.ID AND billing_first_name.meta_key = '_billing_first_name'
            LEFT JOIN `{$wpdb->postmeta}` AS billing_last_name ON billing_last_name.post_id = posts.ID AND billing_last_name.meta_key = '_billing_last_name'
            LEFT JOIN `{$wpdb->postmeta}` AS customer_email ON customer_email.post_id = posts.ID AND customer_email.meta_key = '_billing_email'
        ";

        if (isset($this->show_all_customers) && !$this->show_all_customers) {
            $sql .= " LEFT JOIN `{$wpdb->usermeta}` AS cap ON cap.user_id = users.ID ";
            $query_where_parts[] = " (cap.meta_key = '{$wpdb->prefix}capabilities' AND cap.meta_value LIKE '%customer%') ";
        }

        if (!function_exists('wc_get_order_status_name')) {
            $sql .= " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts.ID
                    AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
        }

        $query = $fields . $sql;
        $query_totals = $total_fields . $sql;

        $query_where_parts[] = " posts.post_type = 'shop_order' ";

        if (!empty($this->status_list_hide)) {
            $query_where_parts[] = " posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        if (!empty($this->orders_from)) {
            $query_where_parts[] = sprintf(" UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) >= '%d'",
                strtotime($this->orders_from . ' 00:00:00'));
        }

        if (!empty($this->orders_to)) {
            $query_where_parts[] = sprintf(" UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) <= '%d'",
                strtotime($this->orders_to . ' 23:59:59'));
        }

        if (!empty($this->search_order_id) && preg_match('/^\d+(?:,\d+)*$/', $this->search_order_id)) {
            $query_where_parts[] = sprintf('posts.ID IN (%s)', $this->search_order_id);
        } elseif (!empty($this->search_order_id)) {
            $query_where_parts[] = sprintf(
                " (CONCAT(first_name.meta_value, ' ', last_name.meta_value) LIKE '%%%s%%' OR users.display_name LIKE '%%%s%%' OR customer_email.meta_value = '%s') ",
                $this->search_order_id,
                $this->search_order_id,
                $this->search_order_id
            );
            if (isset($this->show_all_customers) && $this->show_all_customers) {
                $query_where_not_registered = sprintf(
                    " OR (CONCAT(billing_first_name.meta_value, ' ', billing_last_name.meta_value) LIKE '%%%s%%' ) ",
                    $this->search_order_id
                );
            }
        }

        if (!empty($this->statuses)) {
            if (function_exists('wc_get_order_status_name')) {
                $query_where_parts[] = sprintf(" posts.post_status IN ('%s')",
                    $this->get_filter_statuses($this->statuses));
            } else {
                $query_where_parts[] = sprintf(" status_terms.slug IN ('%s')",
                    $this->get_filter_statuses($this->statuses));
            }
        }

        if (!empty($query_where_parts)) {
            $query .= ' WHERE ' . implode(' AND ', $query_where_parts);
            $query_totals .= ' WHERE ' . implode(' AND ', $query_where_parts);
        }

        if (!empty($query_where_not_registered)) {
            $query .= $query_where_not_registered;
            $query_totals .= $query_where_not_registered;
        }

        if (empty($this->sort_by)) {
            $this->sort_by = 'id';
        }

        $query .= ' ORDER BY ';
        switch ($this->sort_by) {
            case 'id':
                $dir = $this->getSortDirection();
                $query .= 'posts.ID ' . $dir;
                break;
            case 'date':
                $dir = $this->getSortDirection();
                $query .= 'posts.post_date ' . $dir;
                break;
            case 'name':
                $dir = $this->getSortDirection('ASC');
                $query .= "CONCAT(billing_first_name, ' ', billing_last_name) " . $dir;
                break;
            case 'total':
                $dir = $this->getSortDirection();
                $query .= 'CAST(total_paid AS unsigned) ' . $dir;
                break;
            case 'qty':
                $dir = $this->getSortDirection();
                $query .= 'CAST(count_prods AS unsigned)' . $dir;
                break;
        }

        $query .= sprintf(' LIMIT %d, %d', (($this->page - 1) * $this->show), $this->show);

        $totals = $wpdb->get_row($query_totals, ARRAY_A);

        $orders = array();
        $results = $wpdb->get_results($query, ARRAY_A);
        foreach ($results as $order) {
            $order['ord_status'] = mobassist_get_order_status_name($order['id_order'], $order['status_code']);

            $currency_code = $order['currency_code'];
            $order['total_paid'] = mobassist_nice_price($order['total_paid'], $currency_code, false, true);
            $user = null;
            if (array_key_exists('customer_id', $order)) {
                $user = new WP_User($order['customer_id']);
            }

            $customer_name = trim(trim($order['billing_first_name']) . ' ' . trim($order['billing_last_name']));
            if (!empty($user) && empty($customer_name)) {
                $first_name = trim($user->first_name);
                if (isset($first_name[0])) {
                    $first_name[0] = strtoupper($first_name[0]);
                }

                $last_name = trim($user->last_name);
                if (isset($last_name[0])) {
                    $last_name[0] = strtoupper($last_name[0]);
                }

                $customer_name = trim($first_name . ' ' . $last_name);
            }

            if (empty($customer_name)) {
                $customer_name = __('Guest', 'mobile-assistant-connector');
            }

            $order['customer'] = $customer_name;
            $orders[] = $order;
        }

        $orders_status = null;
        if (!empty($this->get_statuses) && $this->get_statuses == 1) {
            $orders_status = $this->get_orders_statuses();
        }

        return array(
            'orders' => $orders,
            'orders_count' => $totals['total_orders'],
            'orders_total' => mobassist_nice_price($totals['total_sales'], $this->currency),
            'orders_status' => $orders_status
        );
    }

    private function getSortDirection($default_direction = 'DESC')
    {
        if (isset($this->order_by) && !empty($this->order_by)) {
            $direction = $this->order_by;
        } else {
            $direction = $default_direction;
        }

        return ' ' . $direction;
    }

    public function get_orders_statuses()
    {
        $orders_statuses = array();

        $statuses = mobassist_get_order_statuses();

        foreach ($statuses as $code => $name) {
            $orders_statuses[] = array('st_id' => $code, 'st_name' => $name);
        }

        return $orders_statuses;
    }

    public function get_orders_info()
    {
        global $woocommerce, $wpdb;

        $order_info = array();
        $this->order_id = mobassist_validate_post($this->order_id, 'shop_order');

        if (!$this->order_id || empty($this->order_id)) {
            return false;
        }

        $order = new WC_Order($this->order_id);
        $user = new WP_User($order->get_user_id());

        if (method_exists($order, 'get_currency')) {
            $currency_code = $order->get_currency();
        } else if (method_exists($order, 'get_order_currency')) {
            $currency_code = $order->get_order_currency();
        } else {
            $currency_code = $this->currency;
        }

        if (empty($this->only_items)) {
            $first_name = trim($user->first_name);
            if (isset($first_name[0])) {
                $first_name[0] = strtoupper($first_name[0]);
            }

            $last_name = trim($user->last_name);
            if (isset($last_name[0])) {
                $last_name[0] = strtoupper($last_name[0]);
            }

            $customer_name = trim($first_name . ' ' . $last_name);
            if (empty($customer_name)) {
                $first_name = $this->is_v3() ? $order->get_billing_first_name() : $order->billing_first_name;
                $last_name = $this->is_v3() ? $order->get_billing_last_name() : $order->billing_last_name;
                $customer_name = trim($first_name . ' ' . $last_name);
            }
            if (empty($customer_name)) {
                $customer_name = __('Guest', 'mobile-assistant-connector');
            }

            $order_total = mobassist_nice_price($order->get_total(), $currency_code, false, true);
            $countries = $woocommerce->countries->countries;

            $order_id = $this->is_v3() ? $order->get_id() : $order->id;

            $order_query = $wpdb->prepare(
                "SELECT posts.post_date_gmt AS date_add, posts.post_status AS order_status_id FROM `{$wpdb->posts}` AS posts WHERE posts.ID = '%s'",
                $order_id
            );
            $order_additional_info = $wpdb->get_row($order_query, ARRAY_A);

            $shipping_country = $this->is_v3() ? $order->get_shipping_country() : $order->shipping_country;
            $billing_country = $this->is_v3() ? $order->get_billing_country() : $order->billing_country;

            if (isset($user->data->user_email)) {
                $email = $user->data->user_email;
            } elseif ($this->is_v3()) {
                $email = $order->get_billing_email();
            } else {
                $email = $order->billing_email;
            }

            $order_info = array(
                'id_order' => $order_id,
                'id_customer' => $order->get_user_id(),
                'email' => $email,
                'customer' => $customer_name,
                'date_added' => $order_additional_info['date_add'] ? $order_additional_info['date_add'] : '',
                'status_code' => $order->get_status(),
                'status' => mobassist_get_order_status_name($order_id, $order->get_status()),
                'total' => $order_total,
                'currency_code' => $currency_code,
                'customer_note' => $this->is_v3() ? $order->get_customer_note() : $order->customer_note,

                'p_method' => $this->is_v3() ? $order->get_payment_method_title() : $order->payment_method_title,
                'b_name' => $this->is_v3() ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : $order->billing_first_name . ' ' . $order->billing_last_name,
                'b_company' => $this->is_v3() ? $order->get_billing_company() : $order->billing_company,
                'b_address_1' => $this->is_v3() ? $order->get_billing_address_1() : $order->billing_address_1,
                'b_address_2' => $this->is_v3() ? $order->get_billing_address_2() : $order->billing_address_2,
                'b_city' => $this->is_v3() ? $order->get_billing_city() : $order->billing_city,
                'b_postcode' => $this->is_v3() ? $order->get_billing_postcode() : $order->billing_postcode,
                'b_country' => isset($countries[$billing_country]) ? $countries[$billing_country] : '',
                'b_state' => $this->is_v3() ? $order->get_billing_state() : $order->billing_state,
                'b_email' => $this->is_v3() ? $order->get_billing_email() : $order->billing_email,
                'b_telephone' => $this->is_v3() ? $order->get_billing_phone() : $order->billing_phone,

                's_method' => $order->get_shipping_method(),
                's_name' => $this->is_v3() ? $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() : $order->shipping_first_name . ' ' . $order->shipping_last_name,
                's_company' => $this->is_v3() ? $order->get_shipping_company() : $order->shipping_company,
                's_address_1' => $this->is_v3() ? $order->get_shipping_address_1() : $order->shipping_address_1,
                's_address_2' => $this->is_v3() ? $order->get_shipping_address_2() : $order->shipping_address_2,
                's_city' => $this->is_v3() ? $order->get_shipping_city() : $order->shipping_city,
                's_postcode' => $this->is_v3() ? $order->get_shipping_postcode() : $order->shipping_postcode,
                's_country' => isset($countries[$shipping_country]) ? $countries[$shipping_country] : '',
                's_state' => $this->is_v3() ? $order->get_shipping_state() : $order->shipping_state,

                'total_shipping' => mobassist_nice_price($this->is_v3() ? $order->get_shipping_total() : $order->shipping_total, $currency_code),
                'discount' => mobassist_nice_price($order->get_total_discount(), $currency_code),
                'tax_amount' => mobassist_nice_price((float)$order->get_cart_tax() + (float)$order->get_shipping_tax(),
                    $currency_code),
                'order_total' => $order_total,

                'admin_comments' => $this->_get_order_notes($order_id),
            );

            if (method_exists($order, 'get_total_refunded')) {
                $order_info['t_refunded'] = mobassist_nice_price($order->get_total_refunded() * -1, $currency_code);
            }

            $order_custom_fields = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = '%s' AND meta_key NOT LIKE '\_%'",
                    $order_id
                ),
                ARRAY_N
            );

            $order_info['order_custom_fields'] = $order_custom_fields;
        }

        $order_products = array();
        $order_items = $order->get_items();
        $order_item_count = count($order_items);
        $order_item_loop_counter = 0;
        if ($order_item_count > (($this->page - 1) * $this->show)) {
            foreach ($order_items as $item_id => $item) {
                $my_product = array();
                $order_item_loop_counter++;
                if ($order_item_loop_counter <= (($this->page - 1) * $this->show)) {
                    continue;
                }

                $product = $order->get_product_from_item($item);
                $product_id = null;
                $product_sku = null;

                if (is_object($product)) {
                    $my_product['sku'] = $product->get_sku();
                }

                $product_id = $this->is_v3() ? (int)$item->get_product_id() : (int)$product->id;

                $my_product['product_id'] = $product_id;

                $my_product['product_name'] = $item['name'];
                $my_product['product_quantity'] = wc_stock_amount($item['qty']);

                $rp_product_id = new ReflectionProperty($product, 'id');
                if (method_exists($item, 'get_product_id')) {
                    $product_id = (int)$item->get_product_id();
                } else if(method_exists($product, 'get_id')) {
                    $product_id = $product->get_id();
                } else if($rp_product_id->isPublic()) {
                    $product_id = $product->id;
                }

                $var_id = $product->is_type('variation') ? $item->get_variation_id() : $product_id;

                $thumbnail_id = get_post_thumbnail_id($var_id);
                if (empty($thumbnail_id)) {
                    $thumbnail_id = get_post_thumbnail_id($product_id);
                }

                $image_url = get_image_url($thumbnail_id, 'thumbnail');
                $my_product['thumbnail'] = $image_url;

                if (method_exists($item, 'get_subtotal')) {
                    $product_price = $item->{'get_subtotal'}();
                } else {
                    $product_price = $product->price;
                }

                if (method_exists($item, 'get_total')) {
                    $product_total = $item->{'get_total'}();
                } else {
                    $product_total = ($product_price * (int)$my_product['product_quantity']);
                }

                /** rename in future product_price -> product_total */
                $my_product['product_price'] = mobassist_nice_price($product_total, $currency_code, false, true);
                $my_product['product_type'] = $this->_get_product_type($product_id);

                $variation_data = array();
                if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
                    $meta = new WC_Order_Item_Meta($item['item_meta'], $product);
                    foreach ($meta->get_formatted("_") as $meta_key => $formatted_meta) {
                        $variation_data[] = array(
                            'attribute' => $formatted_meta['label'] . ': <strong>'
                                . $formatted_meta['value'] . '</strong>'
                        );
                    }
                } elseif (method_exists($item, 'get_meta_data')) {
                    foreach ($item->{'get_meta_data'}() as $meta) {
                        $mata_data = $meta->get_data();
                        $variation_data[] = array(
                            'attribute' => $mata_data['key'] . ': <strong>'
                                . $mata_data['value'] . '</strong>'
                        );
                    }
                } else {
                    foreach ($item->get_formatted_meta_data('', true) as $meta) {
                        $mata_data = (array)$meta;
                        if (array_key_exists('display_key', $mata_data) && !empty($mata_data['display_key'])) {
                            $key = (string)$mata_data['display_key'];
                        } else {
                            $key = (string)$mata_data['key'];
                        }

                        if (array_key_exists('display_value', $mata_data) && !empty($mata_data['display_value'])) {
                            $value = (string)$mata_data['display_value'];
                        } else {
                            $value = (string)$mata_data['value'];
                        }

                        $attribute_data = $key . ': <strong>' . $value . '</strong>';
                        $variation_data[] = array('attribute' => $attribute_data);
                    }
                }


                $my_product['product_variation'] = $variation_data;
                $order_products[] = $my_product;
            }
        }

        $pdf_invoice = 0;
        if (!empty($order_products)) {
            if (in_array('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php',
                apply_filters('active_plugins', get_option('active_plugins')))
            ) {
                $pdf_invoice = 1;
            }
        }
        $order_full_info = array(
            'order_info'        => $order_info,
            'order_products'    => $order_products,
            'o_products_count'  => $order_item_count,
            'pdf_invoice'       => $pdf_invoice
        );
        return $order_full_info;
    }

    private function _get_order_notes($order_id, $fields = null)
    {
        $args = array(
            'post_id' => $order_id,
            'approve' => 'approve',
            'type' => 'order_note'
        );

        remove_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'), 10, 1);
        remove_filter('comments_clauses', 'woocommerce_exclude_order_comments');

        $notes = get_comments($args);

        add_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'), 10, 1);
        add_filter('comments_clauses', 'woocommerce_exclude_order_comments');

        $order_notes = array();

        foreach ($notes as $note) {

            $order_notes[] = current($this->_get_order_note($order_id, $note->comment_ID, $fields));
        }

        $order_notes = apply_filters('woocommerce_api_order_notes_response', $order_notes, $order_id, $fields, $notes);

        $notes = array();
        foreach ($order_notes as $note) {
            $temp_note = array('date_added' => $note['created_at'], 'note' => $note['note'],);

            if ($note['customer_note'] == 1) {
                $temp_note['note_type'] = __('Customer note', 'mobile-assistant-connector');
            } else {
                $temp_note['note_type'] = __('Private note', 'mobile-assistant-connector');
            }

            $notes[] = $temp_note;
        }

        return $notes;
    }

    private function _get_order_note($order_id, $id, $fields = null)
    {
        $id = absint($id);

        if (empty($id)) {
            return new WP_Error('woocommerce_api_invalid_order_note_id',
                __('Invalid order note ID', 'mobile-assistant-connector'), array('status' => 400));
        }

        $note = get_comment($id);

        if (is_null($note)) {
            return new WP_Error('woocommerce_api_invalid_order_note_id',
                __('An order note with the provided ID could not be found', 'mobile-assistant-connector'),
                array('status' => 404));
        }

        $order_note = array(
            'id' => $note->comment_ID,
            'created_at' => $this->_parse_datetime($note->comment_date_gmt),
            'note' => $note->comment_content,
            'customer_note' => get_comment_meta($note->comment_ID, 'is_customer_note', true) ? true : false,
        );

        return array(
            'order_note' => apply_filters('woocommerce_api_order_note_response', $order_note, $id, $fields, $note,
                $order_id, $this)
        );
    }

    private function _parse_datetime($datetime)
    {
        // Strip millisecond precision (a full stop followed by one or more digits)
        if (strpos($datetime, '.') !== false) {
            $datetime = preg_replace('/\.\d+/', '', $datetime);
        }

        // default timezone to UTC
        $datetime = preg_replace('/[+-]\d+:+\d+$/', '+00:00', $datetime);

        try {
            $datetime = new DateTime($datetime, new DateTimeZone('UTC'));

        } catch (Exception $e) {
            $datetime = new DateTime('@0');
        }

        return $datetime->format('Y-m-d H:i:s');
    }

    private function _get_product_type($product_id)
    {
        if (function_exists('wc_get_product')) {
            $the_product = wc_get_product($product_id);
        } else {
            $the_product = get_product($product_id);
        }

        $type = '';
        $product_type = $this->is_v3() ? $the_product->get_type() : $the_product->product_type;
        if ('grouped' == $product_type) {
            $type = __('Grouped', 'mobile-assistant-connector');

        } elseif ('external' == $product_type) {
            $type = __('External/Affiliate', 'mobile-assistant-connector');

        } elseif ('simple' == $product_type) {

            if ($the_product->is_virtual()) {
                $type = __('Virtual', 'mobile-assistant-connector');

            } elseif ($the_product->is_downloadable()) {
                $type = __('Downloadable', 'mobile-assistant-connector');

            } else {
                $type = __('Simple', 'mobile-assistant-connector');
            }

        } elseif ('variable' == $product_type) {
            $type = __('Variable', 'mobile-assistant-connector');

        } else {
            $type = ucfirst($product_type);
        }

        return $type;
    }

    public function get_customers()
    {
        global $wpdb;
        $query_where_parts = array();
        $query_page_not_registered = array();

        // Get registered customers query
        $fields = "SELECT
            DISTINCT(c.ID) AS id_customer,
            um_first_name.meta_value AS firstname,
            um_last_name.meta_value AS lastname,
            CONCAT(um_first_name.meta_value, ' ', um_last_name.meta_value) AS full_name,
            c.user_registered AS date_add,
            c.user_email AS email,
            c.display_name,
            posts.ID AS total_orders";

        $total_fields = 'SELECT c.user_email';

        $sql = " FROM `{$wpdb->users}` AS c
              LEFT JOIN `{$wpdb->usermeta}` AS um_first_name ON um_first_name.user_id = c.ID AND um_first_name.meta_key = 'first_name'
              LEFT JOIN `{$wpdb->usermeta}` AS um_last_name ON um_last_name.user_id = c.ID AND um_last_name.meta_key = 'last_name'
              LEFT JOIN `{$wpdb->usermeta}` AS cap ON cap.user_id = c.ID
              LEFT OUTER JOIN (
                SELECT COUNT(DISTINCT(posts.ID)) AS ID, 
                meta.meta_value AS id_customer,
                posts.post_status
                FROM `{$wpdb->posts}` AS posts
                LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts.ID = meta.post_id";

        if (!function_exists('wc_get_order_status_name')) {
            $sql .= " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts_orders.ID
                        AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                    LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
        }

        $sql .= " WHERE meta.meta_key = '_customer_user' 
                AND posts.post_type = 'shop_order'";

        if (!empty($this->status_list_hide)) {
            $post_status_not_in = " posts.post_status NOT IN ('" . implode("','", $this->status_list_hide) . "')";
            $sql .= ' AND ' . $post_status_not_in;
        }

        if (!empty($this->statuses) && $this->statuses != -1) {
            if (function_exists('wc_get_order_status_name')) {
                $post_status_in = sprintf(" posts.post_status IN ('%s')", $this->get_filter_statuses($this->statuses));
                $sql .= ' AND ' . $post_status_in;
            } else {
                $post_status_in = sprintf(" status_terms.slug IN ('%s')", $this->get_filter_statuses($this->statuses));
                $sql .= ' AND ' . $post_status_in;
            }
        }

        $sql .= " GROUP BY meta.meta_value ) AS posts ON posts.id_customer = c.ID
                WHERE (cap.meta_key = '{$wpdb->prefix}capabilities' AND cap.meta_value LIKE '%customer%') ";
        $query = $fields . $sql;
        $query_page = $total_fields . $sql;

        // Get not registered customers query
        if (isset($this->show_all_customers) && $this->show_all_customers) {
            $fields_not_register = "SELECT
                            - 1 AS id_customer,
                            meta_fname.meta_value AS firstname,
                            meta_lname.meta_value AS lastname,
                            CONCAT(meta_fname.meta_value, ' ', meta_lname.meta_value) AS full_name,
                            posts.post_date AS date_add,
                            meta_email.meta_value AS email,
                            ' ' AS display_name,
                            COUNT(posts.ID) AS total_orders";

            $total_fields_not_registered = 'SELECT meta_email.meta_value ';

            $sql_not_registered = " FROM `{$wpdb->posts}` AS posts
                        LEFT JOIN
                    `{$wpdb->postmeta}` AS meta_fname ON meta_fname.post_id = posts.ID
                        AND meta_fname.meta_key = '_billing_first_name'
                        LEFT JOIN
                    `{$wpdb->postmeta}` AS meta_lname ON meta_lname.post_id = posts.ID
                        AND meta_lname.meta_key = '_billing_last_name'
                        LEFT JOIN
                    `{$wpdb->postmeta}` AS meta_email ON meta_email.post_id = posts.ID
                        AND meta_email.meta_key = '_billing_email'
                        LEFT JOIN
                    `{$wpdb->postmeta}` AS meta_customer ON meta_customer.post_id = posts.ID
                        AND meta_customer.meta_key = '_customer_user'
                    WHERE posts.post_type = 'shop_order' 
                        AND meta_customer.meta_value = 0
                    ";

            $query_not_registered = $fields_not_register . $sql_not_registered;
            $query_page_not_registered = $total_fields_not_registered . $sql_not_registered;

            if (!empty($post_status_in)) {
                $query_where_not_registered[] = $post_status_in;
            }

            if (!empty($post_status_not_in)) {
                $query_where_not_registered[] = $post_status_not_in;
            }

            if (!empty($query_where_not_registered)) {
                $query_not_registered .= ' AND ' . implode(' AND ', $query_where_not_registered);
                $query_page_not_registered .= ' AND ' . implode(' AND ', $query_where_not_registered);
            }
        }

        if (!empty($this->customers_from)) {
            if ($this->show_all_customers && !empty($query_not_registered) && !empty($query_page_not_registered)) {
                $query_not_registered .= sprintf(" AND UNIX_TIMESTAMP(posts.post_date) >= '%d'",
                    strtotime($this->customers_from . ' 00:00:00'));
                $query_page_not_registered .= sprintf(" AND UNIX_TIMESTAMP(posts.post_date) >= '%d'",
                    strtotime($this->customers_from . ' 00:00:00'));
            }
            $query .= sprintf(" AND UNIX_TIMESTAMP(c.user_registered) >= '%d'",
                strtotime($this->customers_from . ' 00:00:00'));
            $query_page .= sprintf(" AND UNIX_TIMESTAMP(c.user_registered) >= '%d'",
                strtotime($this->customers_from . ' 00:00:00'));
        }

        if (!empty($this->customers_to)) {
            if ($this->show_all_customers && !empty($query_not_registered) && !empty($query_page_not_registered)) {
                $query_not_registered .= sprintf(" AND UNIX_TIMESTAMP(posts.post_date) <= '%d'",
                    strtotime($this->customers_to . ' 23:59:59'));
                $query_page_not_registered .= sprintf(" AND UNIX_TIMESTAMP(posts.post_date) <= '%d'",
                    strtotime($this->customers_to . ' 23:59:59'));
            }
            $query .= sprintf(" AND UNIX_TIMESTAMP(c.user_registered) <= '%d'",
                strtotime($this->customers_to . ' 23:59:59'));
            $query_page .= sprintf(" AND UNIX_TIMESTAMP(c.user_registered) <= '%d'",
                strtotime($this->customers_to . ' 23:59:59'));
        }

        if (!empty($this->search_val) && preg_match('/^\d+(?:,\d+)*$/', $this->search_val)) {
            $query .= sprintf(" AND c.ID IN (%s)", $this->search_val);

            if ($this->show_all_customers && !empty($query_not_registered)) {
                $query_not_registered .= sprintf(" AND -1 IN (%s)", $this->search_val);
            }
        } elseif (!empty($this->search_val)) {
            $query .= sprintf(" AND(c.user_email LIKE '%%%s%%'
                    OR CONCAT(um_first_name.meta_value, ' ', um_last_name.meta_value) LIKE '%%%s%%'
                    OR c.display_name LIKE '%%%s%%')"
                , $this->search_val, $this->search_val, $this->search_val);

            if ($this->show_all_customers && !empty($query_not_registered)) {
                $query_not_registered .= sprintf(" AND (meta_email.meta_value LIKE '%%%s%%'
                    OR CONCAT(meta_fname.meta_value, ' ', meta_lname.meta_value) LIKE '%%%s%%' )"
                    , $this->search_val, $this->search_val);
            }
        }

        if ($this->cust_with_orders && isset($this->show_all_customers) && $this->statuses == -1) {
            $query_where_parts[] = ' posts.ID IS NULL ';
        }

        if ((!empty($this->cust_with_orders) && !isset($this->show_all_customers)) ||
            (isset($this->show_all_customers) && !$this->cust_with_orders)) {
            $query_where_parts[] = ' posts.ID > 0 ';
        }

        if (!empty($query_where_parts)) {
            $query .= ' AND ' . implode(' AND ', $query_where_parts);
            $query_page .= ' AND ' . implode(' AND ', $query_where_parts);
            if (!empty($query_not_registered) && !empty($query_page_not_registered)) {
                $query_not_registered .= ' AND ' . implode(' AND ', $query_where_parts);
                $query_page_not_registered .= ' AND ' . implode(' AND ', $query_where_parts);
            }
        }

        $query .= ' GROUP BY c.user_email';

        if ($this->show_all_customers && !empty($query_not_registered)) {
            $query_not_registered .= ' GROUP BY meta_email.meta_value';
            $query = $query_not_registered . ' UNION ' . $query;
        }

        if (empty($this->sort_by)) {
            $this->sort_by = 'id';
        }

        $query .= ' ORDER BY ';
        switch ($this->sort_by) {
            case 'id':
                $dir = $this->getSortDirection();
                $query .= 'id_customer ' . $dir;
                break;
            case 'date':
                $dir = $this->getSortDirection();
                $query .= 'date_add ' . $dir;
                break;
            case 'name':
                $dir = $this->getSortDirection('ASC');
                $query .= 'full_name ' . $dir;
                break;
            case 'qty':
                $dir = $this->getSortDirection('ASC');
                $query .= 'posts.total_orders ' . $dir;
                break;
        }

        $query .= sprintf(" LIMIT %d, %d", (($this->page - 1) * $this->show), $this->show);

        $customers = array();
        $results = $wpdb->get_results($query, ARRAY_A);
        foreach ($results as $user) {
            $date = explode(' ', $user['date_add']);
            $user['date_add'] = $date[0];
            $user['total_orders'] = (int)$user['total_orders'];

            if ($user['full_name'] == null || trim($user['full_name']) == '') {
                $user['full_name'] = $user['display_name'];
                $user['firstname'] = $user['display_name'];
            }

            $customers[] = $user;
        }

        if ((int)$this->show_all_customers) {
            $query_page = $query_page . ' UNION ' . $query_page_not_registered;
        }

        $row_page['count_custs'] = count($wpdb->get_col($query_page, 0));

        return array(
            'customers_count' => (int)$row_page['count_custs'],
            'customers' => $customers
        );
    }

    public function get_customers_info()
    {
        global $wpdb;

        if ($this->user_id != -1) {
            $this->user_id = mobassist_validate_post($this->user_id, 'customer');

            if (!$this->user_id || empty($this->user_id)) {
                return false;
            }

            $user = new WP_User($this->user_id);

            if (!$user) {
                return false;
            }

            $customer_name = trim($user->first_name . ' ' . $user->last_name);
            if ($customer_name == null || empty($customer_name)) {
                $customer_name = trim($user->data->display_name);
            }

            $customer = array();

            if (empty($this->only_items)) {
                $customer_general_info = array(
                    'username' => $user->data->user_login,
                    'nickname' => $user->nickname,
                    'display_name' => $user->data->display_name,
                    'email' => $user->data->user_email,
                    'website' => $user->data->user_url,
                    'date_add' => $user->user_registered,
                );
                $customer_billing_info = array(
                    'b_firstname' => $user->billing_first_name,
                    'b_lastname' => $user->billing_last_name,
                    'b_company' => $user->billing_company,
                    'b_address_1' => $user->billing_address_1,
                    'b_address_2' => $user->billing_address_2,
                    'b_city' => $user->billing_city,
                    'b_postcode' => $user->billing_postcode,
                    'b_state' => $user->billing_state,
                    'b_country' => $user->billing_country,
                    'b_phone' => $user->billing_phone,
                    'b_email' => $user->billing_email,
                );
                $customer_shipping_info = array(
                    's_firstname' => $user->shipping_first_name,
                    's_lastname' => $user->shipping_last_name,
                    's_company' => $user->shipping_company,
                    's_address_1' => $user->shipping_address_1,
                    's_address_2' => $user->shipping_address_2,
                    's_city' => $user->shipping_city,
                    's_postcode' => $user->shipping_postcode,
                    's_state' => $user->shipping_state,
                    's_country' => $user->shipping_country,
                );

                $customer = array(
                    'customer_id' => $user->ID,
                    'name' => $customer_name,
                    'general_info' => $customer_general_info,
                    'billing_info' => $customer_billing_info,
                    'shipping_info' => $customer_shipping_info,
                );
            }

            $customer_orders = $this->_get_customer_orders($user->ID);
            $customer_order_totals = $this->_get_customer_orders_total($user->ID);

        } else {

            // Get not register customer(guest) info
            $query_from_part = " FROM `{$wpdb->posts}` AS tot
                                    LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_fname ON meta_fname.post_id = tot.ID
                                            AND meta_fname.meta_key =  '%s'
                                    LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_lname ON meta_lname.post_id = tot.ID
                                            AND meta_lname.meta_key = '%s'
                                    LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_company ON meta_company.post_id = tot.ID
                                            AND meta_company.meta_key = '%s'
                                    LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_adr1 ON meta_adr1.post_id = tot.ID
                                            AND meta_adr1.meta_key = '%s'
                                    LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_adr2 ON meta_adr2.post_id = tot.ID
                                            AND meta_adr2.meta_key = '%s'
                                    LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_city ON meta_city.post_id = tot.ID
                                            AND meta_city.meta_key = '%s'
                                    LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_postcode ON meta_postcode.post_id = tot.ID
                                            AND meta_postcode.meta_key = '%s'
                                    LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_state ON meta_state.post_id = tot.ID
                                            AND meta_state.meta_key = '%s'
                                    LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_country ON meta_country.post_id = tot.ID
                                            AND meta_country.meta_key = '%s'
                                    LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_email ON meta_email.post_id = tot.ID
                                            AND meta_email.meta_key = '_billing_email' ";

            $query_billing_from = sprintf($query_from_part, '_billing_first_name', '_billing_last_name',
                '_billing_company', '_billing_address_1', "_billing_address_2", '_billing_city', '_billing_postcode',
                '_billing_state', '_billing_country');

            $query_billing_from .= "LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_phone ON meta_phone.post_id = tot.ID
                                            AND meta_phone.meta_key = '_billing_phone'";

            $query_shipping_from = sprintf($query_from_part, '_shipping_first_name', '_shipping_last_name',
                '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city',
                '_shipping_postcode',
                '_shipping_state', '_shipping_country');

            $query_where_part = " WHERE meta_email.meta_value LIKE '%" . $this->email . "%' GROUP BY meta_email.meta_value ";
            $query_general_info = "SELECT meta_email.meta_value AS email,
                                    tot.post_date AS date_add,
                                    CONCAT(meta_fname.meta_value, ' ', meta_lname.meta_value) AS display_name"
                . $query_billing_from . $query_where_part;

            $query_billing_info = 'SELECT
                                        meta_email.meta_value AS b_email,
                                        meta_fname.meta_value AS b_firstname,
                                        meta_lname.meta_value AS b_lastname,
                                        meta_company.meta_value AS b_company,
                                        meta_adr1.meta_value AS b_address_1,
                                        meta_adr2.meta_value AS b_address_2,
                                        meta_city.meta_value AS b_city,
                                        meta_postcode.meta_value AS b_postcode,
                                        meta_state.meta_value AS b_state,
                                        meta_country.meta_value AS b_country,
                                        meta_phone.meta_value AS b_phone' . $query_billing_from . $query_where_part;

            $query_shipping_info = 'SELECT
                                        meta_fname.meta_value AS s_firstname,
                                        meta_lname.meta_value AS s_lastname,
                                        meta_company.meta_value AS s_company,
                                        meta_adr1.meta_value AS s_address_1,
                                        meta_adr2.meta_value AS s_address_2,
                                        meta_city.meta_value AS s_city,
                                        meta_postcode.meta_value AS s_postcode,
                                        meta_state.meta_value AS s_state,
                                        meta_country.meta_value AS s_country' . $query_shipping_from . $query_where_part;

            $general_info = $wpdb->get_row($query_general_info);
            $customer = array(
                'customer_id' => -1,
                'name' => $general_info->display_name,
                'general_info' => $general_info,
                'billing_info' => $wpdb->get_row($query_billing_info),
                'shipping_info' => $wpdb->get_row($query_shipping_info),
            );

            $customer_orders = $this->_get_customer_orders($this->user_id, $this->email);
            $customer_order_totals = $this->_get_customer_orders_total($this->user_id, $this->email);
        }

        $customer_info = array('user_info' => $customer, 'customer_orders' => $customer_orders);
        $customer_info = array_merge($customer_info, $customer_order_totals);

        return $customer_info;
    }

    private function _get_customer_orders($id, $email = "")
    {
        global $wpdb;

        if ($id != -1) {
            $customer = new WP_User($id);

            if ($customer->ID == 0) {
                return false;
            }
        }

        $sql = "SELECT
                    posts.ID AS id_order,
                    meta_total.meta_value AS total_paid,
                    meta_curr.meta_value AS currency_code,
                    posts.post_status AS order_status_id,
                    posts.post_date as date_add,
                    (SELECT SUM(meta_value) FROM `{$wpdb->prefix}woocommerce_order_itemmeta` WHERE order_item_id = order_items.order_item_id AND meta_key = '_qty') AS pr_qty
                FROM `$wpdb->posts` AS posts
                    LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts.ID = meta.post_id
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_email ON meta_email.post_id = posts.ID AND meta_email.meta_key = '_billing_email'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_total ON meta_total.post_id = posts.ID AND meta_total.meta_key = '_order_total'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_curr ON meta_curr.post_id = posts.ID AND meta_curr.meta_key = '_order_currency'
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_items` AS order_items on order_items.order_id = posts.ID AND order_item_type = 'line_item'
                WHERE posts.post_type = 'shop_order' 
                    AND meta.meta_key = '_customer_user'";

        if ($id == -1 && !empty($email)) {
            $sql .= " AND meta_email.meta_value = '%s' ";
            $value = $email;
        } else {
            $sql .= " AND meta.meta_value = '%s' ";
            $value = $id;
        }

        if (!empty($this->status_list_hide)) {
            $sql .= " AND posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        $sql .= ' GROUP BY order_items.order_id';

        $sql .= sprintf(" LIMIT %d, %d", (($this->page - 1) * $this->show), $this->show);

        $query = $wpdb->prepare($sql, $value);

        $orders = array();
        $results = $wpdb->get_results($query, ARRAY_A);
        foreach ($results as $order) {
            $order['total_paid'] = mobassist_nice_price($order['total_paid'], $order['currency_code']);
            $order['ord_status'] = mobassist_get_order_status_name($order['id_order'], $order['order_status_id']);
            $order['ord_status_code'] = $order['order_status_id'];
            $orders[] = $order;
        }

        return $orders;
    }

    private function _get_customer_orders_total($id, $email = "")
    {
        global $wpdb;

        if ($id != -1) {
            $customer = new WP_User($id);

            if ($customer->ID == 0) {
                return false;
            }
        }

        $sql = "SELECT COUNT(DISTINCT(posts.ID)) AS c_orders_count, SUM(meta_total.meta_value) AS sum_ords
                FROM `$wpdb->posts` AS posts
                    LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts.ID = meta.post_id
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_email ON meta_email.post_id = posts.ID 
                     AND meta_email.meta_key = '_billing_email'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_total ON meta_total.post_id = posts.ID 
                     AND meta_total.meta_key = '_order_total'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_curr ON meta_curr.post_id = posts.ID 
                     AND meta_curr.meta_key = '_order_currency'
                WHERE meta.meta_key = '_customer_user'
                    AND posts.post_type = 'shop_order'";

        if ($id == -1 && !empty($email)) {
            $sql .= " AND meta_email.meta_value = '%s' ";
            $value = $email;
        } else {
            $sql .= " AND meta.meta_value = '%s' ";
            $value = $id;
        }

        if (!empty($this->status_list_hide)) {
            $sql .= " AND posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        $sql = $wpdb->prepare($sql, $value);

        $orders_total = array('c_orders_count' => 0, 'sum_ords' => 0);
        if ($row_total = $wpdb->get_row($sql, ARRAY_A)) {
            $orders_total = $row_total;
        }

        $orders_total['sum_ords'] = mobassist_nice_price($orders_total['sum_ords'], $this->currency);
        $orders_total['c_orders_count'] = $orders_total['c_orders_count'];

        return $orders_total;
    }

    public function search_products()
    {
        global $wpdb;

        $fields = 'SELECT
            posts.ID AS product_id,
            posts.post_title AS name,
            posts.post_status AS published_status,
            meta_price.meta_value AS price,
            meta_sku.meta_value AS sku,
            meta_stock_status.meta_value AS status_code,
            meta_stock.meta_value AS quantity';

        $fields_total = 'SELECT COUNT(DISTINCT(posts.ID)) AS count_prods';

        $sql = " FROM `$wpdb->posts` AS posts
            LEFT JOIN `$wpdb->postmeta` AS meta_price ON meta_price.post_id = posts.ID AND meta_price.meta_key = '_price'
            LEFT JOIN `$wpdb->postmeta` AS meta_sku ON meta_sku.post_id = posts.ID AND meta_sku.meta_key = '_sku'
            LEFT JOIN `$wpdb->postmeta` AS meta_stock ON meta_stock.post_id = posts.ID AND meta_stock.meta_key = '_stock'
            LEFT JOIN `$wpdb->postmeta` AS meta_stock_status ON meta_stock_status.post_id = posts.ID 
                AND meta_stock_status.meta_key = '_stock_status'";

        if (!function_exists('wc_get_order_status_name')) {
            $sql .= " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts.ID
                                AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                            LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
        }

        $sql .= " WHERE posts.post_type = 'product'";
        $products = $this->_get_products($fields, $fields_total, $sql, true);

        return $products;
    }

    private function _get_products($fields, $fields_total, $sql, $from_products = false, $all_customers_fiels = false)
    {
        global $wpdb;
        $query_where_parts = array();

        if (isset($this->show_all_customers) && !$this->show_all_customers && $all_customers_fiels) {
            $query_where_parts[] = " (cap.meta_key = '{$wpdb->prefix}capabilities' AND cap.meta_value LIKE '%customer%') ";
        }

        $query = $fields . $sql;
        $query_total = $fields_total . $sql;

        if (!empty($this->params) && !empty($this->val)) {
            $params = explode('|', $this->params);

            foreach ($params as $param) {
                switch ($param) {
                    case 'pr_id':
                        $query_params_parts[] = sprintf(" posts.ID LIKE '%%%s%%'", $this->val);
                        break;
                    case 'pr_sku':
                        $query_params_parts[] = sprintf(" meta_sku.meta_value LIKE '%%%s%%'", $this->val);
                        break;
                    case 'pr_name':
                        $query_params_parts[] = sprintf(" posts.post_title LIKE '%%%s%%'", $this->val);
                        break;
                }
            }
        }

        if (!empty($this->status_list_hide)) {
            $query_where_parts[] = " posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        if (!empty($this->statuses)) {
            if (function_exists('wc_get_order_status_name')) {
                $query_where_parts[] = sprintf(" posts_orders.post_status IN ('%s')",
                    $this->get_filter_statuses($this->statuses));
            } else {
                $query_where_parts[] = sprintf(" status_terms.slug IN ('%s')",
                    $this->get_filter_statuses($this->statuses));
            }
        }

        if (!empty($this->products_from)) {
            $query_where_parts[] = sprintf(" UNIX_TIMESTAMP(CONVERT_TZ(posts_orders.post_date, '+00:00', @@global.time_zone)) >= '%d'",
                strtotime($this->products_from . ' 00:00:00'));
        }

        if (!empty($this->products_to)) {
            $query_where_parts[] = sprintf(" UNIX_TIMESTAMP(CONVERT_TZ(posts_orders.post_date, '+00:00', @@global.time_zone)) <= '%d'",
                strtotime($this->products_to . ' 23:59:59'));
        }

        if (!empty($query_params_parts)) {
            $query_where_parts[] = ' ( ' . implode(' OR ', $query_params_parts) . ' )';
        }


        if (!empty($query_where_parts)) {
            $query .= ' AND ' . implode(' AND ', $query_where_parts);
            $query_total .= ' AND ' . implode(' AND ', $query_where_parts);
        }

        if (empty($this->sort_by)) {
            $this->sort_by = 'id';
        }

        if (!empty($this->group_by_product_id)) {
            $query .= ' GROUP BY posts.ID ORDER BY ';
        } elseif ($from_products) {
            $query .= ' GROUP BY posts.ID ORDER BY ';
        } else {
            $query .= ' GROUP BY order_items.order_id, posts.ID, order_items.order_item_name ORDER BY ';
        }

        switch ($this->sort_by) {
            case 'id':
                $dir = $this->getSortDirection();
                $query .= 'posts.ID ' . $dir;
                break;
            case 'name':
                $dir = $this->getSortDirection('ASC');
                if ($from_products) {
                    $query .= 'posts.post_title ' . $dir;
                } else {
                    $query .= 'order_items.order_item_name ' . $dir;
                }
                break;
            case 'total':
                $dir = $this->getSortDirection();
                if ($from_products) {
                    $query .= 'CAST(meta_price.meta_value AS unsigned) ' . $dir;
                } else {
                    $query .= 'CAST(meta_line_total.meta_value AS unsigned) ' . $dir;
                }
                break;
            case 'qty':
                $dir = $this->getSortDirection();
                if ($from_products) {
                    $query .= 'CAST(meta_stock.meta_value AS unsigned) ' . $dir;
                } else {
                    $query .= 'CAST(meta_qty.meta_value AS unsigned) ' . $dir;
                }
                break;
            case 'price':
                $dir = $this->getSortDirection();
                if ($from_products) {
                    $query .= 'CAST(meta_price.meta_value AS unsigned) ' . $dir;
                }
                break;
            case 'status':
                $dir = $this->getSortDirection();
                if ($from_products) {
                    $query .= 'meta_stock_status.meta_value ' . $dir;
                }
                break;
        }

        $query .= sprintf(" LIMIT %d, %d", (($this->page - 1) * $this->show), $this->show);

        $products_count = array('count_prods' => 0,);
        if ($row_total = $wpdb->get_row($query_total, ARRAY_A)) {
            $products_count = $row_total;
        }

        $products = array();
        $results = $wpdb->get_results($query, ARRAY_A);
        foreach ($results as $product) {
            $product['sale_price'] = isset($product['sale_price']) ? mobassist_nice_price($product['sale_price'],
                $this->currency) : null;
            $product['price'] = mobassist_nice_price($product['price'], $this->currency, false, true);
            $product['quantity'] = (int)$product['quantity'];
            $product['product_type'] = $this->_get_product_type($product['product_id']);

            if (!in_array($product['product_type'], array('Simple', 'Grouped'))) {
                unset($product['status_code']);
            }

            if (empty($this->without_thumbnails)) {
                $attachment_id = get_post_thumbnail_id($product['product_id']);
                $id_image = get_image_url($attachment_id, 'thumbnail');
                $product['thumbnail'] = $id_image;
            }

            $products[] = $product;
        }

        return array('products_count' => mobassist_nice_count($products_count['count_prods']), 'products' => $products);;
    }


//== PUSH ===========================================================================

    public function search_products_ordered()
    {
        if (!empty($this->group_by_product_id)) {
            $result = $this->search_products_ordered_by_product();
        } else {
            $result = $this->search_products_ordered_by_order();
        }

        return $result;
    }

    public function search_products_ordered_by_product()
    {
        global $wpdb;

        $fields = 'SELECT
            posts.ID AS product_id,
            order_items.order_item_name AS name,
            meta_sku.meta_value AS sku,
            SUM(meta_qty.meta_value) AS quantity,
            SUM(meta_line_total.meta_value) AS price,
            meta_variation_id.meta_value AS variation_id
            ';

        $fields_total = 'SELECT COUNT(DISTINCT(posts.ID)) AS count_prods';

        $sql = " FROM `{$wpdb->prefix}woocommerce_order_items` AS order_items
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_product_id ON meta_product_id.order_item_id = order_items.order_item_id AND meta_product_id.meta_key = '_product_id'
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_variation_id ON meta_variation_id.order_item_id = order_items.order_item_id AND meta_variation_id.meta_key = '_variation_id'
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_qty ON meta_qty.order_item_id = order_items.order_item_id AND meta_qty.meta_key = '_qty'
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_line_total ON meta_line_total.order_item_id = order_items.order_item_id AND meta_line_total.meta_key = '_line_total'
                    LEFT JOIN `{$wpdb->postmeta}` AS postmeta_thumbnail ON postmeta_thumbnail.post_id = meta_product_id.meta_value AND (postmeta_thumbnail.meta_key = '_thumbnail_id')

                    LEFT JOIN `{$wpdb->posts}` AS posts ON posts.ID = meta_product_id.meta_value
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_sku ON posts.ID = meta_sku.post_id AND meta_sku.meta_key = '_sku'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_stock ON posts.ID = meta_stock.post_id AND meta_stock.meta_key = '_stock'
                    LEFT JOIN `{$wpdb->posts}` AS posts_orders ON posts_orders.ID = order_items.order_id";

        if (isset($this->show_all_customers) && !$this->show_all_customers) {
            $query_for_registered_customers = " LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts_orders.ID = meta.post_id AND meta.meta_key = '_customer_user'
                               LEFT JOIN `{$wpdb->users}` AS c ON c.ID = meta.meta_value
                               LEFT JOIN `{$wpdb->usermeta}` AS cap ON cap.user_id = c.ID ";
            $sql .= $query_for_registered_customers;
        }

        if (!function_exists('wc_get_order_status_name')) {
            $sql .= " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts_orders.ID
                            AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                        LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
        }

        $sql .= " WHERE order_items.order_item_type = 'line_item'
                AND posts.post_type = 'product'";

        if (!empty($this->status_list_hide)) {
            $sql .= " AND posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        if (isset($this->show_all_customers) && !$this->show_all_customers) {
            $products = $this->_get_products($fields, $fields_total, $sql, false, true);
        } else {
            $products = $this->_get_products($fields, $fields_total, $sql, false);
        }


        return $products;
    }

    public function search_products_ordered_by_order()
    {
        global $wpdb;

        $fields = 'SELECT
            posts.ID AS product_id,
            order_items.order_id AS order_id,
            order_items.order_item_name AS name,
            meta_sku.meta_value AS sku,
            CAST(meta_qty.meta_value AS unsigned) AS quantity,
            CAST(meta_line_total.meta_value AS unsigned) AS price,
            meta_variation_id.meta_value AS variation_id
            ';

        $fields_total = 'SELECT COUNT(posts.ID) AS count_prods';

        $sql = " FROM `{$wpdb->prefix}woocommerce_order_items` AS order_items
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_product_id ON meta_product_id.order_item_id = order_items.order_item_id AND meta_product_id.meta_key = '_product_id'
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_variation_id ON meta_variation_id.order_item_id = order_items.order_item_id AND meta_variation_id.meta_key = '_variation_id'
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_qty ON meta_qty.order_item_id = order_items.order_item_id AND meta_qty.meta_key = '_qty'
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_line_total ON meta_line_total.order_item_id = order_items.order_item_id AND meta_line_total.meta_key = '_line_total'
                    LEFT JOIN `{$wpdb->postmeta}` AS postmeta_thumbnail ON postmeta_thumbnail.post_id = meta_product_id.meta_value AND (postmeta_thumbnail.meta_key = '_thumbnail_id')

                    LEFT JOIN `{$wpdb->posts}` AS posts ON posts.ID = meta_product_id.meta_value
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_sku ON posts.ID = meta_sku.post_id AND meta_sku.meta_key = '_sku'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_stock ON posts.ID = meta_stock.post_id AND meta_stock.meta_key = '_stock'
                    LEFT JOIN `{$wpdb->posts}` AS posts_orders ON posts_orders.ID = order_items.order_id";

        if (isset($this->show_all_customers) && !$this->show_all_customers) {
            $query_for_registered_customers = " LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts_orders.ID = meta.post_id AND meta.meta_key = '_customer_user'
                               LEFT JOIN `{$wpdb->users}` AS c ON c.ID = meta.meta_value
                               LEFT JOIN `{$wpdb->usermeta}` AS cap ON cap.user_id = c.ID ";
            $sql .= $query_for_registered_customers;
        }

        if (!function_exists('wc_get_order_status_name')) {
            $sql .= " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts_orders.ID
                            AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                        LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
        }

        $sql .= " WHERE order_items.order_item_type = 'line_item'
                AND posts.post_type = 'product'";

        if (isset($this->show_all_customers) && !$this->show_all_customers) {
            $products = $this->_get_products($fields, $fields_total, $sql, false, true);
        } else {
            $products = $this->_get_products($fields, $fields_total, $sql, false);
        }

        return $products;
    }

    public function search_products_ordered_old()
    {
        global $wpdb;

        $fields = 'SELECT
            posts.ID AS product_id,
            posts_orders.ID AS order_id,
            posts.post_title AS name,
            meta_price.meta_value AS price,
            meta_sku.meta_value AS sku,
            meta_stock.meta_value AS quantity';

        $fields_total = 'SELECT COUNT(DISTINCT(posts.ID)) AS count_prods';

        $sql = " FROM `{$wpdb->prefix}woocommerce_order_items` AS order_items
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_product_id ON meta_product_id.order_item_id = order_items.order_item_id AND meta_product_id.meta_key = '_product_id'
                    LEFT JOIN `{$wpdb->posts}` AS posts ON posts.ID = meta_product_id.meta_value
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_price ON posts.ID = meta_price.post_id AND meta_price.meta_key = '_price'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_sku ON posts.ID = meta_sku.post_id AND meta_sku.meta_key = '_sku'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_stock ON posts.ID = meta_stock.post_id AND meta_stock.meta_key = '_stock'
                    LEFT JOIN `{$wpdb->posts}` AS posts_orders ON posts_orders.ID = order_items.order_id";

        if (!function_exists('wc_get_order_status_name')) {
            $sql .= " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts_orders.ID
                            AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                        LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
        }

        $sql .= " WHERE order_items.order_item_type = 'line_item'
                AND posts.post_type = 'product'";

        if (!empty($this->status_list_hide)) {
            $sql .= " AND posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        $products = $this->_get_products($fields, $fields_total, $sql);

        return $products;
    }

    public function get_products_info()
    {
        global $wpdb;

        $this->product_id = mobassist_validate_post($this->product_id, 'product');

        if (!$this->product_id || empty($this->product_id)) {
            return false;
        }

        $sql_total_ordered = "SELECT SUM(meta_items_qty.meta_value)
            FROM `{$wpdb->prefix}woocommerce_order_itemmeta` AS order_itemmeta
              LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_items_qty ON order_itemmeta.order_item_id = meta_items_qty.order_item_id AND meta_items_qty.meta_key = '_qty'
            WHERE order_itemmeta.meta_key LIKE '_product_id' AND order_itemmeta.meta_value = posts.ID";

        $sql = "SELECT
                posts.ID AS product_id,
                posts.post_title AS name,
                meta_price.meta_value AS price,
                meta_sku.meta_value AS sku,
                meta_stock.meta_value AS quantity,
                ({$sql_total_ordered}) AS total_ordered,
                posts.post_status
            FROM `$wpdb->posts` AS posts
                LEFT JOIN `$wpdb->postmeta` AS meta_price ON meta_price.post_id = posts.ID AND meta_price.meta_key = '_price'
                LEFT JOIN `$wpdb->postmeta` AS meta_sku ON meta_sku.post_id = posts.ID AND meta_sku.meta_key = '_sku'
                LEFT JOIN `$wpdb->postmeta` AS meta_stock ON meta_stock.post_id = posts.ID AND meta_stock.meta_key = '_stock'
            WHERE posts.post_type = 'product'
                AND posts.ID = '%d'";

        if (!empty($this->status_list_hide)) {
            $sql .= " AND posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        $sql = sprintf($sql, $this->product_id);

        $product = $wpdb->get_row($sql, ARRAY_A);

        $product['sale_price'] = isset($product['sale_price']) ? mobassist_nice_price($product['sale_price'],
            $this->currency) : null;
        $product['price'] = mobassist_nice_price($product['price'], $this->currency, false, true);
        $product['quantity'] = (int)$product['quantity'];
        $product['total_ordered'] = (int)$product['total_ordered'];

        $stat = 'Undefined';

        switch (strtolower($product['post_status'])) {
            case 'publish' :
            case 'private' :
                $stat = __('Published', 'mobile-assistant-connector');
                break;
            case 'future' :
                $stat = __('Scheduled', 'mobile-assistant-connector');
                break;
            case 'pending' :
                $stat = __('Pending Review', 'mobile-assistant-connector');
                break;
            case 'draft' :
                $stat = __('Draft', 'mobile-assistant-connector');
                break;
            case 'trash' :
                $stat = __('Trash', 'mobile-assistant-connector');
                break;
            default:
                $stat = $product['post_status'];
        }
        $product['forsale'] = $stat;

        $product['product_type'] = $this->_get_product_type($this->product_id);

        // get product images
        $productWP = new WC_product($this->product_id);
        $attachment_ids = $this->is_v3() ? $productWP->get_gallery_image_ids() : $productWP->get_gallery_attachment_ids();

        $product_image_gallery = array();
        $product_main_image = array();
        $image_main = array();

        if (empty($this->without_thumbnails)) {
            foreach ($attachment_ids as $attachment_id) {
                $image = array(
                    'small' => get_image_url($attachment_id, 'shop_catalog'),
                    'large' => get_image_url($attachment_id, 'large'),
                );
                $product_image_gallery[] = $image;
            }

            $attachment_id = get_post_thumbnail_id($product['product_id']);

            $id_image_large = get_image_url($attachment_id, 'large');
            $product_main_image['id_image_large'] = $id_image_large;

            $id_image = get_image_url($attachment_id, 'shop_catalog');
            $product_main_image['id_image'] = $id_image;

            $image_main[] = array(
                'small' => $product_main_image['id_image'],
                'large' => $product_main_image['id_image_large'],
            );
        }
        $product['images'] = array_merge($image_main, $product_image_gallery);

        return $product;
    }

    public function get_products_descr()
    {
        global $wpdb;

        $sql = "SELECT post_content AS descr, post_excerpt AS short_descr FROM `$wpdb->posts` WHERE post_type = 'product' AND ID = '%d'";

        $sql = sprintf($sql, $this->product_id);

        if ($product_descr = $wpdb->get_row($sql, ARRAY_A)) {
            return $product_descr;
        }

        return false;
    }

    public function get_product_to_edit()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            return array('error' => 'woocommerce_version_less_3');
        }

        $this->product_id = mobassist_validate_post($this->product_id, 'product');

        if (empty($this->product_id)) {
            return false;
        }

        $product = new WC_Product($this->product_id);
        $post_data = $this->is_v3() ? get_post($this->product_id) : $product->get_post_data();

        $result = array(
            'product' => array(
                'product_id' => $this->product_id,
                'name' => $post_data->post_title,
                'description' => $post_data->post_content,
                'description_short' => $post_data->post_excerpt,
                'status' => $post_data->post_status,
                'comment_status' => $post_data->comment_status,
                'menu_order' => $post_data->menu_order,
                'sku' => get_post_meta($this->product_id, '_sku', true),
                'regular_price' => get_post_meta($this->product_id, '_regular_price', true),
                'sale_price' => get_post_meta($this->product_id, '_sale_price', true),
                'sale_price_dates_from' => get_post_meta($this->product_id, '_sale_price_dates_from', true),
                'sale_price_dates_to' => get_post_meta($this->product_id, '_sale_price_dates_to', true),
                'manage_stock' => get_post_meta($this->product_id, '_manage_stock', true),
                'stock' => get_post_meta($this->product_id, '_stock', true),
                'backorders' => get_post_meta($this->product_id, '_backorders', true),
                'stock_status' => get_post_meta($this->product_id, '_stock_status', true),
                'sold_individually' => get_post_meta($this->product_id, '_sold_individually', true),
                'purchase_note' => get_post_meta($this->product_id, '_purchase_note', true),
                'product_url' => get_post_meta($this->product_id, '_product_url', true),
                'button_text' => get_post_meta($this->product_id, '_button_text', true),
                'virtual' => get_post_meta($this->product_id, '_virtual', true),
                'downloadable' => get_post_meta($this->product_id, '_downloadable', true),
                'download_limit' => get_post_meta($this->product_id, '_download_limit', true),
                'download_expiry' => get_post_meta($this->product_id, '_download_expiry', true),
                'downloadable_files' => get_post_meta($this->product_id, '_downloadable_files', true),
            ),
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'product_types' => wc_get_product_types(),
            'product_stock_statuses' => wc_get_product_stock_status_options(),
            'backorder_options' => wc_get_product_backorder_options(),
            'product_statuses' => self::getProductStatuses(),
        );

        // Get product type
        $terms = wp_get_object_terms($this->product_id, 'product_type');
        $result['product']['product_type'] = $terms[0]->name;

        // Get product main image
        $thumbnail_id = get_post_meta($this->product_id, '_thumbnail_id', true);
        if (!empty($thumbnail_id)) {
            $result['product']['main_image'] = array(
                'id_image' => $thumbnail_id,
                'image_url' => get_image_url($thumbnail_id)
            );
        }

        // Get product gallery images
        $images = array();
        $image_ids = $product->get_gallery_image_ids();
        $count = count($image_ids);
        for ($i = 0; $i < $count; $i++) {
            $images[] = array(
                'id_image' => $image_ids[$i],
                'image_url' => get_image_url($image_ids[$i])
            );
        }
        $result['images'] = $images;

        // Max file size allowed to upload
        $result['max_file_upload_in_bytes'] = self::getMaxFileUploadInBytes();

        return $result;
    }

    private static function getProductStatuses()
    {
        $statuses = get_post_statuses();

        if (array_key_exists('private', $statuses)) {
            unset($statuses['private']);
        }

        return $statuses;
    }

    private static function getMaxFileUploadInBytes()
    {
        //select maximum upload size
        $max_upload = self::calculateBytes(ini_get('upload_max_filesize'));

        //select post limit
        $max_post = self::calculateBytes(ini_get('post_max_size'));

        //select memory limit
        $memory_limit = self::calculateBytes(ini_get('memory_limit'));

        // return the smallest of them, this defines the real limit
        return min($max_upload, $max_post, $memory_limit);
    }

    private static function calculateBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = floatval($val);

        switch ($last) {
            case 'g':
                $val *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $val *= 1024 * 1024;
                break;
            case 'k':
                $val *= 1024;
                break;
        }

        return $val;
    }

    public function update_product()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            return array('error' => 'woocommerce_version_less_3');
        }

        if (empty($this->product)) {
            return false;
        }

        $product_data = json_decode(stripslashes(urldecode($this->product)), true);

        return self::saveProductData($product_data, false)
            ? array('success' => 'true')
            : array('error' => 'something_wrong');
    }

    private static function saveProductData($product_data, $is_new_product)
    {
        global $wpdb;

        if (empty($product_data) || !isset($product_data['product_id']) || $product_data['product_id'] < 1) {
            return false;
        }

        if (!empty($_FILES)) {
            if (isset($product_data['downloadable_file']) && $product_data['downloadable_file'] == 1) {
                return self::storeDownloadableFile($product_data['product_id'], $product_data['file_name'], self::_sanitize_files_array());
            }

            return self::storeImage(
                $product_data['product_id'],
                self::_sanitize_files_array(),
                !empty($product_data['main_image']) && $product_data['main_image'] == 1
            );
        }

        $post = get_post($product_data['product_id'], ARRAY_A);
        $post['ID'] = $product_data['product_id'];
        $post['post_title'] = $product_data['name'];
        $post['post_content'] = $product_data['description'];
        $post['post_excerpt'] = $product_data['description_short'];
        $post['post_status'] = $product_data['status'];
        $post['comment_status'] = $product_data['comment_status'];
        $post['menu_order'] = $product_data['menu_order'];

        $everything_updated = true;

        if (!wp_update_post($post)) {
            $everything_updated = false;
        }

        update_post_meta($product_data['product_id'], '_sku', $product_data['sku']);
        update_post_meta($product_data['product_id'], '_virtual', $product_data['virtual']);
        update_post_meta($product_data['product_id'], '_downloadable', $product_data['downloadable']);
        update_post_meta($product_data['product_id'], '_download_limit', $product_data['download_limit']);
        update_post_meta($product_data['product_id'], '_download_expiry', $product_data['download_expiry']);
        update_post_meta($product_data['product_id'], '_manage_stock', $product_data['manage_stock']);
        update_post_meta($product_data['product_id'], '_stock', $product_data['stock']);
        update_post_meta($product_data['product_id'], '_backorders', $product_data['backorders']);
        update_post_meta($product_data['product_id'], '_stock_status', $product_data['stock_status']);
        update_post_meta($product_data['product_id'], '_sold_individually', $product_data['sold_individually']);
        update_post_meta($product_data['product_id'], '_purchase_note', $product_data['purchase_note']);
        update_post_meta($product_data['product_id'], '_product_url', $product_data['product_url']);
        update_post_meta($product_data['product_id'], '_button_text', $product_data['button_text']);

        // Correct Update of price fields
        $regular_price = (string)$product_data['regular_price'];
        update_post_meta($product_data['product_id'], '_regular_price', $regular_price);
        update_post_meta($product_data['product_id'], '_price', $regular_price);

        $sale_price = (string)$product_data['sale_price'];
        if ((float)$regular_price < (float)$sale_price) {
            $sale_price = $regular_price;
        }
        update_post_meta($product_data['product_id'], '_sale_price', $sale_price);
        if ((float)$sale_price > 0) {
            update_post_meta($product_data['product_id'], '_price', $sale_price);
        }
        update_post_meta($product_data['product_id'], '_sale_price_dates_from', $product_data['sale_price_dates_from']);
        update_post_meta($product_data['product_id'], '_sale_price_dates_to', $product_data['sale_price_dates_to']);

        // Delete main image
        if ($product_data['deleted_main_image'] == 1) {
            delete_post_meta($product_data['product_id'], '_thumbnail_id');
        }

        // Update product gallery
        if (!empty($product_data['deleted_images'])) {
            $delete_gallery_ids = explode(',', $product_data['deleted_images']);
            $image_ids = self::_getGalleryImageIds($product_data['product_id']);

            for ($i = 0, $count = count($delete_gallery_ids); $i < $count; $i++) {
                $key = array_search($delete_gallery_ids[$i], $image_ids);

                if ($key !== false) {
                    unset($image_ids[$key]);
                }
            }

            update_post_meta(
                $product_data['product_id'],
                '_product_image_gallery',
                implode(',', $image_ids)
            );
        }

        // Update downloadable files
        $downloadable_files = !empty($product_data['downloadable_files'])
            ? json_decode($product_data['downloadable_files'], true)
            : array();
        self::updateDownloadableFiles($downloadable_files, $product_data['product_id']);

        $wpdb->update(
            $wpdb->term_relationships,
            array(
                'term_taxonomy_id' => $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT `term_id` FROM `$wpdb->terms` WHERE `slug` = '%s'",
                        $product_data['product_type']
                    )
                )
            ),
            array('object_id' => $product_data['product_id'])
        );

        if (!$is_new_product) {
            return $everything_updated;
        }

        $classname = WC_Product_Factory::get_product_classname($product_data['product_id'],
            $product_data['product_type']);
        $product = new $classname($product_data['product_id']);
        $errors = $product->set_props(
            array(
                'cross_sell_ids' => array(),
                'upsell_ids' => array(),
                'default_attributes' => array(),
                'downloads' => array(),
                'height' => '',
                'width' => '',
                'length' => '',
                'weight' => '',
            )
        );

        if (!is_wp_error($errors)) {
            $product->save();
        }

        return $everything_updated;
    }

    private static function storeDownloadableFile($product_id, $file_name, $downloadable_file)
    {
        if ($downloadable_file['file']['error'] != UPLOAD_ERR_OK) {
            return array('error' => 'upload_error');
        }

        $time = current_time('mysql');
        if ($post = get_post($product_id)) {
            if (substr($post->post_date, 0, 4) > 0) {
                $time = $post->post_date;
            }
        }

        if (!($upload_dir = wp_upload_dir($time))) {
            return array('error' => 'upload_error');
        }

        $upload_dir['path'] = $upload_dir['basedir'] . '/woocommerce_uploads' . $upload_dir['subdir'];
        $upload_dir['url'] = $upload_dir['baseurl'] . '/woocommerce_uploads' . $upload_dir['subdir'];

        if (!file_exists($upload_dir['path'])) {
            if (!mkdir($concurrentDirectory = $upload_dir['path'], 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        $filename = wp_unique_filename($upload_dir['path'], $downloadable_file['file']['name']);

        // Move the file to the uploads dir
        $new_file = $upload_dir['path'] . "/$filename";
        if (!@move_uploaded_file($downloadable_file['file']['tmp_name'], $new_file)) {
            return array('error' => 'upload_error');
        }

        // Set correct file permissions
        $stat = stat(dirname($new_file));
        $perms = $stat['mode'] & 0000666;
        @chmod($new_file, $perms);

        // Compute the URL
        $url = $upload_dir['url'] . "/$filename";

        if (is_multisite()) {
            delete_transient('dirsize_cache');
        }

        $name = $downloadable_file['file']['name'];
        $name_parts = pathinfo($name);
        $name = trim(substr($name, 0, -(1 + strlen($name_parts['extension']))));

        $wp_filetype = wp_check_filetype_and_ext(
            $downloadable_file['file']['tmp_name'],
            $downloadable_file['file']['name']
        );
        $type = empty($wp_filetype['type']) ? '' : $wp_filetype['type'];

        $title = sanitize_text_field($name);

        // Construct the attachment array
        $attachment = array(
            'post_mime_type' => $type,
            'guid' => $url,
            'post_parent' => $product_id,
            'post_title' => $title,
            'post_content' => '',
            'post_excerpt' => '',
        );

        // This should never be set as it would then overwrite an existing attachment.
        unset($attachment['ID']);

        // Save the data
        $id = wp_insert_attachment($attachment, $new_file, $product_id);
        if (!is_wp_error($id)) {
            $downloadable_files = get_post_meta($product_id, '_downloadable_files', true);

            $downloadable_files_prepared = array();
            if (!empty($downloadable_files)) {
                foreach ($downloadable_files as $file_data) {
                    $downloadable_files_prepared[] = $file_data;
                }
            }

            $downloadable_files_prepared[] = array(
                'name' => empty($file_name) ? $name : $file_name,
                'file' => $url
            );

            self::updateDownloadableFiles($downloadable_files_prepared, $product_id);

            return array('success' => 'true');
        }

        return array('error' => 'save_image');
    }


    private static function updateDownloadableFiles($downloadable_files, $product_id)
    {
        $downloads = array();

        for ($i = 0, $count = count($downloadable_files); $i < $count; $i++) {
            if (empty($downloadable_files[$i]['file'])) {
                continue;
            }

            $download_object = new WC_Product_Download();

            $downloadable_files[$i]['previous_hash'] = isset($downloadable_files[$i]['previous_hash'])
                ? $downloadable_files[$i]['previous_hash']
                : '';

            $file_hash = md5($downloadable_files[$i]['file']);

            $download_object->set_id($file_hash);
            $download_object->set_name($downloadable_files[$i]['name']);
            $download_object->set_file($downloadable_files[$i]['file']);
            $download_object->set_previous_hash($downloadable_files[$i]['previous_hash']);

            $downloads[$download_object->get_id()] = $download_object;
        }

        $meta_values = array();
        foreach ($downloads as $key => $download) {
            // Store in format WC uses in meta.
            $meta_values[$key] = $download->get_data();
        }

        update_post_meta($product_id, '_downloadable_files', $meta_values);
    }

    private static function storeImage($product_id, $image_file, $is_main_image)
    {
        if ($image_file['image']['error'] != UPLOAD_ERR_OK) {
            return array('error' => 'upload_error');
        }

        $time = current_time('mysql');
        if ($post = get_post($product_id)) {
            if (substr($post->post_date, 0, 4) > 0) {
                $time = $post->post_date;
            }
        }

        if (!($uploads = wp_upload_dir($time))) {
            return array('error' => 'upload_error');
        }

        $filename = wp_unique_filename($uploads['path'], $image_file['image']['name']);

        // Move the file to the uploads dir
        $new_file = $uploads['path'] . "/$filename";
        if (!@move_uploaded_file($image_file['image']['tmp_name'], $new_file)) {
            return array('error' => 'upload_error');
        }

        // Set correct file permissions
        $stat = stat(dirname($new_file));
        $perms = $stat['mode'] & 0000666;
        @chmod($new_file, $perms);

        // Compute the URL
        $url = $uploads['url'] . "/$filename";

        if (is_multisite()) {
            delete_transient('dirsize_cache');
        }

        $name = $image_file['image']['name'];
        $name_parts = pathinfo($name);
        $name = trim(substr($name, 0, -(1 + strlen($name_parts['extension']))));

        $wp_filetype = wp_check_filetype_and_ext($image_file['image']['tmp_name'], $image_file['image']['name']);
        $type = empty($wp_filetype['type']) ? '' : $wp_filetype['type'];

        $title = sanitize_text_field($name);

        // Construct the attachment array
        $attachment = array(
            'post_mime_type' => $type,
            'guid' => $url,
            'post_parent' => $product_id,
            'post_title' => $title,
            'post_content' => '',
            'post_excerpt' => '',
        );

        // This should never be set as it would then overwrite an existing attachment.
        unset($attachment['ID']);

        // Save the data
        $id = wp_insert_attachment($attachment, $new_file, $product_id);
        if (!is_wp_error($id) && wp_update_attachment_metadata($id,
                self::wp_generate_attachment_metadata($id, $new_file))) {
            if (!$is_main_image) {
                $product = new WC_Product($product_id);
                $image_ids = $product->get_gallery_image_ids();
                $image_ids[] = $id;
                update_post_meta($product_id, '_product_image_gallery', implode(',', $image_ids));
            } else {
                update_post_meta($product_id, '_thumbnail_id', $id);
            }

            return array('success' => 'true');
        }

        return array('error' => 'save_image');
    }

    private static function wp_generate_attachment_metadata($attachment_id, $file)
    {
        $attachment = get_post($attachment_id);

        $metadata = array();
        $support = false;
        if (preg_match('!^image/!', get_post_mime_type($attachment))/* && file_is_displayable_image($file)*/) {
            $imagesize = getimagesize($file);
            $metadata['width'] = $imagesize[0];
            $metadata['height'] = $imagesize[1];

            // Make the file path relative to the upload dir.
            $metadata['file'] = _wp_relative_upload_path($file);

            // Make thumbnails and other intermediate sizes.
            global $_wp_additional_image_sizes;

            $sizes = array();
            foreach (get_intermediate_image_sizes() as $s) {
                $sizes[$s] = array('width' => '', 'height' => '', 'crop' => false);
                if (isset($_wp_additional_image_sizes[$s]['width'])) {
                    $sizes[$s]['width'] = (int)$_wp_additional_image_sizes[$s]['width']; // For theme-added sizes
                } else {
                    $sizes[$s]['width'] = get_option("{$s}_size_w"); // For default sizes set in options
                }

                if (isset($_wp_additional_image_sizes[$s]['height'])) {
                    $sizes[$s]['height'] = (int)$_wp_additional_image_sizes[$s]['height']; // For theme-added sizes
                } else {
                    $sizes[$s]['height'] = get_option("{$s}_size_h"); // For default sizes set in options
                }

                if (isset($_wp_additional_image_sizes[$s]['crop'])) {
                    $sizes[$s]['crop'] = $_wp_additional_image_sizes[$s]['crop']; // For theme-added sizes
                } else {
                    $sizes[$s]['crop'] = get_option("{$s}_crop"); // For default sizes set in options
                }
            }

            /**
             * Filters the image sizes automatically generated when uploading an image.
             *
             * @since 2.9.0
             * @since 4.4.0 Added the `$metadata` argument.
             *
             * @param array $sizes An associative array of image sizes.
             * @param array $metadata An associative array of image metadata: width, height, file.
             */
            $sizes = apply_filters('intermediate_image_sizes_advanced', $sizes, $metadata);

            if ($sizes) {
                $editor = wp_get_image_editor($file);

                if (!is_wp_error($editor)) {
                    $metadata['sizes'] = $editor->multi_resize($sizes);
                }
            } else {
                $metadata['sizes'] = array();
            }
        } elseif (wp_attachment_is('video', $attachment)) {
            $metadata = wp_read_video_metadata($file);
            $support = current_theme_supports('post-thumbnails',
                    'attachment:video') || post_type_supports('attachment:video', 'thumbnail');
        } elseif (wp_attachment_is('audio', $attachment)) {
            $metadata = wp_read_audio_metadata($file);
            $support = current_theme_supports('post-thumbnails',
                    'attachment:audio') || post_type_supports('attachment:audio', 'thumbnail');
        }

        if ($support && !empty($metadata['image']['data'])) {
            // Check for existing cover.
            $hash = md5($metadata['image']['data']);
            $posts = get_posts(array(
                'fields' => 'ids',
                'post_type' => 'attachment',
                'post_mime_type' => $metadata['image']['mime'],
                'post_status' => 'inherit',
                'posts_per_page' => 1,
                'meta_key' => '_cover_hash',
                'meta_value' => $hash
            ));
            $exists = reset($posts);

            if (!empty($exists)) {
                update_post_meta($attachment_id, '_thumbnail_id', $exists);
            } else {
                $ext = '.jpg';
                switch ($metadata['image']['mime']) {
                    case 'image/gif':
                        $ext = '.gif';
                        break;
                    case 'image/png':
                        $ext = '.png';
                        break;
                }
                $basename = str_replace('.', '-', basename($file)) . '-image' . $ext;
                $uploaded = wp_upload_bits($basename, '', $metadata['image']['data']);
                if (false === $uploaded['error']) {
                    $image_attachment = array(
                        'post_mime_type' => $metadata['image']['mime'],
                        'post_type' => 'attachment',
                        'post_content' => '',
                    );
                    /**
                     * Filters the parameters for the attachment thumbnail creation.
                     *
                     * @since 3.9.0
                     *
                     * @param array $image_attachment An array of parameters to create the thumbnail.
                     * @param array $metadata Current attachment metadata.
                     * @param array $uploaded An array containing the thumbnail path and url.
                     */
                    $image_attachment = apply_filters('attachment_thumbnail_args', $image_attachment, $metadata,
                        $uploaded);

                    $sub_attachment_id = wp_insert_attachment($image_attachment, $uploaded['file']);
                    add_post_meta($sub_attachment_id, '_cover_hash', $hash);
                    $attach_data = wp_generate_attachment_metadata($sub_attachment_id, $uploaded['file']);
                    wp_update_attachment_metadata($sub_attachment_id, $attach_data);
                    update_post_meta($attachment_id, '_thumbnail_id', $sub_attachment_id);
                }
            }
        }

        // Remove the blob of binary data from the array.
        if ($metadata) {
            unset($metadata['image']['data']);
        }

        /**
         * Filters the generated attachment meta data.
         *
         * @since 2.1.0
         *
         * @param array $metadata An array of attachment meta data.
         * @param int $attachment_id Current attachment ID.
         */
        return apply_filters('wp_generate_attachment_metadata', $metadata, $attachment_id);
    }

    private static function _getGalleryImageIds($product_id)
    {
        $product = new WC_Product($product_id);

        return $product->get_gallery_image_ids();
    }

    public function get_data_for_new_product()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            return array('error' => 'woocommerce_version_less_3');
        }

        return array(
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'product_types' => wc_get_product_types(),
            'product_stock_statuses' => wc_get_product_stock_status_options(),
            'backorder_options' => wc_get_product_backorder_options(),
            'product_statuses' => self::getProductStatuses(),
            'max_file_upload_in_bytes' => self::getMaxFileUploadInBytes(),
        );
    }

    public function add_product()
    {
        if (empty($this->product)) {
            return false;
        }

        $product_data = json_decode(stripslashes(urldecode($this->product)), true);

        if (empty($product_data)) {
            return false;
        }

        $post_id = wp_insert_post(
            array('post_title' => __('Auto Draft'), 'post_type' => 'product', 'post_status' => 'auto-draft')
        );

        if ($post_id > 0) {
            wc_get_product($post_id);

            $product_data['product_id'] = $post_id;

            if (self::saveProductData($product_data, true)) {
                return array('success' => 'true', 'product_id' => $post_id);
            }
        }

        return array('error' => 'something_wrong');
    }

    public function set_order_action()
    {
        if ($this->order_id <= 0) {
            $error = 'Order ID cannot be empty!';
            mobassist_log_me('ORDER ACTION ERROR: ' . $error);
            return array('error' => $error);
        }

        if (empty($this->action)) {
            $error = 'Action is not set!';
            mobassist_log_me('ORDER ACTION ERROR: ' . $error);
            return array('error' => $error);
        }

        $order = new WC_Order($this->order_id);

        if (!$order) {
            $error = 'Order not found!';
            mobassist_log_me('ORDER ACTION ERROR: ' . $error);
            return array('error' => $error);
        }

        if ($this->action == 'change_status') {
            if (!isset($this->new_status) || (int)$this->new_status < 0) {
                $error = 'New order status is not set!';
                mobassist_log_me('ORDER ACTION ERROR: ' . $error);
                return array('error' => $error);
            }

            $order->update_status($this->new_status, $this->change_order_status_comment);

            return array('success' => 'true');
        }

        $error = 'Unknown error!';
        mobassist_log_me('ORDER ACTION ERROR: ' . $error);
        return array('error' => $error);
    }

    public function push_notification_settings()
    {
        $data = array();

        if (empty($this->registration_id)) {
            $error = 'Empty device ID';
            mobassist_log_me('PUSH SETTINGS ERROR: ' . $error);
            return array('error' => 'missing_parameters');
        }

        if (empty($this->app_connection_id)) {
            $error = 'Wrong app connection ID: ' . $this->app_connection_id;
            mobassist_log_me('PUSH SETTINGS ERROR: ' . $error);
            return array('error' => 'missing_parameters');
        }

        if (empty($this->api_key)) {
            $error = 'Empty application API key';
            mobassist_log_me('PUSH SETTINGS ERROR: ' . $error);
            return array('error' => 'missing_parameters');
        }

        // update current API KEY
        $options = get_option('mobassistantconnector');
        if (!isset($options['mobassist_api_key']) || $options['mobassist_api_key'] != $this->api_key) {
            $options['mobassist_api_key'] = $this->api_key;
            update_option('mobassistantconnector', $options);
        }

        $data['registration_id'] = $this->registration_id;
        $data['app_connection_id'] = $this->app_connection_id;
        $data['push_new_order'] = $this->push_new_order;
        $data['push_order_statuses'] = $this->push_order_statuses;
        $data['push_new_customer'] = $this->push_new_customer;
        $data['push_currency_code'] = ((isset($this->push_currency_code) && !empty($this->push_currency_code) && ($this->push_currency_code !== 'not_set')) ? $this->push_currency_code : $this->currency);
        $data['device_unique'] = (string)$this->device_unique_id;
        $data['device_name'] = (string)$this->device_name;
        $data['date'] = date('Y-m-d H:i:s');
        $data['status'] = 1;
        $data['user_id'] = (int)Mobassistantconnector_Access::get_user_id_by_session_key($this->session_key);
        $data['user_actions'] = Mobassistantconnector_Access::get_allowed_actions_by_user_id($data['user_id']);

        if (!empty($this->registration_id_old)) {
            $data['registration_id_old'] = $this->registration_id_old;
        }

        if ($this->savePushNotificationSettings($data)) {
            return array('success' => 'true');
        }

        $error = 'could_not_update_data';
        mobassist_log_me('PUSH SETTINGS ERROR: ' . $error);
        return array('error' => $error);
    }

    public function savePushNotificationSettings($data = array())
    {
        global $wpdb;

        $query_values = array();
        $query_where = array();
        $result = false;

        if (isset($data['registration_id_old'])) {
            $sql = "UPDATE `{$wpdb->prefix}mobileassistant_push_settings` SET registration_id = '%s' 
                    WHERE registration_id = '%s'";
            $sql = sprintf($sql, $data['registration_id'], $data['registration_id_old']);
            $wpdb->query($sql);
        }

        // Delete empty record
        if (empty($data['push_new_order']) && empty($data['push_order_statuses']) && empty($data['push_new_customer'])) {
            $sql_del = "DELETE FROM `{$wpdb->prefix}mobileassistant_push_settings` WHERE registration_id = '%s' 
                        AND app_connection_id = '%s'";
            $sql_del = sprintf($sql_del, $data['registration_id'], $data['app_connection_id']);

            $wpdb->query($sql_del);
            return true;
        }

        // Check if device could have higher permissions
        if (in_array('push_notification_settings_new_order', $data['user_actions'])) {
            $data['push_new_order'] = (int)$this->push_new_order;
        } else {
            $data['push_new_order'] = 0;
        }

        if (in_array('push_notification_settings_new_customer', $data['user_actions'])) {
            $data['push_new_customer'] = (int)$this->push_new_customer;
        } else {
            $data['push_new_customer'] = 0;
        }

        if (in_array('push_notification_settings_order_statuses', $data['user_actions'])) {
            $data['push_order_statuses'] = (string)$this->push_order_statuses;
        } else {
            $data['push_order_statuses'] = 0;
        }

        $query_values[] = sprintf(" push_new_order = '%d'", $data['push_new_order']);
        $query_values[] = sprintf(" push_order_statuses = '%s'", $data['push_order_statuses']);
        $query_values[] = sprintf(" push_new_customer = '%d'", $data['push_new_customer']);
        $query_values[] = sprintf(" push_currency_code = '%s'", $data['push_currency_code']);
        $query_values[] = sprintf(" `device_unique_id` = %d", $data['device_unique_id']);

        // Get devices with same reg_id and con_id
        $sql = "SELECT setting_id FROM `{$wpdb->prefix}mobileassistant_push_settings`
                WHERE registration_id = '%s' AND app_connection_id = '%s'";

        $sql = sprintf($sql, $data['registration_id'], $data['app_connection_id']);

        $results = $wpdb->get_results($sql, ARRAY_A);

        if (!$results || count($results) > 1 || count($results) <= 0) {
            if (count($results) > 1) {
                foreach ($results as $row) {
                    $sql_del = "DELETE FROM `{$wpdb->prefix}mobileassistant_push_settings` WHERE setting_id = '%d'";
                    $sql_del = sprintf($sql_del, $row['setting_id']);
                    $wpdb->query($sql_del);
                }
            }

            $query_values[] = sprintf(" registration_id = '%s'", $data['registration_id']);
            $query_values[] = sprintf(" app_connection_id = '%s'", $data['app_connection_id']);

            $query_values[] = sprintf(" `status` = %d", $data['status']);
            $query_values[] = sprintf(" `user_id` = %d", $data['user_id']);

            $sql = "INSERT INTO `{$wpdb->prefix}mobileassistant_push_settings` SET ";

            if (!empty($query_values)) {
                $sql .= implode(' , ', $query_values);
            }

            $result = $wpdb->query($sql);
        } else {
            $query_where[] = sprintf(" registration_id = '%s'", $data['registration_id']);
            $query_where[] = sprintf(" app_connection_id = '%s'", $data['app_connection_id']);

            $sql = "UPDATE `{$wpdb->prefix}mobileassistant_push_settings` SET ";

            if (!empty($query_values)) {
                $sql .= implode(' , ', $query_values);
            }

            if (!empty($query_where)) {
                $sql .= ' WHERE ' . implode(' AND ', $query_where);
            }

            $result = $wpdb->query($sql);
        }

        if ($result || empty($wpdb->last_error)) {
            $result = true;
        }

        return $result;
    }

    public function delete_push_config()
    {
        global $wpdb;

        if ($this->app_connection_id && $this->registration_id) {
            $result = $wpdb->delete("{$wpdb->prefix}mobileassistant_push_settings",
                array('registration_id' => $this->registration_id, 'app_connection_id' => $this->app_connection_id),
                array('%s', '%s'));

            if ($result) {
                $ret = array('success' => 'true');
            } else {
                $ret = array('error' => 'delete_data');
            }
        } else {
            $ret = array('error' => 'missing_parameters');
        }

        return $ret;
    }

    protected function split_values($arr, $keys, $sign = ', ')
    {
        $new_arr = array();
        foreach ($keys as $key) {
            if (isset($arr[$key])) {
                if (!is_null($arr[$key]) && $arr[$key] != '') {
                    $new_arr[] = $arr[$key];
                }
            }
        }
        return implode($sign, $new_arr);
    }

    private function _test_default_password_is_changed()
    {
        $options = get_option('mobassistantconnector');

        return !($options['login'] == '1' && md5($options['pass']) == 'c4ca4238a0b923820dcc509a6f75849b');
    }

    private function _get_order_products()
    {
        global $wpdb;

        $query = "SELECT
                    meta_product_id.meta_value AS product_id,
                    posts.post_title AS product_name,
                    items_qty.meta_value AS product_quantity,
                    meta_price.meta_value AS product_price,
                    items_variation_id.meta_value AS variation_id,
                    meta_sku.meta_value AS sku
                  FROM `{$wpdb->prefix}woocommerce_order_items` AS order_items
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_product_id ON meta_product_id.order_item_id = order_items.order_item_id AND meta_product_id.meta_key = '_product_id'
                    LEFT JOIN `{$wpdb->posts}` AS posts ON posts.ID = meta_product_id.meta_value
                    LEFT JOIN `$wpdb->postmeta` AS meta_price ON posts.ID = meta_price.post_id AND meta_price.meta_key = '_price'
                    LEFT JOIN `$wpdb->postmeta` AS meta_sku ON posts.ID = meta_sku.post_id AND meta_sku.meta_key = '_sku'
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS items_qty ON items_qty.order_item_id = order_items.order_item_id AND items_qty.meta_key = '_qty'
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS items_variation_id ON items_variation_id.order_item_id = order_items.order_item_id AND items_variation_id.meta_key = '_variation_id'
                    LEFT JOIN `{$wpdb->posts}` AS posts_orders ON posts_orders.ID = order_items.order_id
                WHERE order_items.order_item_type = 'line_item'
                AND posts.post_type = 'product'
                AND order_items.order_id = '%d'";

        if (!empty($this->status_list_hide)) {
            $query .= " AND posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        $query = sprintf($query, $this->order_id);

        $results = $wpdb->get_results($query, ARRAY_A);

        return $results;
    }

    private static function  _sanitize_files_array()
    {
        $files = array(
            'image' => array(
                'name' => '',
                'type' => '',
                'tmp_name' => '',
                'error' => '',
                'size' => 0
            )
        );

        foreach ($_FILES['image'] as $param => $value) {
            switch ($param) {
                case 'name':
                    $files['image']['name'] = sanitize_file_name($value);
                    break;
                case 'type':
                    $files['image']['type'] = sanitize_textarea_field($value);
                    break;
                case 'tmp_name':
                    $files['image']['tmp_name'] = sanitize_textarea_field($value);
                    break;
                case 'error':
                    $files['image']['error'] = sanitize_title_with_dashes($value);
                    break;
                case 'size':
                    $files['image']['size'] = sanitize_file_name($value);
                    break;
                default:
                    break;
            }
        }

        return $files;
    }
}
