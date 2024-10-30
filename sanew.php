<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!defined('DOING_AJAX') && isset($_REQUEST['connector']) && isset($_REQUEST['page']) && $_REQUEST['page'] == 'mobileassistant') {
    define('DOING_AJAX', true);
}

class MobileAssistantConnectorNew extends MobileAssistantConnectorMain
{
    protected $sDBHost = '';
    protected $sDBUser = '';
    protected $sDBPwd = '';
    protected $sDBName = '';
    protected $sDBPrefix = '';
    protected $site_url = '';
    protected $CartType = -1;
    protected $status_list_hide = array('auto-draft', 'trash');
    private $shop_id;
    private $token;
    private $device_name;
    private $show;
    private $page;
    private $page_index;
    private $page_size;
    private $search_order_id;
    private $search_phrase;
    private $only_with_orders;
    private $orders_from;
    private $orders_to;
    private $customers_from;
    private $customers_to;
    private $date_from;
    private $date_to;
    private $graph_from;
    private $graph_to;
    private $stats_from;
    private $stats_to;
    private $products_to;
    private $products_from;
    private $order_id;
    private $id;
    private $email;
    private $user_id;
    private $params;
    private $val;
    private $search_val;
    private $statuses;
    private $sort_by;
    private $sort_field;
    private $sort_direction;
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
    private $not_notified_order_statuses_ids;
    private $push_new_customer;
    private $app_connection_id;
    private $action;
    private $custom_period;
    private $new_status;
    private $change_order_status_comment;
    private $account_email;
    private $check_permission;
    private $dateFrom;
    private $dateTo;
    private $dashboardGrouping;
    private $order_statuses;
    private $order_status_id;
    private $show_all_customers;
    private $carrier_id;
    private $data;
    private $changes;
    private $assigned_categories;
    private $file_id;
    private $attribute_id;
    private $is_new_item;
    private $post_password;

    const KEY_TOKEN                 = 'token';
    const RESPONSE_CODE_AUTH_ERROR  = 'auth_error';
    const KEY_MODULE_VERSION        = 'module_version';
    const KEY_CART_VERSION          = 'cart_version';
    const KEY_STORE_STATISTICS              = 'store_statistics';
    const KEY_ORDERS_TOTAL                  = 'orders_total';
    const KEY_ORDERS_COUNT                  = 'orders_count';
    const KEY_PRODUCTS_COUNT                = 'products_count';
    const KEY_CUSTOMERS_COUNT               = 'customers_count';
    const KEY_ORDERS                        = 'orders';
    const KEY_PRODUCTS                      = 'products';
    const KEY_CUSTOMERS                     = 'customers';
    const KEY_DATE_FROM                     = 'date_from';
    const KEY_DATE_TO                       = 'date_to';
    const GLOBAL_DATE_FORMAT                = 'Y-m-d H:i:s';
    const GROUP_BY_DAY                      = 'd';
    const GROUP_BY_MONTH                    = 'm';
    const GROUP_BY_HOUR                     = 'h';
    const GROUP_BY_WEEK                     = 'W';
    const ERROR_CODE_COULD_NOT_CREATE_DATETIME_OBJECT               = 'error_while_creating_date_time_object';
    const KEY_ORDERS_DATE                   = 'orders_date';
    const KEY_CUSTOMERS_DATE                = 'customers_date';
    const KEY_TIMESTAMP                     = 'timestamp';
    const KEY_ORDERS_TOTAL_PER_CUSTOMER     = 'orders_total_per_customer';
    const KEY_GRAPH_DATA                    = 'graph_data';
    const KEY_AVERAGE                       = 'average';
    const KEY_GROUP_BY                      = 'group_by';
    const KEY_CURRENCY_SYMBOL               = 'currency_symbol';
    const KEY_TOTAL                         = 'total';
    const KEY_ID                            = 'id';
    const KEY_TITLE                         = 'title';
    const KEY_FORMATTED_ORDERS_TOTAL        = 'formatted_orders_total';
    const KEY_ORDER_STATUSES_STATISTICS     = 'order_statuses_statistics';
    const ORDER_BY_ID               = 'ID';
    const ORDER_BY_DATE_CREATED     = 'DATE_CREATED';
    const ORDER_BY_CUSTOMER_NAME    = 'CUSTOMER_NAME';
    const ORDER_BY_TOTAL            = 'TOTAL';
    const ORDER_BY_PRODUCTS_COUNT   = 'PRODUCTS_COUNT';
    const ORDER_BY_ORDERS_TOTAL     = 'ORDERS_TOTAL';
    const ORDER_BY_NAME             = 'NAME';
    const ORDER_BY_ORDERS_COUNT     = 'ORDERS_COUNT';
    const ORDER_BY_PRODUCT_NAME     = 'NAME';
    const ORDER_BY_QUANTITY         = 'QUANTITY';
    const ORDER_BY_PRICE            = 'PRICE';
    const ORDER_BY_STATUS           = 'STATUS';

    const ERROR_CODE_ORDER_NOT_FOUND                                = 'order_not_found';

    const INTERVAL_ONE_DAY                  = 'P1D';
    const INTERVAL_ONE_WEEK                 = 'P1W';
    const INTERVAL_ONE_MONTH                = 'P1M';
    const INTERVAL_ONE_HOUR                 = 'PT1H';
    const INTERVAL                          = array(
        self::GROUP_BY_HOUR     => self::INTERVAL_ONE_HOUR,
        self::GROUP_BY_DAY      => self::INTERVAL_ONE_DAY,
        self::GROUP_BY_WEEK     => self::INTERVAL_ONE_WEEK,
        self::GROUP_BY_MONTH    => self::INTERVAL_ONE_MONTH,
    );

    const GROUP_BY                          = array(
        self::GROUP_BY_HOUR     => 'HOUR',
        self::GROUP_BY_DAY      => 'DAY',
        self::GROUP_BY_WEEK     => 'WEEK',
        self::GROUP_BY_MONTH    => 'MONTH',
    );


    public function __construct()
    {
        global $wpdb;

        $this->shop_id = $GLOBALS['blog_id'];

        if (!ini_get('date.timezone') || ini_get('date.timezone') == '') {
            @date_default_timezone_set(@date_default_timezone_get());
        }

        Mobassistantconnector_Access::clear_old_data();
        $this->check_is_woocommerce_activated();

        $wpdb->query('SET SQL_BIG_SELECTS=1;');

        $jsn = (fopen('php://input', 'rb') !== false
            ? file_get_contents('php://input')
            : '{}'
        );

        $this->request = json_decode($jsn, true);

        if(!$this->request) $this->request = array();
        $this->request = array_merge($this->request, $_REQUEST);

        if (!empty($_FILES) || !empty($this->request['data'])) {
            if(is_array($this->request['data'])) $this->request = array_merge($this->request, $this->request['data']);
            else $this->request = array_merge($this->request, json_decode(stripslashes_deep($this->request['data']), true));
        }

        if (isset($this->request['call_function'])) {
            $this->call_function = $this->validate_type($this->request['call_function'], 'STR');
        }

        if (isset($this->request['hash'])) {
            $this->hash = $this->validate_type($this->request['hash'], 'STR');
        }
        if (isset($this->request['key'])) {
            $this->session_key = $this->validate_type($this->request['key'], 'STR');
        }
        if (isset($this->request['token'])) {
            $this->token = $this->validate_type($this->request['token'], 'STR');
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

        if ($this->call_function == 'get_qr_code' && $this->hash) {
            $this->generate_output_error($this->get_qr_code());
        }

        if ($this->call_function == 'get_version') {
            $this->get_version();
        }

        if ($this->hash) {
            $key = Mobassistantconnector_Access::get_session_key($this->hash);

            if (!$key) {
                $this->generate_output_error(self::RESPONSE_CODE_AUTH_ERROR);
            }
        } elseif ($this->token || $this->token === '') {
            if (!Mobassistantconnector_Access::check_session_key($this->token)) {
                $this->generate_output_error('bad_token');
            }
        } else {
            Mobassistantconnector_Access::add_failed_attempt();
            $this->generate_output_error(self::RESPONSE_CODE_AUTH_ERROR);
        }

        $params = $this->validate_types(
            $this->request, array(
                'show' => 'INT',
                'page' => 'INT',
                'search_order_id' => 'STR',
                'search_phrase' => 'STR',
                'orders_from' => 'STR',
                'orders_to' => 'STR',
                'customers_from' => 'STR',
                'customers_to' => 'STR',
                'date_from' => 'INT',
                'date_to' => 'INT',
                'graph_from' => 'STR',
                'graph_to' => 'STR',
                'stats_from' => 'STR',
                'stats_to' => 'STR',
                'products_to' => 'STR',
                'products_from' => 'STR',
                'order_id' => 'INT',
                'id' => 'INT',
                'user_id' => 'INT',
                'params' => 'STR',
                'val' => 'STR',
                'search_val' => 'STR',
                'statuses' => 'STR',
                'sort_by' => 'STR',
                'sort_field' => 'STR',
                'sort_direction' => 'STR',
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
                'push_new_order' => 'BOOL',
                'push_order_statuses' => 'BOOL',
                'not_notified_order_statuses_ids' => 'STR_ARR',
                'push_new_customer' => 'BOOL',
                'app_connection_id' => 'STR',
                'action' => 'STR',
                'carrier_code' => 'STR',
                'custom_period' => 'INT',
                'page_index' => 'INT',
                'page_size' => 'INT',
                'store_id' => 'STR',
                'new_status' => 'STR',
                'notify_customer' => 'INT',
                'currency_code' => 'STR',
                'change_order_status_comment' => 'STR',
                'account_email' => 'STR',
                'check_permission' => 'STR',
                'order_status_id' => 'STR',
                'carrier_id' => 'STR',
                'email' => 'STR',
                'attribute_id' => 'STR',
                'only_with_orders' => 'BOOL',
                'is_new_item' => 'BOOL',
                'order_statuses' => 'STR_ARR',
                'post_password' => 'STR',
            )
        );

        foreach ($params as $k => $value) {
            $this->{$k} = $value;
        }
        $this->currency = get_woocommerce_currency();
//        $this->show_all_customers = true;

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

// CALL FUNCTIONS ---------------------------------------------------------------------------------------

    public function get_settings()
    {
        $res = array_merge(
            $this->_get_token(),
            $this->get_store_title(),
            array('shop_groups' => array()),
            array('languages' => array()),
            $this->get_version(),
            $this->get_orders_statuses(),
            array('currencies' => array()),
            array('carriers' => array())
        );

        return $res;
    }

    public function get_token()
    {
        return $this->_get_token();
    }

    public function get_orders_statuses()
    {
        $orders_statuses = array();

        $statuses = mobassist_get_order_statuses();

        foreach ($statuses as $code => $name) {
            $orders_statuses[] = array(
                'id' => $code,
                'name' => $name);
        }

        return array('order_statuses' => $orders_statuses);
    }

    public function push_notification_settings()
    {
        $data = array();

        if (empty($this->registration_id)) {
            $error = 'Empty device ID';
            mobassist_log_me('PUSH SETTINGS ERROR: ' . $error);
            $this->generate_output_error('missing_parameters');
        }

        if (empty($this->app_connection_id)) {
            $error = 'Wrong app connection ID: ' . $this->app_connection_id;
            mobassist_log_me('PUSH SETTINGS ERROR: ' . $error);
            $this->generate_output_error('missing_parameters');
        }

        // update current API KEY
        $options = get_option('mobassistantconnector');
        if (!isset($options['mobassist_api_key']) || $options['mobassist_api_key'] != $this->api_key) {
            $options['mobassist_api_key'] = $this->api_key;
            update_option('mobassistantconnector', $options);
        }

//        $data['account_id'] = $this->getAccountIdByEmail((string)$this->account_email);
        $data['registration_id'] = $this->registration_id;
        $data['app_connection_id'] = $this->app_connection_id;
        $data['push_new_order'] = $this->push_new_order;
        $data['push_order_statuses'] = $this->push_order_statuses;
        $data['not_notified_order_statuses_ids'] = $this->not_notified_order_statuses_ids;
        $data['push_new_customer'] = $this->push_new_customer;
        $data['push_currency_code'] = ((isset($this->push_currency_code) && !empty($this->push_currency_code) && ($this->push_currency_code !== 'not_set')) ? $this->push_currency_code : $this->currency);
        $data['device_unique'] = (string)$this->device_unique_id;
        $data['device_name'] = (string)$this->device_name;
        $data['date'] = date('Y-m-d H:i:s');
        $data['status'] = 1;
        $data['user_id'] = (int)Mobassistantconnector_Access::get_user_id_by_session_key($this->token);
        $data['user_actions'] = Mobassistantconnector_Access::get_allowed_actions_by_user_id($data['user_id']);

        if (!empty($this->registration_id_old)) {
            $data['registration_id_old'] = $this->registration_id_old;
        }

        if ($this->savePushNotificationSettings($data)) {
            return array();
        }

        $error = 'could_not_update_data';
        mobassist_log_me('PUSH SETTINGS ERROR: ' . $error);

        $this->generate_output_error($error);
    }

    public function delete_push_config()
    {
        global $wpdb;

        if ($this->app_connection_id && $this->registration_id) {
            $result = $wpdb->delete(
                "{$wpdb->prefix}mobileassistant_push_settings",
                array('registration_id' => $this->registration_id, 'app_connection_id' => $this->app_connection_id),
                array('%s', '%s')
            );

            if ($result) {
                $this->generate_output(array());
            } else {
                $this->generate_output_error('delete_data');
            }
        } else {
            $this->generate_output_error('missing_parameters');
        }
    }

    public function get_dashboard()
    {
        $storeStatisticData = array();

        $this->init_dates($this->date_from, $this->date_to);

        $this->dashboardGrouping    = $this->_getDashboardGrouping($this->dateFrom, $this->dateTo);

        $statisticData              = $this->getStoreStatisticsData();
        $dataGraphsData             = $this->getGraphsData();
        $orderStatusStatisticData   = $this->getStatusStatistic();

        $storeStatisticData[self::KEY_STORE_STATISTICS] = array(
            self::KEY_ORDERS_TOTAL      => (double)$statisticData[self::KEY_ORDERS]['orders_total'],
            self::KEY_ORDERS_COUNT      => (int)$statisticData[self::KEY_ORDERS]['orders_count'],
            self::KEY_PRODUCTS_COUNT    => (int)$statisticData[self::KEY_PRODUCTS]['products_count'],
            self::KEY_CUSTOMERS_COUNT   => (int)$statisticData[self::KEY_CUSTOMERS]['customers_count'],
        );

        return array_merge($storeStatisticData, $dataGraphsData, $orderStatusStatisticData);
    }

    public function get_widget_data()
    {
        // todo $this->shopId
        $this->init_dates($this->date_from, $this->date_to);

        $this->orderStatuses = implode(',', $this->order_statuses);

        $ordersData = $this->get_orders_store_statistic();
        $customersData = $this->get_customers_store_statistic();

        return
            array(
                self::KEY_CUSTOMERS_COUNT           => (int)$customersData['customers_count'],
                self::KEY_ORDERS_COUNT              => (int)$ordersData['orders_count'],
                self::KEY_FORMATTED_ORDERS_TOTAL    => mobassist_nice_price((float)$ordersData['orders_total'], false, false, false, true)
            );
    }

    public function get_orders()
    {
        global $wpdb;

        if (!empty($this->date_from) && !empty($this->date_to) && $this->date_from !== -1 && $this->date_to !== -1) {
            $this->dateFrom = self::convertMillisecondsTimestampToTimestamp($this->date_from);
            $this->dateTo   = self::convertMillisecondsTimestampToTimestamp($this->date_to);
        }

        $sql_total_products = "SELECT COUNT(order_items.order_item_id)
            FROM `{$wpdb->prefix}woocommerce_order_items` AS order_items
            WHERE order_items.order_item_type = 'line_item' AND order_items.order_id = posts.ID";

        if (function_exists('wc_get_order_status_name')) {
            $status_code_field = 'posts.post_status';
        } else {
            $status_code_field = 'status_terms.slug';
        }

        $fields = "SELECT
                    posts.ID AS id_order,
                    posts.post_date_gmt AS date_add,
                    meta_order_total.meta_value AS total_paid,
                    meta_order_currency.meta_value AS currency_code,
                    $status_code_field AS status_code,
                    first_name.meta_value AS first_name,
                    last_name.meta_value AS last_name,
                    CONCAT(first_name.meta_value, ' ', last_name.meta_value) AS customer,
                    users.display_name,
                    customer_id.meta_value AS customer_id,
                    billing_first_name.meta_value AS billing_first_name,
                    billing_last_name.meta_value AS billing_last_name,
                    customer_email.meta_value AS customer_email,
                    ( $sql_total_products ) AS count_prods";

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

        if ($this->dateFrom) {
            $query_where_parts[] = sprintf(
                " UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date_gmt, '+00:00', @@global.time_zone)) >= '%d'",
                $this->dateFrom
            );
        }

        if ($this->dateTo) {
            $query_where_parts[] = sprintf(
                " UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date_gmt, '+00:00', @@global.time_zone)) <= '%d'",
                $this->dateTo
            );
        }

        if (!empty($this->search_phrase) && preg_match('/^\d+(?:,\d+)*$/', $this->search_phrase)) {
            $query_where_parts[] = sprintf('posts.ID IN (%s)', $this->search_phrase);
        } elseif (!empty($this->search_phrase)) {
            $query_where_parts[] = sprintf(
                " (CONCAT(first_name.meta_value, ' ', last_name.meta_value) 
                LIKE '%%%s%%' OR users.display_name LIKE '%%%s%%' OR customer_email.meta_value LIKE '%%%s%%') ",
                $this->search_phrase,
                $this->search_phrase,
                $this->search_phrase
            );
            if (isset($this->show_all_customers) && $this->show_all_customers) {
                $query_where_not_registered = sprintf(
                    " OR (CONCAT(billing_first_name.meta_value, ' ', billing_last_name.meta_value) LIKE '%%%s%%' ) ",
                    $this->search_phrase
                );
            }
        }

        if (!empty($this->order_statuses)) {
            if (function_exists('wc_get_order_status_name')) {
                $query_where_parts[] = sprintf(" posts.post_status IN ('%s')", implode("', '", $this->order_statuses));
            } else {
                $query_where_parts[] = sprintf(" status_terms.slug IN ('%s')", implode("', '", $this->order_statuses));
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

        if (empty($this->sort_field)) $this->sort_field = self::ORDER_BY_ID;

        $query .= ' ORDER BY ';
        switch ($this->sort_field) {
            case self::ORDER_BY_ID:
                $query .= 'posts.ID ' . $this->getSortDirection($this->sort_direction);
                break;
            case self::ORDER_BY_DATE_CREATED:
                $query .= 'posts.post_date_gmt ' . $this->getSortDirection($this->sort_direction);
                break;
            case self::ORDER_BY_CUSTOMER_NAME:
                $query .= "CONCAT(billing_first_name, ' ', billing_last_name) " . $this->getSortDirection($this->sort_direction, 'ASC');
                break;
            case self::ORDER_BY_TOTAL:
                $query .= 'CAST(total_paid AS unsigned) ' . $this->getSortDirection($this->sort_direction);
                break;
            case self::ORDER_BY_PRODUCTS_COUNT:
                $query .= 'CAST(count_prods AS unsigned)' . $this->getSortDirection($this->sort_direction);
                break;
        }

        $query .= sprintf(' LIMIT %d, %d', (($this->page_index - 1) * $this->page_size), $this->page_size);

        $totals = $wpdb->get_row($query_totals, ARRAY_A);

        $orders = array();
        $results = $wpdb->get_results($query, ARRAY_A);
        foreach ($results as $order) {
            $order['ord_status'] = mobassist_get_order_status_name($order['id_order'], $order['status_code']);

            $user = null;
            if (array_key_exists('customer_id', $order)) {
                $user = new WP_User($order['customer_id']);
            }

            $customer_first_name = trim($order['billing_first_name']);
            $customer_last_name = trim($order['billing_last_name']);
            if (!empty($user) && empty($customer_first_name) && empty($customer_last_name)) {
                $customer_first_name = trim($user->first_name);
                $customer_last_name = trim($user->last_name);
            }

            if (empty($customer_first_name) && empty($customer_last_name)) {
                $customer_first_name = __('Guest', 'mobile-assistant-connector');
            }

            $orderInfo = array(
                "order_id" => (int)$order['id_order'],
                "customer_id" => (int)$order['customer_id'],
                "customer_email" => $order['customer_email'],
                "customer_first_name" => $customer_first_name,
                "customer_last_name" => $customer_last_name,
                "status_id" => $order['status_code'],
                "total" => (float)$order['total_paid'],
                "formatted_total" => mobassist_nice_price($order['total_paid'], $order['currency_code'], false, false, true),
                "date_add" => self::convertTimestampToMillisecondsTimestamp((int)strtotime($order['date_add'])),
                "products_count" => (int)$order['count_prods']
            );

            $orders[] = $orderInfo;
        }

        return array(
            'orders' => $orders,
            'orders_count' => $totals['total_orders'],
            'formatted_orders_total' => mobassist_nice_price($totals['total_sales'], false, false, false, true)
        );
    }

    public function search_orders()
    {
        return $this->get_orders();
    }

    public function get_order_details()
    {
        global $woocommerce, $wpdb;

        $order_info = array();
        $order = $this->_get_order();
        $user = new WP_User($order->get_user_id());

        $currency_code = $this->_get_order_currency($order);

        if (empty($this->only_items)) {
            $first_name = trim($user->first_name);
            $last_name = trim($user->last_name);

            if (empty($first_name) && empty($last_name)) {
                $first_name = $this->is_v3() ? $order->get_billing_first_name() : $order->billing_first_name;
                $last_name = $this->is_v3() ? $order->get_billing_last_name() : $order->billing_last_name;
            }

            if (empty($first_name) && empty($last_name)) {
                $first_name = __('Guest', 'mobile-assistant-connector');
            }

            $order_total = $order->get_total();
            $countries = $woocommerce->countries->countries;

//            if (function_exists('wc_get_order_status_name')) {
//                $order_status_code = $this->is_v3() ? $order->get_status() : $order->post_status;
//            } else {
//                $order_status_code = $order->status;
//            }

            $order_query = $wpdb->prepare(
                "SELECT posts.post_date_gmt AS date_add, posts.post_status AS order_status_id 
                            FROM `{$wpdb->posts}` AS posts WHERE posts.ID = '%s'",
                $this->id
            );
            $order_additional_info = $wpdb->get_row($order_query, ARRAY_A);

            $order_status_code = $order_additional_info['order_status_id'];

            $order_id = $this->id;

            if ($this->is_v3()) {
                $tax_amount = (float)$order->get_cart_tax() + (float)$order->get_shipping_tax();
            } else {
                $tax_amount = (float)$order->order_tax + (float)$order->order_shipping_tax;
            }

            if (isset($user->data->user_email)) {
                $customer_email = $user->data->user_email;
            } elseif ($this->is_v3()) {
                $customer_email = $order->get_billing_email();
            } else {
                $customer_email = $order->billing_email;
            }

            $order_info = array(
                'order_id' => $order_id,
                'customer_id' => $this->is_v3() ? (int)$order->get_user_id() : (int)$order->user_id,
                'customer_email' =>  $customer_email,
                "customer_first_name" => $first_name,
                "customer_last_name" => $last_name,
                'status_id' => $order_status_code,
                'total' => $order_total,
                'formatted_total' => mobassist_nice_price($order_total, $currency_code, false, false, true),
                'formatted_total_discounts' => mobassist_nice_price($order->get_total_discount(), $currency_code, false, false, true),
                'formatted_total_shipping' => mobassist_nice_price($this->is_v3() ? $order->get_shipping_total() : $order->shipping_total, $currency_code, false, false, true),
                'tax_amount' => mobassist_nice_price($tax_amount, $currency_code),
                'date_add' => self::convertTimestampToMillisecondsTimestamp((int)strtotime($order_additional_info['date_add'])),
                'customer_note' => $this->is_v3() ? $order->get_customer_note() : $order->customer_note,
                'order_notes' => $this->_get_order_notes($order_id),
            );

            $ship_methods = array();
            $shipping_methods = $order->get_shipping_methods();
            if ($this->is_v3()) {
            foreach ($shipping_methods as $shipping_method) {
                $total = $shipping_method->get_total();
                $ship_methods[] = array(
                    "id" => $shipping_method->get_method_id(),
                    "title" => $shipping_method->get_method_title(),
                    "total" => $total,
                    "formatted_total" => mobassist_nice_price($total, $currency_code, false, false, true)
                );
            }
            } else {
                foreach ( $shipping_methods as $shipping_item_id => $shipping_item ) {
                    $total = wc_format_decimal($shipping_item['cost'], 2);
                    $ship_methods[] = array(
                        "id" => $shipping_item_id,
                        "title" => $shipping_item['name'],
                        "total" => $total,
                        "formatted_total" => mobassist_nice_price($total, $currency_code, false, false, true)
                    );
                }
            }

            $order_info['shipping_methods'] = $ship_methods;
            $order_info['shipping_methods_count'] = count($ship_methods);

            if ($this->is_v3()) {
                $date_paid = $order->get_date_paid();
                $payment_date = $date_paid ? self::convertTimestampToMillisecondsTimestamp((int)$date_paid->getTimestamp()) : 0;
            } else {
                $date_paid = strtotime($order->order_date);
                $payment_date = $date_paid ? self::convertTimestampToMillisecondsTimestamp($date_paid) : 0;
            }

            $payment_method = $this->is_v3() ? $order->get_payment_method_title() : $order->payment_method_title;

            $order_info['payment_method'] = $payment_method;
            $order_info['payment_date'] = $payment_date;

            $order_info['payment_code'] = $this->is_v3() ? $order->get_payment_method() : $order->payment_method;
            $order_info['payment_date'] = $payment_date;
            $order_info['payment_method'] = $payment_method;

            $shipping_country = $this->is_v3() ? $order->get_shipping_country() : $order->shipping_country;
            $billing_country = $this->is_v3() ? $order->get_billing_country() : $order->billing_country;

            $order_info['shipping_first_name'] = $this->is_v3() ? $order->get_shipping_first_name() : $order->shipping_first_name;
            $order_info['shipping_last_name'] = $this->is_v3() ? $order->get_shipping_last_name() : $order->shipping_last_name;
            $order_info['shipping_company'] = $this->is_v3() ? $order->get_shipping_company() : $order->shipping_company;
            $order_info['shipping_address1'] = $this->is_v3() ? $order->get_shipping_address_1() : $order->shipping_address_1;
            $order_info['shipping_address2'] = $this->is_v3() ? $order->get_shipping_address_2() : $order->shipping_address_2;
            $order_info['shipping_city'] = $this->is_v3() ? $order->get_shipping_city() : $order->shipping_city;
            $order_info['shipping_post_code'] = $this->is_v3() ? $order->get_shipping_postcode() : $order->shipping_postcode;
            $order_info['shipping_country'] = isset($countries[$shipping_country]) ? $countries[$shipping_country] : '';
            $order_info['shipping_state'] = $this->is_v3() ? $order->get_shipping_state() : $order->shipping_state;

            $order_info['billing_first_name'] = $this->is_v3() ? $order->get_billing_first_name() : $order->billing_first_name;
            $order_info['billing_last_name'] = $this->is_v3() ? $order->get_billing_last_name() : $order->billing_last_name;
            $order_info['billing_company'] = $this->is_v3() ? $order->get_billing_company() : $order->billing_company;
            $order_info['billing_address1'] = $this->is_v3() ? $order->get_billing_address_1() : $order->billing_address_1;
            $order_info['billing_address2'] = $this->is_v3() ? $order->get_billing_address_2() : $order->billing_address_2;
            $order_info['billing_city'] = $this->is_v3() ? $order->get_billing_city() : $order->billing_city;
            $order_info['billing_post_code'] = $this->is_v3() ? $order->get_billing_postcode() : $order->billing_postcode;
            $order_info['billing_country'] = isset($countries[$billing_country]) ? $countries[$billing_country] : '';
            $order_info['billing_state'] = $this->is_v3() ? $order->get_billing_state() : $order->billing_state;
            $order_info['billing_email'] = $this->is_v3() ? $order->get_billing_email() : $order->billing_email;
            $order_info['billing_phone'] = $this->is_v3() ? $order->get_billing_phone() : $order->billing_phone;

            if (method_exists($order, 'get_total_refunded')) {
                $order_info['total_refunded'] = $order->get_total_refunded() * -1;
                $order_info['formatted_total_refunded'] = mobassist_nice_price($order->get_total_refunded() * -1, $currency_code, false, false, true);
            }

            $sql = $wpdb->prepare(
                "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = '%s' AND meta_key NOT LIKE '\_%'",
                $order_id
            );
            $order_custom_fields = $wpdb->get_results($sql, ARRAY_N);

            $order_info['custom_fields'] = $order_custom_fields;
        }

        $order_items = $order->get_items();

        $order_products = $this->_get_order_products($order_id, $order_items, $currency_code);

        $order_info['products'] = $order_products;
        $order_info['products_count'] = count($order_items);

        return array('order' => $order_info);
    }

    public function get_order_products()
    {
        $order = $this->_get_order();

        $order_items = $order->get_items();
        $currency_code = $this->_get_order_currency($order);

        $order_products = $this->_get_order_products($this->is_v3() ? $order->get_id() : $order->ID, $order_items, $currency_code);

        return array('products' => $order_products);
    }

    public function change_order_status()
    {
        $order = $this->_get_order();

        if (!$this->order_status_id || empty($this->order_status_id)) {
            $this->generate_output_error("new_order_status_incorrect");
            return false;
        }

        try {
            $order->update_status($this->order_status_id, $this->change_order_status_comment);
        } catch (Exception $exception) {
            throw new EM1Exception(EM1Exception::ERROR_CODE_CHANGE_STATUS_ORDER_FAILED, $exception->getMessage());
        }

        return array();
    }

    public function check_download_order_invoice_availability()
    {
        $order = $this->_get_order();

        $is_pdf_plugin_active = is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php');

        if ($is_pdf_plugin_active) {
            return array();
        }

        $this->generate_output_error("no_pdf_invoices_plugin", "No PDF Invoices Packing Slips plugin installed!");
    }

    public function download_order_invoice()
    {
        global $wpo_wcpdf;

        $order = $this->_get_order();

        $is_pdf_plugin_active = is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php');

        if (!$is_pdf_plugin_active || !is_object($wpo_wcpdf)) {
            $this->generate_output_error("no_pdf_invoices_plugin", "No PDF Invoices Packing Slips plugin installed!");
        }

        //$pdf_data = $wpo_wcpdf->export->get_pdf('invoice', (array)$this->id); //outdated
        $invoice = wcpdf_get_document('invoice', (array)$this->id, true);
        $pdf_data = $invoice->get_pdf();
        if (!$pdf_data) {
            $this->generate_output_error("cant_generate_pdf_snvoice", "Can't generate PDF Invoice!");
        }

        header('Content-type: application/pdf');

        echo $pdf_data;
        exit;
    }

    public function update_order_shipping_details()
    {
        $this->generate_output_error("not_implemented");
    }

    public function get_customers()
    {
        global $wpdb;
        $query_where_parts = array();
        $query_where_not_registered = array();
        $query_not_registered = '';
        $query_page_not_registered = '';

        if (!empty($this->date_from) && !empty($this->date_to) && $this->date_from !== -1 && $this->date_to !== -1) {
            $this->dateFrom = self::convertMillisecondsTimestampToTimestamp($this->date_from);
            $this->dateTo   = self::convertMillisecondsTimestampToTimestamp($this->date_to);
        }

        $wpdb->query("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        // Get registered customers query
        $fields = "SELECT
            DISTINCT(c.ID) AS id_customer,
            um_first_name.meta_value AS first_name,
            um_last_name.meta_value AS last_name,
            CONCAT(um_first_name.meta_value, ' ', um_last_name.meta_value) AS full_name,
            c.user_registered AS date_add,
            c.user_email AS email,
            c.display_name,
            posts.posts_count AS orders_count,
            posts.orders_total AS orders_total";

        $total_fields = 'SELECT c.user_email';

        $sql = " FROM `{$wpdb->users}` AS c
              LEFT JOIN `{$wpdb->usermeta}` AS um_first_name ON um_first_name.user_id = c.ID AND um_first_name.meta_key = 'first_name'
              LEFT JOIN `{$wpdb->usermeta}` AS um_last_name ON um_last_name.user_id = c.ID AND um_last_name.meta_key = 'last_name'
              LEFT JOIN `{$wpdb->usermeta}` AS cap ON cap.user_id = c.ID
              LEFT OUTER JOIN (
                SELECT COUNT(DISTINCT(posts.ID)) AS posts_count, 
                meta.meta_value AS id_customer,
                posts.post_status,
                posts.ID,
                SUM(meta_order_total.meta_value) AS orders_total
                FROM `{$wpdb->posts}` AS posts
                LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts.ID = meta.post_id
                LEFT JOIN `{$wpdb->postmeta}` AS meta_order_total ON meta_order_total.post_id = posts.ID AND meta_order_total.meta_key = '_order_total'";

        if (!function_exists('wc_get_order_status_name')) {
            $sql .= " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts_orders.ID
                        AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                      LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
        }

        $sql .= " WHERE meta.meta_key = '_customer_user' AND posts.post_type = 'shop_order'";

        if (!empty($this->status_list_hide)) {
            $post_status_not_in = " posts.post_status NOT IN ('" . implode("','", $this->status_list_hide) . "')";
            $sql .= ' AND ' . $post_status_not_in;
        }

        if (!empty($this->order_statuses)) {
            if (function_exists('wc_get_order_status_name')) {
                $post_status_in = sprintf(" posts.post_status IN ('%s')", implode("', '", $this->order_statuses));
                $sql .= ' AND ' . $post_status_in;
            } else {
                $post_status_in = sprintf(" status_terms.slug IN ('%s')", implode("', '", $this->order_statuses));
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
                            meta_fname.meta_value AS first_name,
                            meta_lname.meta_value AS last_name,
                            CONCAT(meta_fname.meta_value, ' ', meta_lname.meta_value) AS full_name,
                            posts.post_date_gmt AS date_add,
                            meta_email.meta_value AS email,
                            ' ' AS display_name,
                            COUNT(posts.ID) AS orders_count,
                            SUM(meta_order_total.meta_value) AS orders_total";

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
                        LEFT JOIN `{$wpdb->postmeta}` AS meta_order_total ON meta_order_total.post_id = posts.ID 
                        AND meta_order_total.meta_key = '_order_total'
                    WHERE posts.post_type = 'shop_order' AND meta_customer.meta_value = 0";

            $query_not_registered = $fields_not_register . $sql_not_registered;
            $query_page_not_registered = $total_fields_not_registered . $sql_not_registered;

            if (!empty($post_status_in)) {
                $query_where_not_registered[] = $post_status_in;
            }

            if (!empty($post_status_not_in)) {
                $query_where_not_registered[] = $post_status_not_in;
            }
        }

        if ($this->dateFrom) {
            $query_where_parts[] = sprintf(
                " UNIX_TIMESTAMP(CONVERT_TZ(c.user_registered, '+00:00', @@global.time_zone)) >= '%d'",
                $this->dateFrom
            );

            if ($this->show_all_customers && !empty($query_not_registered) && !empty($query_page_not_registered)) {
                $query_where_not_registered[] = sprintf(
                    " UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) >= '%d'",
                    $this->dateFrom
                );
            }
        }

        if ($this->dateTo) {
            $query_where_parts[] = sprintf(
                " UNIX_TIMESTAMP(CONVERT_TZ(c.user_registered, '+00:00', @@global.time_zone)) <= '%d'",
                $this->dateTo
            );

            if ($this->show_all_customers && !empty($query_not_registered) && !empty($query_page_not_registered)) {
                $query_where_not_registered[] = sprintf(
                    " UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) <= '%d'",
                    $this->dateTo
                );
            }
        }

        if (!empty($this->search_phrase) && preg_match('/^\d+(?:,\d+)*$/', $this->search_phrase)) {
            $query_where_parts[] = sprintf(" c.ID IN (%s)", $this->search_phrase);

            if ($this->show_all_customers && !empty($query_not_registered)) {
                $query_where_not_registered[] = sprintf(" -1 IN (%s)", $this->search_phrase);
            }
        } elseif (!empty($this->search_phrase)) {
            $query_where_parts[] = sprintf(
                " (c.user_email LIKE '%%%s%%'
                    OR CONCAT(um_first_name.meta_value, ' ', um_last_name.meta_value) LIKE '%%%s%%'
                    OR c.display_name LIKE '%%%s%%')", $this->search_phrase, $this->search_phrase, $this->search_phrase
            );

            if ($this->show_all_customers && !empty($query_not_registered)) {
                $query_where_not_registered[] = sprintf(
                    " (meta_email.meta_value LIKE '%%%s%%'
                    OR CONCAT(meta_fname.meta_value, ' ', meta_lname.meta_value) LIKE '%%%s%%' )", $this->search_phrase, $this->search_phrase
                );
            }
        }

        if ($this->only_with_orders) {
            $query_where_parts[] = ' posts.ID > 0 ';

            if ($this->show_all_customers && !empty($query_not_registered)) {
                $query_where_not_registered[] = ' posts.ID > 0 ';
            }
        }

        if (!empty($query_where_not_registered)) {
            $query_not_registered .= ' AND ' . implode(' AND ', $query_where_not_registered);
            $query_page_not_registered .= ' AND ' . implode(' AND ', $query_where_not_registered);
        }

        if (!empty($query_where_parts)) {
            $query .= ' AND ' . implode(' AND ', $query_where_parts);
            $query_page .= ' AND ' . implode(' AND ', $query_where_parts);
        }

        $query .= ' GROUP BY c.user_email';

        if ($this->show_all_customers && !empty($query_not_registered)) {
            $query_not_registered .= ' GROUP BY meta_email.meta_value';
            $query = $query_not_registered . ' UNION ' . $query;
        }

        if (empty($this->sort_field)) $this->sort_field = self::ORDER_BY_ID;

        $query .= ' ORDER BY ';
        switch ($this->sort_field) {
            case self::ORDER_BY_ID:
                $query .= 'id_customer ' . $this->getSortDirection($this->sort_direction);
                break;
            case self::ORDER_BY_DATE_CREATED:
                $query .= 'date_add ' . $this->getSortDirection($this->sort_direction);
                break;
            case self::ORDER_BY_NAME:
                $query .= 'full_name ' . $this->getSortDirection($this->sort_direction, 'ASC');
                break;
            case self::ORDER_BY_ORDERS_COUNT:
                $query .= 'orders_count ' . $this->getSortDirection($this->sort_direction, 'ASC');
                break;
            case self::ORDER_BY_ORDERS_TOTAL:
                $query .= 'posts.orders_total ' . $this->getSortDirection($this->sort_direction, 'ASC');
                break;
        }

        $query .= sprintf(' LIMIT %d, %d', (($this->page_index - 1) * $this->page_size), $this->page_size);

        $customers = array();
        $results = $wpdb->get_results($query, ARRAY_A);

        foreach ($results as $user) {
            $first_name = trim($user['first_name']);
            $last_name = trim($user['last_name']);
            if (empty($first_name) && empty($last_name)) {
                $first_name = $user['display_name'];
            }

            if (empty($first_name) && empty($last_name)) {
                $first_name = __('Guest', 'mobile-assistant-connector');
            }

            $userData = array(
                "customer_id" => $user['id_customer'],
                "first_name" => $first_name,
                "last_name" => $last_name,
                "email" => $user['email'],
                "date_add" => self::convertTimestampToMillisecondsTimestamp((int)strtotime($user['date_add'])),
                "orders_count" => $user['orders_count'],
                "orders_total" => $user['orders_total'],
                "formatted_orders_total" => mobassist_nice_price($user['orders_total'], false, false, false, true),
            );

            $customers[] = $userData;
        }

        if ((int)$this->show_all_customers) {
            $query_page = $query_page . ' UNION ' . $query_page_not_registered;
        }

        $count_custs = count($wpdb->get_col($query_page, 0));

        return array(
            'customers_count' => (int)$count_custs,
            'customers' => $customers
        );
    }

    public function search_customers()
    {
        return $this->get_customers();
    }


    public function get_customer_details()
    {
        global $wpdb;

        $customer = array();

        if ($this->id != -1 && $this->id > 0) {
            $this->id = mobassist_validate_post($this->id, 'customer');

            if (!$this->id || empty($this->id)) {
                $this->generate_output_error(EM1Exception::ERROR_CODE_CUSTOMER_NOT_FOUND);
                return false;
            }

            $user = new WP_User($this->id);

            if (empty($this->only_items)) {
                $firstname = $user->first_name;
                if(empty(trim($firstname))) $firstname = $user->data->display_name;

                $customer['customer_id'] = $this->id;
                $customer['username'] = $user->data->user_login;
                $customer['nickname'] = $user->nickname;
                $customer['first_name'] = $firstname;
                $customer['last_name'] = $user->last_name;
                $customer['display_name'] = $user->data->display_name;
                $customer['email'] = $user->data->user_email;
                $customer['website'] = $user->data->user_url;
                $customer['date_add'] = self::convertTimestampToMillisecondsTimestamp((int)strtotime($user->user_registered));

                $customer['shipping_first_name'] = $user->shipping_first_name;
                $customer['shipping_last_name'] = $user->shipping_last_name;
                $customer['shipping_company'] = $user->shipping_company;
                $customer['shipping_address1'] = $user->shipping_address_1;
                $customer['shipping_address2'] = $user->shipping_address_2;
                $customer['shipping_city'] = $user->shipping_city;
                $customer['shipping_post_code'] = $user->shipping_postcode;
                $customer['shipping_state'] = $user->shipping_state;
                $customer['shipping_country'] = $user->shipping_country;

                $customer['billing_first_name'] = $user->billing_first_name;
                $customer['billing_last_name'] = $user->billing_last_name;
                $customer['billing_company'] = $user->billing_company;
                $customer['billing_address1'] = $user->billing_address_1;
                $customer['billing_address2'] = $user->billing_address_2;
                $customer['billing_city'] = $user->billing_city;
                $customer['billing_post_code'] = $user->billing_postcode;
                $customer['billing_state'] = $user->billing_state;
                $customer['billing_country'] = $user->billing_country;
                $customer['billing_phone'] = $user->billing_phone;
                $customer['billing_email'] = $user->billing_email;
            }

            $customer['orders'] = $this->_get_customer_orders($user->ID);

            $orders_total = $this->_get_customer_orders_total($user->ID);
            $customer['orders_count'] = $orders_total['c_orders_count'];
            $customer['orders_total'] = $orders_total['sum_ords'];
            $customer['formatted_orders_total'] = mobassist_nice_price($orders_total['sum_ords'], false, false, false, true);

        } else {

            // Get not register customer(guest) info
            $select_general_info = "SELECT meta_email.meta_value AS email,
                                    tot.post_date_gmt AS date_add,
                                    meta_fname.meta_value AS first_name,
                                    meta_lname.meta_value AS last_name";

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

            $query_billing_from = sprintf(
                $query_from_part, '_billing_first_name', '_billing_last_name',
                '_billing_company', '_billing_address_1', "_billing_address_2", '_billing_city', '_billing_postcode',
                '_billing_state', '_billing_country'
            );

            $query_billing_from .= "LEFT JOIN
                                        `{$wpdb->postmeta}` AS meta_phone ON meta_phone.post_id = tot.ID
                                            AND meta_phone.meta_key = '_billing_phone'";

            $query_shipping_from = sprintf(
                $query_from_part, '_shipping_first_name', '_shipping_last_name',
                '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city',
                '_shipping_postcode', '_shipping_state', '_shipping_country'
            );

            $query_where_part = " WHERE meta_email.meta_value LIKE '%" . $this->email . "%' GROUP BY meta_email.meta_value ";

            $query_general_info = $select_general_info . $query_billing_from . $query_where_part;

            $query_billing_info = 'SELECT
                                        meta_email.meta_value AS email,
                                        meta_fname.meta_value AS first_name,
                                        meta_lname.meta_value AS last_name,
                                        meta_company.meta_value AS company,
                                        meta_adr1.meta_value AS address_1,
                                        meta_adr2.meta_value AS address_2,
                                        meta_city.meta_value AS city,
                                        meta_postcode.meta_value AS post_code,
                                        meta_state.meta_value AS state,
                                        meta_country.meta_value AS country,
                                        meta_phone.meta_value AS phone' . $query_billing_from . $query_where_part;

            $query_shipping_info = 'SELECT
                                        meta_fname.meta_value AS first_name,
                                        meta_lname.meta_value AS last_name,
                                        meta_company.meta_value AS company,
                                        meta_adr1.meta_value AS address1,
                                        meta_adr2.meta_value AS address2,
                                        meta_city.meta_value AS city,
                                        meta_postcode.meta_value AS post_code,
                                        meta_state.meta_value AS state,
                                        meta_country.meta_value AS country' . $query_shipping_from . $query_where_part;

            $customer_info = $wpdb->get_row($query_general_info);

            if (!$customer_info) {
                $this->generate_output_error(EM1Exception::ERROR_CODE_CUSTOMER_NOT_FOUND);
                return false;
            }

            $customer = array(
                'customer_id' => -1,
                'first_name' => $customer_info->first_name,
                'last_name' => $customer_info->last_name,
                'email' => $customer_info->email,
                'date_add' => self::convertTimestampToMillisecondsTimestamp((int)strtotime($customer_info->date_add))
            );

            $customer['addresses_count'] = 2;
            $shipping_address = $wpdb->get_row($query_shipping_info);
            $shipping_address['customer_id'] = -1;
            $shipping_address['address_type'] = "shipping_address";

            $billing_address = $wpdb->get_row($query_billing_info);
            $billing_address['customer_id'] = -1;
            $billing_address['address_type'] = "billing_address";

            $customer['addresses'][] = $shipping_address;
            $customer['addresses'][] = $billing_address;

            $customer['orders'] = $this->_get_customer_orders($this->id, $this->email);

            $orders_total = $this->_get_customer_orders_total($this->id, $this->email);
            $customer['orders_count'] = $orders_total['c_orders_count'];
            $customer['orders_total'] = $orders_total['sum_ords'];
            $customer['formatted_orders_total'] = mobassist_nice_price($orders_total['sum_ords'], false, false, false, true);
        }

        return array('customer' => $customer);
    }

    public function get_customer_orders()
    {
        if ($this->id != -1 && $this->id > 0) {
            $this->id = mobassist_validate_post($this->id, 'customer');

            if (!$this->id || empty($this->id)) {
                $this->generate_output_error(EM1Exception::ERROR_CODE_CUSTOMER_NOT_FOUND);
                return false;
            }

            $user = new WP_User($this->id);

            return array('orders' => $this->_get_customer_orders($user->ID));
        } else {
            return array('orders' => $this->_get_customer_orders($this->id, $this->email));
        }
    }

    public function get_products()
    {
        global $wpdb;

        $fields = 'SELECT
            posts.ID AS product_id,
            posts.post_title AS product_name,
            posts.post_status AS post_status,
            meta_sku.meta_value AS sku,
            meta_stock_status.meta_value AS stock,
            meta_price.meta_value AS regular_price,
            meta_sale_price.meta_value AS sale_price,
            meta_manage_stock.meta_value AS manage_stock,
            meta_stock.meta_value AS quantity';

        $fields_total = 'SELECT COUNT(DISTINCT(posts.ID)) AS count_prods';

        $sql = " FROM `$wpdb->posts` AS posts
            LEFT JOIN `$wpdb->postmeta` AS meta_price ON meta_price.post_id = posts.ID AND meta_price.meta_key = '_regular_price'
            LEFT JOIN `$wpdb->postmeta` AS meta_sale_price ON meta_sale_price.post_id = posts.ID AND meta_sale_price.meta_key = '_sale_price'
            LEFT JOIN `$wpdb->postmeta` AS meta_sku ON meta_sku.post_id = posts.ID AND meta_sku.meta_key = '_sku'
            LEFT JOIN `$wpdb->postmeta` AS meta_stock ON meta_stock.post_id = posts.ID AND meta_stock.meta_key = '_stock'
            LEFT JOIN `$wpdb->postmeta` AS meta_manage_stock ON meta_manage_stock.post_id = posts.ID AND meta_manage_stock.meta_key = '_manage_stock'
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

    public function search_products()
    {
        return $this->get_products();
    }


    public function get_product_details()
    {
        global $wpdb;

        $this->id = mobassist_validate_post($this->id, 'product');

        if (!$this->id || empty($this->id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $sql_total_ordered = "SELECT SUM(meta_items_qty.meta_value)
            FROM `{$wpdb->prefix}woocommerce_order_itemmeta` AS order_itemmeta
                LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_items_qty ON order_itemmeta.order_item_id = meta_items_qty.order_item_id AND meta_items_qty.meta_key = '_qty'
            WHERE order_itemmeta.meta_key LIKE '_product_id' AND order_itemmeta.meta_value = posts.ID";

        $sql = "SELECT
                posts.ID AS product_id,
                posts.post_title AS name,
                meta_price.meta_value AS regular_price,
                meta_sale_price.meta_value AS sale_price,
                meta_sku.meta_value AS sku,
                meta_stock.meta_value AS quantity,
                meta_manage_stock.meta_value AS manage_stock,
                meta_stock_status.meta_value AS stock,
                meta_product_url.meta_value AS product_url,
                meta_button_text.meta_value AS button_text,
                meta_sale_price_dates_from.meta_value AS sale_price_dates_from,
                meta_sale_price_dates_to.meta_value AS sale_price_dates_to,
                meta_backorders.meta_value AS allow_backorders,
                meta_low_stock_amount.meta_value AS low_stock_amount,
                meta_sold_individually.meta_value AS sold_individually,
                ({$sql_total_ordered}) AS total_ordered,
                posts.post_status
            FROM `$wpdb->posts` AS posts
                LEFT JOIN `$wpdb->postmeta` AS meta_price ON meta_price.post_id = posts.ID AND meta_price.meta_key = '_regular_price'
                LEFT JOIN `$wpdb->postmeta` AS meta_sale_price ON meta_sale_price.post_id = posts.ID AND meta_sale_price.meta_key = '_sale_price'
                LEFT JOIN `$wpdb->postmeta` AS meta_sku ON meta_sku.post_id = posts.ID AND meta_sku.meta_key = '_sku'
                LEFT JOIN `$wpdb->postmeta` AS meta_stock ON meta_stock.post_id = posts.ID AND meta_stock.meta_key = '_stock'
                LEFT JOIN `$wpdb->postmeta` AS meta_manage_stock ON meta_manage_stock.post_id = posts.ID AND meta_manage_stock.meta_key = '_manage_stock'
                LEFT JOIN `$wpdb->postmeta` AS meta_stock_status ON meta_stock_status.post_id = posts.ID AND meta_stock_status.meta_key = '_stock_status'
                LEFT JOIN `$wpdb->postmeta` AS meta_product_url ON meta_product_url.post_id = posts.ID AND meta_product_url.meta_key = '_product_url'
                LEFT JOIN `$wpdb->postmeta` AS meta_button_text ON meta_button_text.post_id = posts.ID AND meta_button_text.meta_key = '_button_text'
                LEFT JOIN `$wpdb->postmeta` AS meta_sale_price_dates_from ON meta_sale_price_dates_from.post_id = posts.ID AND meta_sale_price_dates_from.meta_key = '_sale_price_dates_from'
                LEFT JOIN `$wpdb->postmeta` AS meta_sale_price_dates_to ON meta_sale_price_dates_to.post_id = posts.ID AND meta_sale_price_dates_to.meta_key = '_sale_price_dates_to'
                LEFT JOIN `$wpdb->postmeta` AS meta_backorders ON meta_backorders.post_id = posts.ID AND meta_backorders.meta_key = '_backorders'
                LEFT JOIN `$wpdb->postmeta` AS meta_low_stock_amount ON meta_low_stock_amount.post_id = posts.ID AND meta_low_stock_amount.meta_key = '_low_stock_amount'
                LEFT JOIN `$wpdb->postmeta` AS meta_sold_individually ON meta_sold_individually.post_id = posts.ID AND meta_sold_individually.meta_key = '_sold_individually'
            WHERE posts.post_type = 'product' AND posts.ID = '%d'";

        if (!empty($this->status_list_hide)) {
            $sql .= " AND posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        $sql = sprintf($sql, $this->id);

        $product = $wpdb->get_row($sql, ARRAY_A);

        // get product images
        $productWP = new WC_product($this->id);
        $url = $productWP->get_permalink();
        $post_data = get_post($this->id);
        $attachment_ids = $this->is_v3() ? $productWP->get_gallery_image_ids() : $productWP->get_gallery_attachment_ids();

        $images = array();
        if (empty($this->without_thumbnails)) {
            $cover_id = get_post_thumbnail_id($product['product_id']);

            $pos = 0;
            if (!empty($cover_id) && $cover_id > 0) {
                $pos++;
                $images[] = array(
                    "image_id" => (int)$cover_id,
                    "position" => $pos,
                    "image_url" => get_image_url($cover_id, 'shop_catalog'),
                    "cover" => true,
//                "image_url_large" => get_image_url($cover_id, 'large')
                );
            }

            foreach ($attachment_ids as $attachment_id) {
                if($attachment_id <= 0) continue;

                $pos++;
                $images[] = array(
                    "image_id" => (int)$attachment_id,
                    "position" => $pos,
                    "image_url" => get_image_url($attachment_id, 'shop_catalog'),
                    "cover" => false,
//                    "image_url_large" => get_image_url($attachment_id, 'large')
                );
            }
        }

        $count_variations = 0;
        $the_product = wc_get_product($this->id);
        if ($the_product->is_type('variable') && $the_product->has_child()) {
            $count_variations = count($the_product->get_children());
        }

        $sale_price = isset($product['sale_price']) && !empty($product['sale_price']) ? $product['sale_price'] : false;
        $product_type = $this->_get_product_type($product['product_id']);
        $post_status = $this->_get_product_status($product['post_status']);

        if (function_exists('wc_get_product')) {
            $wc_product = wc_get_product($product['product_id']);
        } else {
            $wc_product = get_product($product['product_id']);
        }

//        $product_url = null;
//        if($product_type == 'External/Affiliate') {
//            $product_url = $wc_product->get_product_url();
//        }

        $weight = $wc_product->get_weight();
        if ($this->is_v3()) {
            $length = $wc_product->get_length();
            $width  = $wc_product->get_width();
            $height = $wc_product->get_height();
        } else {
            $length = $wc_product->length;
            $width = $wc_product->width;
            $height = $wc_product->height;
        }

        $weight_unit    = get_option('woocommerce_weight_unit');
        $dimension_unit = get_option('woocommerce_dimension_unit');

        $tax_status = $this->getTaxStatusTitle($wc_product);
        $tax_class = $this->getTaxClassTitle($wc_product);
        $allow_backorders = $this->getAllowBackordersTitle($product['allow_backorders']);

        $shipping_class = null;
        if ($class_id = $wc_product->get_shipping_class_id() ) {
            $term = get_term_by('id', $class_id, 'product_shipping_class');
            $shipping_class = $term->name;
        }

        $sale_price_dates_from = $product['sale_price_dates_from'] ? wp_date('Y-m-d', $product['sale_price_dates_from']) : null;
        $sale_price_dates_to   = $product['sale_price_dates_to'] ? wp_date('Y-m-d', $product['sale_price_dates_to']) : null;

        $product_details['product'] = array(
            'product_id' => (int)$product['product_id'],
            'product_type' => $product_type,
            'product_name' => $product['name'],
            'sku' => $product['sku'],
            'url' => $url,
            'total_ordered' => (int)$product['total_ordered'],
            'images' => $images,
            'regular_price' => $product['regular_price'] ? floatval($product['regular_price']) : null,
            'formatted_price' => $product['regular_price'] ? mobassist_nice_price($product['regular_price'], $this->currency, false, false, true) : null,
            'sale_price' => $sale_price ? floatval($sale_price) : null,
            'formatted_sale_price' => $sale_price ? mobassist_nice_price($sale_price, $this->currency, false, false, true) : null,
            'price_html' => $this->_get_product_html_price($product['product_id'], $product_type),
            'quantity' => (int)$product['quantity'],
            'manage_stock' => $product['manage_stock'],
            'stock' => $product['stock'],
            'status' => $post_status,
            'combinations_count' => (int)$count_variations,
            'description' => $post_data->post_content,
            'short_description' => $post_data->post_excerpt,
            'product_url' => $product['product_url'],
            'button_text' => $product['button_text'],
            'sale_price_dates_from' => $sale_price_dates_from,
            'sale_price_dates_to' => $sale_price_dates_to,
            'tax_status' => $tax_status,
            'tax_class' => $tax_class,
            'allow_backorders' => $allow_backorders,
            'low_stock_amount' => $product['low_stock_amount'],
            'sold_individually' => $product['sold_individually'],
            'weight' => $weight,
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'weight_unit' => $weight_unit,
            'dimension_unit' => $dimension_unit,
            'shipping_class' => $shipping_class,
        );

        return $product_details;
    }

    public function get_product_edit_data()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        $this->product_id = mobassist_validate_post($this->id, 'product');

        if (!$this->product_id || empty($this->product_id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $product = new WC_Product($this->product_id);
        $post_data = get_post($this->product_id);

        $tags_comma_separated = "";
        $tags_array = get_the_terms($this->product_id, 'product_tag');
        if ($tags_array) {
            $tags = array();
            foreach ($tags_array as $tag) {
                $tags[] = $tag->name;
            }
            $tags_comma_separated = implode(",", $tags);
        }

        $categories = wp_get_post_terms($this->product_id, 'product_cat', array('fields'=>'ids'));

        $regular_price = get_post_meta($this->product_id, '_regular_price', true);
        $sale_price = get_post_meta($this->product_id, '_sale_price', true);

        $dates_from = get_post_meta($this->product_id, '_sale_price_dates_from', true);
        $dates_to = get_post_meta($this->product_id, '_sale_price_dates_to', true);
//        $sale_price_dates_from = self::convertTimestampToMillisecondsTimestamp((int)$dates_from);
//        $sale_price_dates_to = self::convertTimestampToMillisecondsTimestamp((int)$dates_to);

        $sale_price_dates_from = $dates_from ? wp_date('Y-m-d', $dates_from) : null;
        $sale_price_dates_to   = $dates_to ? wp_date('Y-m-d', $dates_to) : null;

        $tax_status = $product->get_tax_status();
        $tax_class = $product->get_tax_class('edit');

//        $tax_status_title = $this->getTaxStatusTitle($product);
//        $tax_class_title = $this->getTaxClassTitle($product);
//        $allow_backorders = get_post_meta($this->product_id, '_backorders', true);
//        $allow_backorders_title = $this->getAllowBackordersTitle($allow_backorders);
        $_downloadable_files = get_post_meta($this->product_id, '_downloadable_files', true);
        $_downloadable_files_count = 0;
        if($_downloadable_files) $_downloadable_files_count = count($_downloadable_files);

        $count_variations = 0;
        $attributes_count = 0;
        $the_product = wc_get_product($this->id);
        if ($the_product->is_type('variable')) {
            $count_variations = count($the_product->get_children());
            //$attributes_count = count($the_product->get_attributes('edit'));
        }
        $attributes_count = count($the_product->get_attributes('edit'));

        $result = array(
            'product' => array(
                'product_id' => $this->product_id,
                'product_name' => $post_data->post_title,
                'product_type' => $this->_get_product_type($this->product_id),
//                'description' => $post_data->post_content,
//                'short_description' => $post_data->post_excerpt,
                'is_short_description_filled' => !empty($post_data->post_excerpt),
                'is_description_filled' => !empty($post_data->post_content),
                'tax_status' => $tax_status,
//                'tax_status_title' => $tax_status_title,
                'tax_class' => $tax_class,
//                'tax_class_title' => $tax_class_title,
                'status' => $post_data->post_status,
                'catalog_visibility' => $product->get_catalog_visibility(),
//                'comment_status' => $post_data->comment_status,
                'menu_order' => $post_data->menu_order,
                'sku' => get_post_meta($this->product_id, '_sku', true),
                'regular_price' => !empty($regular_price) ? self::round(floatval($regular_price)) : null,
                'sale_price' => !empty($sale_price) ? self::round(floatval($sale_price)) : null,
                'sale_price_dates_from' => $sale_price_dates_from,
                'sale_price_dates_to' => $sale_price_dates_to,
//                'manage_stock' => 'yes' === get_post_meta($this->product_id, '_manage_stock', true),
                'manage_stock' => self::_string_to_bool(get_post_meta($this->product_id, '_manage_stock', true)),
                'stock_quantity' => (int)get_post_meta($this->product_id, '_stock', true),
//                'backorders_allow' => self::_string_to_bool(get_post_meta($this->product_id, '_backorders', true)),
                'backorders_allow' => get_post_meta($this->product_id, '_backorders', true),
//                'backorders_allow_title' => $allow_backorders_title,
                'stock_status' => get_post_meta($this->product_id, '_stock_status', true),
                'sold_individually' => self::_string_to_bool(get_post_meta($this->product_id, '_sold_individually', true)),
                'enable_reviews' => 'open' === $post_data->comment_status,
                'product_tags' => $tags_comma_separated,
                'assigned_categories' => count($categories),
                'purchase_note' => get_post_meta($this->product_id, '_purchase_note', true),
                'product_url' => get_post_meta($this->product_id, '_product_url', true),
                'button_text' => get_post_meta($this->product_id, '_button_text', true),
                'virtual' => get_post_meta($this->product_id, '_virtual', true),
                'downloadable' => get_post_meta($this->product_id, '_downloadable', true),
                'download_limit' => get_post_meta($this->product_id, '_download_limit', true) === "" ? null : get_post_meta($this->product_id, '_download_limit', true),
                'download_expiry' => get_post_meta($this->product_id, '_download_expiry', true) === "" ? null : get_post_meta($this->product_id, '_download_expiry', true),
                'downloadable_files' => get_post_meta($this->product_id, '_downloadable_files', true),
                'downloadable_files_count' => $_downloadable_files_count,
                'variations_count' => (int)$count_variations,
                'attributes_count' => (int)$attributes_count,
                'publish_visibility' => self::getProductVisibility($post_data),
                'publish_password_visibility' => $post_data->post_password,
            ),
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'product_types' => wc_get_product_types(),
            'product_stock_statuses' => json_encode(wc_get_product_stock_status_options()),
            'backorder_options' => json_encode(wc_get_product_backorder_options()),
            'product_statuses' => json_encode(self::getProductStatuses()),
            'catalog_visibilities' => json_encode(wc_get_product_visibility_options()),
            'visibilities' => json_encode(self::getProductVisibilities()),
            'tax_statuses' => json_encode(self::getTaxStatuses()),
            'tax_classes' => json_encode($this->getTaxClasses())
        );

        // Get product type
//        $terms = wp_get_object_terms($this->product_id, 'product_type');
//        $result['product']['product_type'] = $terms[0]->name;

        $pos = 0;
        // Get product main image
        $thumbnail_id = get_post_meta($this->product_id, '_thumbnail_id', true);

        $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        $post_data = get_post($thumbnail_id);
        $title = $post_data->post_title;
        $caption = $post_data->post_excerpt;
        $description = $post_data->post_content;

        $images = array();
        if (!empty($thumbnail_id) && $thumbnail_id > 0) {
//            $result['product']['main_image'] = array(
            $images[] = array(
                'image_id' => (int)$thumbnail_id,
                'image_url' => get_image_url($thumbnail_id),
                'position' => $pos,
                'cover' => true,
                'alt_text' => $alt_text,
                'title' => $title,
                'caption' => $caption,
                'description' => $description
            );
            $pos++;
        }

        // Get product gallery images
        $image_ids = $product->get_gallery_image_ids();
        $count = count($image_ids);
        for ($i = 0; $i < $count; $i++) {
            $id = (int)$image_ids[$i];
//            if($thumbnail_id > 0 && $id == $thumbnail_id) continue;
            if($id <= 0) continue;
            $pos++;

            $alt_text = get_post_meta($id, '_wp_attachment_image_alt', true);
            $image_data = get_post($id);
            $title = $image_data->post_title;
            $caption = $image_data->post_excerpt;
            $description = $image_data->post_content;

            $images[] = array(
                'image_id' => $id,
                'image_url' => get_image_url($id),
                'position' => $pos,
                'cover' => false,
                'alt_text' => $alt_text,
                'title' => $title,
                'caption' => $caption,
                'description' => $description
            );
        }
        $result['product']['images'] = $images;

        // Max file size allowed to upload
        $result['max_file_upload_size'] = self::getMaxFileUploadInBytes();

        return $result;
    }


    public function get_product_edit_description()
    {
        global $wpdb;

        $this->product_id = mobassist_validate_post($this->id, 'product');

        if (!$this->product_id || empty($this->product_id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $sql = "SELECT post_content AS description FROM `$wpdb->posts` WHERE post_type = 'product' AND ID = '%d'";

        $sql = sprintf($sql, $this->product_id);

        if ($product_descr = $wpdb->get_row($sql, ARRAY_A)) {
            return $product_descr;
        }

        return false;
    }

    public function get_product_edit_short_description()
    {
        global $wpdb;

        $this->product_id = mobassist_validate_post($this->id, 'product');

        if (!$this->product_id || empty($this->product_id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $sql = "SELECT post_excerpt AS description FROM `$wpdb->posts` WHERE post_type = 'product' AND ID = '%d'";

        $sql = sprintf($sql, $this->product_id);

        if ($product_descr = $wpdb->get_row($sql, ARRAY_A)) {
            return $product_descr;
        }

        return false;
    }


    public function get_product_edit_attributes()
    {
        $this->id = mobassist_validate_post($this->id, 'product');

        if (!$this->id || empty($this->id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $the_product = wc_get_product($this->id);
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        $attributes = $the_product->get_attributes('edit');

        $product_attributes = array();
        $default_attributes = array();

        $assigned_attributes = array();
        foreach ($attributes as $attr) {
            $assigned_attributes[] = $attr->get_name();
        }

        foreach ($attribute_taxonomies as $attribute) {
            if (is_wp_error($attribute)) continue;

            $attribute_name = "pa_".$attribute->attribute_name;

            $all_terms = get_terms($attribute_name);
            $attribute_terms = array();
            foreach ($all_terms as $term) {
                if(is_wp_error($term)) continue;
                $attribute_terms[] = array(
                    "term_id" => $term->term_id,
                    "name" => $term->name,
                    "slug" => $term->slug
                );
            }

            $default_attributes[] = array(
                "attribute_id" => $attribute_name, // "pa_1"
                "id" => $attribute->attribute_id, // "1"
                "name" => $attribute->attribute_label, // "Test attribute"
                "attribute_public" => $attribute->attribute_public, // int(0)
                "options" => $attribute_terms,
                "assigned" => in_array($attribute_name, $assigned_attributes)
            );

        }

        foreach ($attributes as $attr) {
            $product_attributes[] = array(
                "attribute_id" => $attr->get_name(),
                "name" => wc_attribute_label($attr->get_name()),
                "options" => $attr->get_options(),
                "position" => $attr->get_position(),
                "visible_on_page" => $attr->get_visible(),
                "used_for_variation" => $attr->get_variation(),
                "is_default_attribute" => array_search($attr->get_name(), array_column($default_attributes, 'attribute_id')) !== false
            );
        }

        return array(
            'product_attributes' => $product_attributes,
            'default_attributes' => $default_attributes,
            'product_attributes_count' => count($product_attributes),
        );
    }


    public function add_product_attribute()
    {
        $this->is_new_item = true;
        return $this->save_product_attribute();
    }


    public function save_product_attribute()
    {
        $this->id = mobassist_validate_post($this->id, 'product');

        if (!$this->id || empty($this->id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $the_product = wc_get_product($this->id);
        /*if ( !$the_product->is_type( 'variable' ) ) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_TYPE_IS_INCORRECT);
            return false;
        }*/

        if (empty($this->data)) {
            $this->generate_output_error(EM1Exception::ERROR_CODE_UNKNOWN_REQUEST);
            return false;
        }

        $data = $this->data;
        $meta_values = array();
        $attributes = $the_product->get_attributes('edit');

        if ($this->is_new_item) {
            if(!$data['is_default_attribute']) $data["attribute_id"] = $data["name"];
            $attribute = new WC_Product_Attribute();
            !$data['is_default_attribute'] ? $attribute->set_id(0) : $attribute->set_id(wc_attribute_taxonomy_id_by_name($data["attribute_id"]));
            $attribute->set_name($data["attribute_id"]);
            $attribute->set_position(count($attributes));
            $attributes[$data["attribute_id"]] = $attribute;
        }

        foreach ($attributes as $attribute_key => $attr) {
            if ($attr->get_name() == $data["attribute_id"] || $attr->get_name() == wc_attribute_label($data["attribute_id"])) {
                if (isset($data["name"]) && !is_null($data["name"]) && !$data['is_default_attribute']) {
                    $attr->set_name($data["name"]);
                    $data["attribute_id"] = $data["name"];
                }
                if (!$attr->is_taxonomy() && isset($data["options"]) && !is_null($data["options"])) $attr->set_options($data["options"]);
                if (isset($data["is_visible_on_page"]) && !is_null($data["is_visible_on_page"])) $attr->set_visible($data["is_visible_on_page"]);
                //if (isset($data["is_used_for_variation"]) && !is_null($data["is_used_for_variation"])) $attr->set_variation($data["is_used_for_variation"]);
                if (isset($data["is_used_for_variation"]) && !is_null($data["is_used_for_variation"]) && $the_product->is_type('variable')) {
                    $attr->set_variation($data["is_used_for_variation"]);
                } else {
                    //$attr->set_variation(0);
                }
            }

            $value = '';
            if ($attr->is_taxonomy()) {
                if (isset($data["options"]) && !is_null($data["options"]) && $attr->get_name() == $data["attribute_id"]) {
                    $options = array_map('intval', $data["options"]);
                    wp_set_object_terms($the_product->get_id(), $options, $attr->get_name());
                }
            } else {
                $value = wc_implode_text_attributes($attr->get_options());
            }

            $meta_values[$attribute_key] = array(
                'name' => $attr->get_name(),
                'value' => $value,
                'position' => $attr->get_position(),
                'is_visible' => $attr->get_visible() ? 1 : 0,
                'is_variation' => $attr->get_variation() ? 1 : 0,
                'is_taxonomy' => $attr->is_taxonomy() ? 1 : 0,
            );
        }

        update_post_meta($the_product->get_id(), '_product_attributes', wp_slash($meta_values));

        $product_attribute = (object) array();
        $the_product = wc_get_product($this->id);
        $attributes = $the_product->get_attributes('edit');

        foreach ($attributes as $attr) {
            if ($attr->get_name() == $data["attribute_id"] || $attr->get_name() == wc_attribute_label($data["attribute_id"])) {
                $product_attribute = array(
                    "attribute_id" => $attr->get_name(),
                    "name" => wc_attribute_label($attr->get_name()),
                    "options" => $attr->get_options(),
                    "position" => $attr->get_position(),
                    "visible_on_page" => $attr->get_visible(),
                    "used_for_variation" => $attr->get_variation(),
                    "is_default_attribute" => $data["is_default_attribute"]
                );
            }
        }

        return array('product_attribute' => $product_attribute, 'product_attributes_count' => count($attributes));
    }


    public function delete_product_attribute()
    {
        $this->id = mobassist_validate_post($this->id, 'product');

        if (!$this->id || empty($this->id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $the_product = wc_get_product($this->id);
        /*if (!$the_product->is_type('variable')) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_TYPE_IS_INCORRECT);
            return false;
        }*/

        $meta_values = array();
        $attributes = $the_product->get_attributes('edit');
        foreach ($attributes as $attribute_key => $attr) {
            if ($attr->get_name() == $this->attribute_id || $attr->get_name() == wc_attribute_label($this->attribute_id)) {
                continue;
            }

            $value = '';
            if (!$attr->is_taxonomy()) {
                $value = wc_implode_text_attributes($attr->get_options());
            }

            $meta_values[$attribute_key] = array(
                'name'         => $attr->get_name(),
                'value'        => $value,
                'position'     => $attr->get_position(),
                'is_visible'   => $attr->get_visible() ? 1 : 0,
                'is_variation' => $attr->get_variation() ? 1 : 0,
                'is_taxonomy'  => $attr->is_taxonomy() ? 1 : 0,
            );
        }

        update_post_meta($the_product->get_id(), '_product_attributes', wp_slash($meta_values));

        return array(
            'product_attributes_count' => count($attributes),
        );
    }


    public function get_new_variation_data()
    {
        $this->id = mobassist_validate_post($this->id, 'product');

        if (!$this->id || empty($this->id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $the_product = wc_get_product($this->id);
        if (!$the_product->is_type('variable')) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_TYPE_IS_INCORRECT);
            return false;
        }

        $tax_options = array( 'parent' => __('Same as parent', 'woocommerce') ) + wc_get_product_tax_class_options();
        $backorder_options = wc_get_product_backorder_options();
        $stock_status_options = wc_get_product_stock_status_options();

        $attributes = $the_product->get_attributes();
        $product_attributes = array();
        foreach ($attributes as $attr) {
            $product_attribute_slug = $attr->get_name();
            $attr_options = array();

            $attribute_values = get_terms($product_attribute_slug);
            if (!is_wp_error($attribute_values)) {
                foreach ($attribute_values as $val) {
                    if (in_array($val->term_id, $attr->get_options())) {
                        $attr_options[] = $val->slug;
                    }
                }
            } else {
                $attr_options = $attr->get_options();
            }

            $product_attributes[] = array(
                "name" => wc_attribute_label($product_attribute_slug),
                "slug" => $product_attribute_slug,
                "options" => $attr_options,
                "position" => $attr->get_position(),
                "visible" => $attr->get_visible(),
                "variation" => $attr->get_variation()
            );
        }

        $available_variations = [];

        $weight_unit    = get_option('woocommerce_weight_unit');
        $dimension_unit = get_option('woocommerce_dimension_unit');

        return array(
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'tax_options' => json_encode($tax_options),
            'backorder_options' => json_encode($backorder_options),
            'stock_status_options' => json_encode($stock_status_options),
            'weight_unit' => $weight_unit,
            'dimension_unit' => $dimension_unit,
            'attributes' => $product_attributes,
            'variations' => $available_variations,
            'variations_count' => 0,
            'max_file_upload_size' => self::getMaxFileUploadInBytes()
        );
    }


    public function get_product_edit_variations()
    {
        $this->id = mobassist_validate_post($this->id, 'product');

        if (!$this->id || empty($this->id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $the_product = wc_get_product($this->id);
        if (!$the_product->is_type('variable')) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_TYPE_IS_INCORRECT);
            return false;
        }

        $tax_options = array( 'parent' => __('Same as parent', 'woocommerce') ) + wc_get_product_tax_class_options();
        $backorder_options = wc_get_product_backorder_options();
        $stock_status_options = wc_get_product_stock_status_options();
        $attributes = $the_product->get_attributes();
        $product_attributes = array();
        foreach ($attributes as $attr) {
            $product_attribute_slug = $attr->get_name();
            $attr_options = array();

            $attribute_values = get_terms($product_attribute_slug);
            if (!is_wp_error($attribute_values)) {
                foreach ($attribute_values as $val) {
                    if (in_array($val->term_id, $attr->get_options())) {
                        $attr_options[] = $val->slug;
                    }
                }
            } else {
                $attr_options = $attr->get_options();
            }

            $product_attributes[] = array(
                "name" => wc_attribute_label($product_attribute_slug),
                "slug" => $product_attribute_slug,
                "options" => $attr_options,
                "position" => $attr->get_position(),
                "visible" => $attr->get_visible(),
                "variation" => $attr->get_variation()
            );
        }

        $variation_ids = $the_product->get_children();
        $available_variations = [];

        $weight_unit    = get_option('woocommerce_weight_unit');
        $dimension_unit = get_option('woocommerce_dimension_unit');

        foreach ( $variation_ids as $variation_id ) {
            $variation = wc_get_product($variation_id);
            if(!$variation) continue;

            $variation_array = $the_product->get_available_variation($variation);
            $attributes = $variation->get_attributes();
            $variation_array['name'] = implode(', ', array_filter($attributes));

            $variation_array['variation_tax_class'] = $variation->get_tax_class('edit');
            $variation_array['variation_stock_status'] = $variation->get_stock_status('edit');
//            $variation_array['variation_backorders'] = $variation->get_backorders('edit');

            $dates_from = $variation->get_date_on_sale_from();
            $dates_to = $variation->get_date_on_sale_to();

            $variation_array['date_on_sale_from']    = $dates_from ? wp_date('Y-m-d', $dates_from->getTimestamp()) : null;
            $variation_array['date_on_sale_to']      = $dates_to ? wp_date('Y-m-d', $dates_to->getTimestamp()) : null;
            $variation_array['on_sale']              = $variation->is_on_sale();
            $variation_array['downloads']            = $this->get_downloads($variation);
            $variation_array['download_limit']       = '' !== $variation->get_download_limit() ? (int) $variation->get_download_limit() : -1;
            $variation_array['download_expiry']      = '' !== $variation->get_download_expiry() ? (int) $variation->get_download_expiry() : -1;
            $variation_array['permalink']            = $variation->get_permalink();
            $variation_array['variation_backorders'] = $variation->get_backorders('edit');
            $variation_array['stock_quantity']       = $variation->get_stock_quantity('edit');
            $variation_array['variable_enabled']     = $variation->get_status();
            $variation_array['is_enabled']           = (bool)$variation->get_status() == "publish";
            $variation_array['manage_stock']         = (bool)$variation->get_manage_stock();
            $variation_array['max_qty']              = (int)$variation_array['max_qty'];
            $variation_array['min_qty']              = (int)$variation_array['min_qty'];
            $variation_array['price_html']           = trim(strip_tags($variation->get_price_html(), '<del><ins>'));
            $variation_array['sku']                  = $variation->get_sku('edit');
            $variation_array['image']                = wc_get_product_attachment_props($variation->get_image_id('edit'));
            $variation_array['display_price']        = self::round(wc_get_price_excluding_tax($variation));
            $variation_array['display_regular_price']= self::round(wc_get_price_excluding_tax($variation, array( 'price' => $variation->get_regular_price('edit'))));

            $variation_array['height'] =  $variation->get_height();
            $variation_array['width'] = $variation->get_width();
            $variation_array['length'] = $variation-> get_length();
            $variation_array['weight'] = $variation->get_weight();

            $variation_attributes = array();
            if (isset($variation_array['attributes'])) {
                foreach ($variation_array['attributes'] as $name => $val) {
                    $variation_attributes[] = array(
                        "attribute" => wc_attribute_label(str_replace('attribute_', '', $name), $the_product),
                        "value" => $val);
                }
            }
            $variation_array['variation_attributes'] = $variation_attributes;
            unset($variation_array['attributes']);

            if (isset($variation_array['display_price']) && !$variation_array['on_sale']) {
                $variation_array['display_price'] = null;
            } elseif (isset($variation_array['display_price']) && $variation_array['on_sale']) {
                $variation_array['display_price'] = !empty($variation_array['display_price']) ? floatval($variation_array['display_price']) : null;
            }
            if(isset($variation_array['display_regular_price'])) $variation_array['display_regular_price'] = !empty($variation_array['display_regular_price']) ? floatval($variation_array['display_regular_price']) : null;
            if(isset($variation_array['stock_quantity'])) $variation_array['stock_quantity'] = intval($variation_array['stock_quantity']);
            else $variation_array['stock_quantity'] = 0;

            if (isset($variation_array['image'])) {
                $image = $variation_array['image'];
                $variation_array['image'] = [
                    "url" => $image["url"],
                    "src" => $image["src"]
                ];
            }

            $available_variations[] = $variation_array;
        }

        $available_variations = array_values(array_filter($available_variations));

        $count_variations = count($variation_ids);

        return array(
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'tax_options' => json_encode($tax_options),
            'backorder_options' => json_encode($backorder_options),
            'stock_status_options' => json_encode($stock_status_options),
            'weight_unit' => $weight_unit,
            'dimension_unit' => $dimension_unit,
            'attributes' => $product_attributes,
            'variations' => $available_variations,
            'variations_count' => $count_variations,
            'max_file_upload_size' => self::getMaxFileUploadInBytes()
        );
    }


    protected function get_downloads( $product )
    {
        $downloads = array();

        if ($product->is_downloadable()) {
            foreach ( $product->get_downloads() as $file_id => $file ) {
                $downloads[] = array(
                    'id'   => $file_id, // MD5 hash.
                    'name' => $file['name'],
                    'file' => $file['file'],
                );
            }
        }

        return $downloads;
    }


    public function save_product_variations()
    {
        $this->id = mobassist_validate_post($this->id, 'product');

        if (!$this->id || empty($this->id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $the_product = wc_get_product($this->id);
        if (!$the_product->is_type('variable')) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_TYPE_IS_INCORRECT);
            return false;
        }

        $variation = $this->data;
        $this->save_variations($the_product, $variation);

        return $this->get_product_edit_variations();
    }


    private function save_variations($the_product, $data)
    {
        $menu_order = 0;
//        foreach($variations as $menu_order => $data) {
            $variation_id = isset($data['variation_id']) && $data['variation_id'] > 0 ? absint($data['variation_id']) : 0;

            if ($data['variation_id'] <= -1) { // new variation
                $variation_post = array(
                    'post_title'  => $the_product->get_name(),
                    'post_name'   => 'product-'.$the_product->get_id().'-variation',
                    'post_status' => 'publish',
                    'post_parent' => $the_product->get_id(),
                    'post_type'   => 'product_variation',
                    'guid'        => $the_product->get_permalink()
                );

                // Creating the product variation
                $variation_id = wp_insert_post($variation_post);
            }

            $variation = new WC_Product_Variation($variation_id);

            if (!$variation->get_slug()) {
                $variation->set_name(sprintf(__('Variation #%1$s of %2$s', 'woocommerce'), $variation->get_id(), $the_product->get_name()));
                if (isset($data['visible']) && !is_null($data['visible'])) {
                    $variation->set_status(false === $data['visible'] ? 'private' : 'publish');
                }
            }

            $variation->set_parent_id($the_product->get_id());
            $variation->set_menu_order($menu_order);

            if(isset($data['is_enabled']) && !is_null($data['is_enabled'])) $variation->set_status(false === $data['is_enabled'] ? 'private' : 'publish');

            if (isset($data['sku']) && !is_null($data['sku'])) {
                $variation->set_sku(wc_clean($data['sku']));
            } elseif (isset($data['sku']) && is_null($data['sku'])) {
                delete_post_meta($variation->get_id(), '_sku');
            }

            $imageInfo = array('image_id' => 0, "image_url" => "");
            if (!empty($_FILES)) {
                // array('image_id' => $new_id, "image_url" => $url);
                $imageInfo = self::storeImage($variation->get_id(), $this->_sanitize_files_array(), true);
                update_post_meta($variation->get_id(), '_thumbnail_id', $imageInfo['image_info']['image_id']);
                //$variation->set_image_id($imageInfo['image_id']);
            }

            if (isset($data['remove_image']) && !is_null($data['remove_image']) && $data['remove_image'] === true) {
                $variation->set_image_id('');
                $variation->set_gallery_image_ids(array());
                $imageInfo['image_url'] = '';
            }

            if(isset($data['is_virtual']) && !is_null($data['is_virtual'])) $variation->set_virtual($data['is_virtual']);

            if (isset($data['is_downloadable']) && !is_null($data['is_downloadable'])) {
                $is_downloadable = $data['is_downloadable'];
                $variation->set_downloadable($is_downloadable);
            } else {
                $is_downloadable = $variation->get_downloadable();
            }

            // todo
            if ($is_downloadable) {
                if(isset($data['downloads']) && !is_null($data['downloads']) && is_array($data['downloads'])) $variation = $this->save_downloadable_files($variation, $data['downloads']);
                if(isset($data['download_limit']) && !is_null($data['download_limit'])) $variation->set_download_limit($data['download_limit']);
                if(isset($data['download_expiry']) && !is_null($data['download_expiry'])) $variation->set_download_expiry($data['download_expiry']);
            }

            // dimensions
            $variation = $this->save_product_shipping_data($variation, $data);

            $manage_stock = (bool) $variation->get_manage_stock();
            if(isset($data['manage_stock']) && !is_null($data['manage_stock'])) $manage_stock = $data['manage_stock'];
            $variation->set_manage_stock($manage_stock);

            $stock_status = $variation->get_stock_status();
            if(isset($data['is_in_stock']) && !is_null($data['is_in_stock'])) $stock_status = true === $data['is_in_stock'] ? 'instock' : 'outofstock';
            $variation->set_stock_status($stock_status);

            $backorders = $variation->get_backorders();
            if(isset($data['backorders_allowed']) && !is_null($data['backorders_allowed'])) $backorders = $data['backorders_allowed'];
            $variation->set_backorders($backorders);

            if ($manage_stock) {
                if (isset($data['stock_quantity']) && !is_null($data['stock_quantity'])) {
                    $variation->set_stock_quantity($data['stock_quantity']);
                } elseif (isset($data['inventory_delta']) && !is_null($data['inventory_delta'])) {
                    $stock_quantity  = wc_stock_amount($variation->get_stock_quantity());
                    $stock_quantity += wc_stock_amount($data['inventory_delta']);
                    $variation->set_stock_quantity($stock_quantity);
                }
            } else {
                $variation->set_backorders('no');
                $variation->set_stock_quantity('');
            }

            /*if(isset($data['regular_price']) && !is_null($data['regular_price']))  $variation->set_regular_price($data['regular_price']);
            if(isset($data['sale_price']) && !is_null($data['sale_price'])) $variation->set_sale_price($data['sale_price']);*/
            $regular_price = isset($data['regular_price']) ? $data['regular_price'] : null;
            $sale_price = isset($data['sale_price']) ? $data['sale_price'] : null;
            if ($sale_price !== null && $regular_price !== null && (float)$regular_price < (float)$sale_price) {
                $sale_price = $regular_price;
            }

            update_post_meta($variation->get_id(), '_regular_price', $regular_price);
            update_post_meta($variation->get_id(), '_sale_price', $sale_price);
            if ((float)$sale_price > 0) {
                update_post_meta($variation->get_id(), '_price', $sale_price);
            } else {
                update_post_meta($variation->get_id(), '_price', $regular_price);
            }

            if(isset($data['variation_tax_class']) && !is_null($data['variation_tax_class'])) $variation->set_tax_class($data['variation_tax_class']);
            if(isset($data['variation_description']) && !is_null($data['variation_description'])) $variation->set_description(wp_kses_post($data['variation_description']));

            if (isset($data['sale_price_dates_from']) && !is_null($data['sale_price_dates_from'])) {
                $date_on_sale_from = wc_clean(wp_unslash($data['sale_price_dates_from']));
                if (!empty($date_on_sale_from)) {
                    $date_localised = $this->timestamp_localised('Y-m-d 00:00:00', strtotime($date_on_sale_from));
                    $variation->set_date_on_sale_from($date_localised);
                }
            }

            if (isset($data['sale_price_dates_to']) && !is_null($data['sale_price_dates_to'])) {
                $date_on_sale_to = wc_clean(wp_unslash($data['sale_price_dates_to']));
                if (!empty($date_on_sale_to)) {
                    $date_localised = $this->timestamp_localised('Y-m-d', strtotime($date_on_sale_to));
                    $variation->set_date_on_sale_to($date_localised);
                }
            }

            if (isset($data['variation_attributes_changed']) && !is_null($data['variation_attributes_changed']) && !empty($data['variation_attributes_changed'])) {
                $_attributes = array();
                $variation_attributes = $variation->get_variation_attributes();
                $submit_attributes = $data['variation_attributes_changed'];

                foreach ($variation_attributes as $name => $val) {
                    $_attribute_key = sanitize_title($name);

                    $attribute = wc_attribute_label(str_replace('attribute_', '', $name), $the_product);
                    $_attributes[$_attribute_key] = isset($submit_attributes[$attribute]) ? $submit_attributes[$attribute] : "";
                }

                $variation->set_attributes($_attributes);
            }

            $variation->save();

            return $imageInfo;
//        }
    }

    public function delete_product_variation()
    {
        $product_id = mobassist_validate_post($this->product_id, 'product');

        if (!$product_id || empty($product_id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $variation_id = mobassist_validate_post($this->id, 'product');
        if (!$variation_id || empty($variation_id)) {
            $this->generate_output_error(EM1Exception::ERROR_VARIATION_NOT_FOUND);
            return false;
        }

        if ('product_variation' === get_post_type($variation_id)) {
            $variation = wc_get_product($variation_id);
            $variation->delete(true);

            WC_Post_Data::delete_post($variation_id);
            wp_delete_post($variation_id, true);

            delete_transient('wc_product_children_' . $product_id);

            $the_product = wc_get_product($this->product_id);
            $variation_ids = $the_product->get_children();

            return array(
                'variations_count' => count($variation_ids)
            );
        }

        $this->generate_output_error(EM1Exception::ERROR_PRODUCT_TYPE_IS_INCORRECT);
    }


    public function generate_product_variations()
    {
        $post_id = mobassist_validate_post($this->id, 'product');

        if (!$post_id || empty($post_id)) {
            $this->generate_output_error(EM1Exception::ERROR_VARIATION_NOT_FOUND);
            return false;
        }

        $product = wc_get_product($post_id);
        $data_store = $product->get_data_store();

        if (!is_callable(array($data_store, 'create_all_product_variations'))) {
            wp_die();
        }

        wc_maybe_define_constant('WC_MAX_LINKED_VARIATIONS', 50);
        wc_set_time_limit(0);

        $generated_variations_count = $data_store->create_all_product_variations($product, WC_MAX_LINKED_VARIATIONS);

        $data_store->sort_all_product_variations($product->get_id());

        $variation_ids = $product->get_children();

        return array(
            'generated_variations_count' => $generated_variations_count,
            'variations_count' => count($variation_ids)
        );
    }


    private function update_attributes( &$product, $force = false )
    {
        $changes = $product->get_changes();

        if ($force || array_key_exists('attributes', $changes)) {
            $attributes             = $product->get_attributes();
            $updated_attribute_keys = array();
            foreach ( $attributes as $key => $value ) {
                update_post_meta($product->get_id(), 'attribute_' . $key, wp_slash($value));
                $updated_attribute_keys[] = 'attribute_' . $key;
            }
        }
    }

    private function timestamp_localised($format, $timestamp = null)
    {
        $tz_string = get_option('timezone_string');
        $tz_offset = get_option('gmt_offset', 0);

        if (!empty($tz_string)) {
            $timezone = $tz_string;

        } elseif ($tz_offset == 0) {
            $timezone = 'UTC';

        } else {
            $timezone = $tz_offset;

            if (substr($tz_offset, 0, 1) != "-" && substr($tz_offset, 0, 1) != "+" && substr($tz_offset, 0, 1) != "U") {
                $timezone = "+" . $tz_offset;
            }
        }

        if ($timestamp === null) {
            $timestamp = time();
        }

        $datetime = new DateTime();
        $datetime->setTimestamp($timestamp);
        $datetime->setTimezone(new DateTimeZone('UTC'));

        $dt = new DateTime($datetime->format($format), new DateTimezone($timezone));
        return $dt->getTimestamp();
    }

    private function save_product_images($product, $images)
    {
        if (is_array($images)) {
            $gallery = array();

            foreach ($images as $image) {
                if (isset($image['position']) && 0 == $image['position']) {
                    $attachment_id = isset($image['id']) ? absint($image['id']) : 0;

                    if (0 === $attachment_id && isset($image['src'])) {
//                        $upload = $this->upload_product_image( esc_url_raw( $image['src']));
                        $upload = WC()->api->WC_API_Products->upload_product_image(esc_url_raw($image['src']));

                        if (is_wp_error($upload)) {
                            throw new WC_API_Exception('woocommerce_api_cannot_upload_product_image', $upload->get_error_message(), 400);
                        }

                        $attachment_id = $this->set_uploaded_image_as_attachment($upload, $product->get_id());
                    }

                    $product->set_image_id($attachment_id);
                } else {
                    $attachment_id = isset($image['id']) ? absint($image['id']) : 0;

                    if (0 === $attachment_id && isset($image['src'])) {
                        $upload = $this->upload_product_image(esc_url_raw($image['src']));
                        $upload = WC()->api->WC_API_Products->upload_product_image(esc_url_raw($image['src']));

                        if (is_wp_error($upload)) {
                            throw new WC_API_Exception('woocommerce_api_cannot_upload_product_image', $upload->get_error_message(), 400);
                        }

                        $attachment_id = $this->set_uploaded_image_as_attachment($upload, $product->get_id());
                    }

                    $gallery[] = $attachment_id;
                }

                if (!empty($image['alt']) && $attachment_id) {
                    update_post_meta($attachment_id, '_wp_attachment_image_alt', wc_clean($image['alt']));
                }

                if (!empty($image['title']) && $attachment_id) {
                    wp_update_post(array( 'ID' => $attachment_id, 'post_title' => $image['title'] ));
                }
            }

            if (!empty($gallery)) {
                $product->set_gallery_image_ids($gallery);
            }
        } else {
            $product->set_image_id('');
            $product->set_gallery_image_ids(array());
        }

        return $product;
    }

    private function save_downloadable_files( $product, $downloads)
    {
        $files = array();
        foreach ( $downloads as $key => $file ) {
            if (isset($file['url'])) {
                $file['file'] = $file['url'];
            }

            if (empty($file['file'])) {
                continue;
            }

            $download = new WC_Product_Download();
            $download->set_id(! empty($file['id']) ? $file['id'] : wp_generate_uuid4());
            $download->set_name($file['name'] ? $file['name'] : wc_get_filename_from_url($file['file']));
            $download->set_file(apply_filters('woocommerce_file_download_path', $file['file'], $product, $key));
            $files[]  = $download;
        }
        $product->set_downloads($files);

        return $product;
    }

    private function save_product_shipping_data($product, $data)
    {
        if(isset($data['weight'])) $product->set_weight('' === $data['weight'] ? '' : wc_format_decimal($data['weight']));

        if (isset($data['dimensions'])) {
            if (isset($data['dimensions']['height'])) {
                $product->set_height('' === $data['dimensions']['height'] ? '' : wc_format_decimal($data['dimensions']['height']));
            }
            if (isset($data['dimensions']['width'])) {
                $product->set_width('' === $data['dimensions']['width'] ? '' : wc_format_decimal($data['dimensions']['width']));
            }
            if (isset($data['dimensions']['length'])) {
                $product->set_length('' === $data['dimensions']['length'] ? '' : wc_format_decimal($data['dimensions']['length']));
            }
        }

        if (isset($data['virtual'])) {
            $virtual = (true === $data['virtual']) ? 'yes' : 'no';

            if ('yes' == $virtual) {
                $product->set_weight('');
                $product->set_height('');
                $product->set_length('');
                $product->set_width('');
            }
        }

        if (isset($data['shipping_class'])) {
            $data_store = $product->get_data_store();
            $shipping_class_id = $data_store->get_shipping_class_id_by_slug(wc_clean($data['shipping_class']));
            $product->set_shipping_class_id($shipping_class_id);
        }

        return $product;
    }

    private function set_uploaded_image_as_attachment( $upload, $id = 0 )
    {
        $info    = wp_check_filetype($upload['file']);
        $title   = '';
        $content = '';

        if ($image_meta = @wp_read_image_metadata($upload['file'])) {
            if (trim($image_meta['title']) && ! is_numeric(sanitize_title($image_meta['title']))) {
                $title = wc_clean($image_meta['title']);
            }
            if (trim($image_meta['caption'])) {
                $content = wc_clean($image_meta['caption']);
            }
        }

        $attachment = array(
            'post_mime_type' => $info['type'],
            'guid'           => $upload['url'],
            'post_parent'    => $id,
            'post_title'     => $title,
            'post_content'   => $content,
        );

        $attachment_id = wp_insert_attachment($attachment, $upload['file'], $id);
        if (!is_wp_error($attachment_id)) {
            wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
        }

        return $attachment_id;
    }



    //-------------------------------------------------------------------------

    public function save_product()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        if (empty($this->product)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $product_data = $this->product;

        $error = self::saveProductData($product_data, false);
        if ($error === true) {
            $this->generate_output(array());
        }

        $this->generate_output_error(self::RESPONSE_CODE_ERROR, $error);
    }

    public function save_product_short_description()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        if (empty($this->data)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $product_data = $this->data;

        $error = self::saveProductDescr($product_data, true);
        if (!is_a($error, 'WP_Error')) {
            $this->generate_output(array());
        }

        $this->generate_output_error(self::RESPONSE_CODE_ERROR, $error);
    }

    public function save_product_description()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        if (empty($this->data)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $product_data = $this->data;

        $error = self::saveProductDescr($product_data, false);
        if (!is_a($error, 'WP_Error')) {
            $this->generate_output(array());
        }

        $this->generate_output_error(self::RESPONSE_CODE_ERROR, $error);
    }

    public function upload_product_image()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        if (empty($this->product_id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        if (!empty($_FILES)) {
            $store_res = self::storeImage($this->product_id, $this->_sanitize_files_array(), isset($this->changes['cover']) && $this->changes['cover']);

            if (isset($store_res['error'])) {
                $this->generate_output_error(self::RESPONSE_CODE_ERROR, $store_res['error']);
                return false;
            } else if (isset($store_res['image_info'])) {
                $this->generate_output($store_res['image_info']);
                return false;
            }
        }

        $this->generate_output_error(self::RESPONSE_CODE_ERROR, 'no_files');
    }

    public function get_product_edit_assigned_categories()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        if (empty($this->product_id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $product_categories = wp_get_post_terms($this->product_id, 'product_cat');
        $categories = array();
        foreach ($product_categories as $product_category) {
            $categoryId = (int)$product_category->term_id;
            $category = array(
                'category_id' => $categoryId,
                'name'        => $product_category->name,
                'path'        => $this->getCategoryPath($categoryId),
                'is_main'     => false // (bool)($categoryObject->id == $categoryDefault)
            );

            $categories[] = $category;
        }

        return array('categories' => $categories, 'categories_count' => count($product_categories));
    }

    public function get_product_edit_categories_to_assign()
    {
        $product_categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty'=>false));

        $categories = array();
        foreach ($product_categories as $categoryObject) {
            $category = array(
                'category_id' => (int)$categoryObject->term_id,
                'parent_category_id' => (int)$categoryObject->parent,
                'name' => $categoryObject->name,
                'path' => self::getCategoryPath($categoryObject->term_id),
                'position' => 1, // (int)$categoryObject->position,
                'is_root' => 0 == intval($categoryObject->parent)
            );

            $categories[] = $category;
        }

        return array('categories' => $categories);
    }


    public function update_assigned_product_categories()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        if (empty($this->product_id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $categories = $this->assigned_categories;
        if (empty($categories) && get_option('default_product_cat', 0)) {
            $categories = array( get_option('default_product_cat', 0) );
        }

        $classname = WC_Product_Factory::get_product_classname($this->product_id, $this->_get_product_type($this->product_id));
        $productWP = new $classname($this->product_id);
        $productWP->set_category_ids(array_column($categories, 'category_id'));
        $productWP->save();

        return self::get_product_edit_assigned_categories();
    }


    public function get_product_edit_downloadable_files()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        $this->id = mobassist_validate_post($this->id, 'product');

        if (!$this->id || empty($this->id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $downloadable_files = get_post_meta($this->id, '_downloadable_files', true);

        $files = array();
        foreach ($downloadable_files as $file) {
            $files[] = array(
                "product_id" => $this->id,
                "file_id" => $file["id"],
                "file_url" => $file["file"],
                "file_name" => $file["name"],
//                "description" => $file["name"] . "(" . $file_size . ")"
            );
        }
        return array('downloadable_files' => $files);
    }

    public function upload_product_attached_file()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        if (empty($this->data['product_id'])) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $product_data = $this->data;

        if (!empty($_FILES)) {
            $res = self::storeDownloadableFile($product_data, $this->_sanitize_files_array());
            if (isset($res['file_info'])) {
                $this->generate_output($res['file_info']);
            } else {
                $this->generate_output_error(self::RESPONSE_CODE_ERROR, $res['error']);
            }
        } else if (!empty($product_data)) {
            $product = new WC_Product((int)$product_data['product_id']);
            $downloads_array = $product->get_downloads();
            $downloads = array();

            $new_download = true;
            foreach ($downloads_array as $download_obj) {
                if ($download_obj->get_id() == wc_clean($product_data['file_id'])) {
                    if ($product_data['file_id'] != null) $download_obj->set_id(wc_clean($product_data['file_id']));
                    if ($product_data['file_name'] != null) $download_obj->set_name(wp_unslash(trim($product_data['file_name'])));
                    if ($product_data['file_url'] != null) $download_obj->set_file(wc_clean($product_data['file_url']));
                    $download_object = $download_obj;
                    $new_download = false;
                }
                $downloads[] = array(
                    'name'        => wc_clean($download_obj->get_name()),
                    'file'        => wp_unslash(trim($download_obj->get_file())),
                    'download_id' => wc_clean($download_obj->get_id()),
                );
            }

            if ($new_download) {
                $download_object = new WC_Product_Download();
                if ($product_data['file_id'] != null) $download_object->set_id(wc_clean($product_data['file_id']));
                if ($product_data['file_name'] != null) $download_object->set_name(wp_unslash(trim($product_data['file_name'])));
                if ($product_data['file_url'] != null) $download_object->set_file(wc_clean($product_data['file_url']));
                $downloads[] = array(
                    'name'        => wc_clean($download_object->get_name()),
                    'file'        => wp_unslash(trim($download_object->get_file())),
                    'download_id' => wc_clean($download_object->get_id()),
                );
            }

            $product->set_props(array('downloads' => $downloads ));
            $product->save();

            return array('file_info' => array("file_id" => $product_data['file_id'], "mime" => $download_object->get_file_type()));
        }

        $this->generate_output_error(self::RESPONSE_CODE_ERROR, "no_data");
    }


    public function update_product_attached_file()
    {
        return $this->upload_product_attached_file()['file_info'];
    }


    public function delete_product_edit_downloadable_file()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        if (empty($this->product_id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $product = new WC_Product((int)$this->product_id);
        $downloads_array = $product->get_downloads();

        $new_downloads_array = array();
        foreach ($downloads_array as $download_obj) {
            if ($download_obj->get_id() != wc_clean($this->file_id)) {
                $new_downloads_array[] = $download_obj;
            }
        }

        $product->set_downloads($new_downloads_array);
        $product->save();

        return array();
    }

    public function get_new_product_data()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            return array('error' => 'woocommerce_version_less_3');
        }

        $post_id = wp_insert_post(array('post_title' => __('Auto Draft'), 'post_type' => 'product', 'post_status' => 'auto-draft'));

        if ($post_id > 0) {
            wc_get_product($post_id);

            $product_data = array(
                'product_id' => $post_id,
                'currency_symbol' => get_woocommerce_currency_symbol(),
                'product_types' => wc_get_product_types(),
                'product_stock_statuses' => json_encode(wc_get_product_stock_status_options()),
                'backorder_options' => json_encode(wc_get_product_backorder_options()),
                'product_statuses' => json_encode(self::getProductStatuses()),
                'catalog_visibilities' => json_encode(wc_get_product_visibility_options()),
                'tax_statuses' => json_encode(self::getTaxStatuses()),
                'tax_classes' => json_encode($this->getTaxClasses()),
                'max_file_upload_size' => self::getMaxFileUploadInBytes()
            );

            $new_product_data = array('product_id' => $post_id, 'product_type' => 'simple');
            if (self::saveProductData($new_product_data, true)) {
                return $product_data;
            }
        }

        $this->generate_output_error(EM1Exception::ERROR_CODE_UNKNOWN);
    }


    public function delete_product()
    {
        if (version_compare(WooCommerce::instance()->version, '3.0', '<')) {
            $this->generate_output_error('woocommerce_version_less_3');
            return false;
        }

        $this->product_id = mobassist_validate_post($this->id, 'product');

        if (!$this->product_id || empty($this->product_id)) {
            $this->generate_output_error(EM1Exception::ERROR_PRODUCT_NOT_FOUND);
            return false;
        }

        $product = wc_get_product($this->product_id);

        $product->delete();
        $result = 'trash' === $product->get_status();

        if ($result) {
            $this->generate_output(array());
            return true;
        }

        $this->generate_output_error(ERROR_CODE_UNKNOWN, sprintf(__('This %s cannot be deleted', 'woocommerce'), 'product'));
    }


//-----------------------------

    private static function _string_to_bool( $string )
    {
        return is_bool($string) ? $string : ( 'yes' === strtolower($string) || 1 === $string || 'true' === strtolower($string) || '1' === $string );
    }

    private function getCategoryPath($categoryId)
    {
        $category = get_term($categoryId, 'product_cat');
        $categoryPath = $category->name;
        while (0 !== intval($category->parent)) {
            $category = get_term($category->parent, 'product_cat');
            $categoryPath = $category->name . '>' . $categoryPath;
        }
        return $categoryPath;
    }

    private function getProductVisibilities()
    {
        $vals = array(
            'private'  => __('Private'),
            'password' => __('Password protected'),
            'public'   => __('Public'),
        );
        return $vals;

        if ('private' == $post->post_status) {
            $post->post_password = '';
            $visibility          = 'private';
            $visibility_trans    = __('Privately Published');
        } elseif (! empty($post->post_password)) {
            $visibility       = 'password';
            $visibility_trans = __('Password protected');
        } elseif ($post_type == 'post' && is_sticky($post->ID)) {
            $visibility       = 'public';
            $visibility_trans = __('Public, Sticky');
        } else {
            $visibility       = 'public';
            $visibility_trans = __('Public');
        }
    }

    private function getProductVisibility($post)
    {
        if ('private' == $post->post_status) {
            $visibility          = 'private';
//            $visibility_trans    = __( 'Private' );
        } elseif (! empty($post->post_password)) {
            $visibility       = 'password';
//            $visibility_trans = __( 'Password protected' );
        } else {
            $visibility       = 'public';
//            $visibility_trans = __( 'Public' );
        }

        return $visibility;
    }

    private function getTaxStatusTitle($wc_product)
    {
        $tax_status = $wc_product->get_tax_status();
        $tax_status_arr = array(
            'taxable'  => __('Taxable', 'woocommerce'),
            'shipping' => __('Shipping only', 'woocommerce'),
            'none'     => _x('None', 'Tax status', 'woocommerce'),
        );
        if(array_key_exists($tax_status, $tax_status_arr)) $tax_status = $tax_status_arr[$tax_status];
        if(empty($tax_status)) $tax_status = $tax_status_arr['none'];

        return $tax_status;
    }

    private function getTaxClassTitle($wc_product)
    {
        $tax_class = $wc_product->get_tax_class('edit');
        $tax_class_options = $this->getTaxClasses();
        $tax_class = $tax_class_options[$tax_class];

        return $tax_class;
    }

    private function getTaxClasses()
    {
        $tax_classes = WC_Tax::get_tax_classes();
        $tax_class_options = array();
        $tax_class_options[''] = __('Standard', 'woocommerce');

        if (! empty($tax_classes)) {
            foreach ( $tax_classes as $class ) {
                $tax_class_options[ sanitize_title($class) ] = $class;
            }
        }
        return $tax_class_options;
    }

    private function getTaxStatuses()
    {
        $tax_statuses = array(
            'test_status'  => "Test status",
            'taxable'  => __('Taxable', 'woocommerce'),
            'shipping' => __('Shipping only', 'woocommerce'),
            'none'     => _x('None', 'Tax status', 'woocommerce'),
        );

        return $tax_statuses;
    }

    private function getAllowBackordersTitle($allow_backorders_code)
    {
        $product_backorder_options = array(
            'no'     => __('Do not allow', 'woocommerce'),
            'notify' => __('Allow, but notify customer', 'woocommerce'),
            'yes'    => __('Allow', 'woocommerce'),
        );
//        $allow_backorders = $product['allow_backorders'];
        $allow_backorders = $allow_backorders_code;
        if(array_key_exists($allow_backorders, $product_backorder_options)) $allow_backorders = $product_backorder_options[$allow_backorders];

        return $allow_backorders;
    }

    private function getStoreStatisticsData()
    {
        return array(
            self::KEY_ORDERS    => $this->get_orders_store_statistic(),
            self::KEY_PRODUCTS  => $this->get_products_store_statistic(),
            self::KEY_CUSTOMERS => $this->get_customers_store_statistic(),
        );
    }

    private function init_dates($dateFrom, $dateTo)
    {
        global $wpdb;

        // Prepare dates before using
        if (!empty($dateFrom) && !empty($dateTo) && $dateFrom !== -1 && $dateTo !== -1) {
            $this->dateFrom = self::convertMillisecondsTimestampToTimestamp($dateFrom);
            $this->dateTo   = self::convertMillisecondsTimestampToTimestamp($dateTo);
        }

        if ($dateTo === -1 || $dateFrom === -1) {
            $dateRange = "SELECT MIN(dateRange.min_date) AS `date_from`, MAX(dateRange.max_date) AS `date_to` FROM (
                 SELECT MIN(post_date_gmt) AS min_date, MAX(post_date_gmt) AS max_date FROM `{$wpdb->posts}` WHERE post_type = 'shop_order'";

            if (!empty($this->status_list_hide)) {
                $dateRange .= " AND post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
            }

            $dateRange .= " UNION ALL ";

            $dateRange .= "SELECT MIN(c.user_registered) AS `date_from`, MAX(c.user_registered) AS `date_to` FROM `{$wpdb->users}` AS c
                      LEFT JOIN `{$wpdb->usermeta}` AS usermeta ON usermeta.user_id = c.ID
                      WHERE (usermeta.meta_key = '{$wpdb->prefix}capabilities' AND usermeta.meta_value LIKE '%customer%')";

            $dateRange .= ") AS dateRange";

            $res = $wpdb->get_results($dateRange, ARRAY_A);
            $res = array_shift($res);

            if (empty($this->dateFrom) && !empty($res)) {
                $this->dateFrom = strtotime($res[self::KEY_DATE_FROM]);
            }

            if (empty($this->dateTo) && !empty($res)) {
                $this->dateTo = strtotime($res[self::KEY_DATE_TO]);
            }
        }
    }

    private function get_orders_store_statistic()
    {
        global $wpdb;
        $query_where_parts = array();

        $query_orders = "SELECT
              COUNT(posts.ID) AS count_orders,
              SUM(meta_order_total.meta_value) AS total_sales
            FROM `{$wpdb->posts}` AS posts
            LEFT JOIN `{$wpdb->postmeta}` AS meta_order_total ON meta_order_total.post_id = posts.ID AND meta_order_total.meta_key = '_order_total'";

        if (isset($this->show_all_customers) && !$this->show_all_customers) {
            $query_for_registered_customers = " LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts.ID = meta.post_id AND meta.meta_key = '_customer_user' 
                               LEFT JOIN `{$wpdb->users}` AS c ON c.ID = meta.meta_value
                               LEFT JOIN `{$wpdb->usermeta}` AS cap ON cap.user_id = c.ID ";
            $query_orders .= $query_for_registered_customers;
            $query_where_parts[] = " (cap.meta_key = '{$wpdb->prefix}capabilities' AND cap.meta_value LIKE '%customer%') ";
        }

        $query_where_parts[] = " posts.post_type = 'shop_order' ";

        if ($this->dateFrom !== $this->dateTo) {
            $query_where_parts[] = sprintf(" UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) >= '%d'", $this->dateFrom);
            $query_where_parts[] = sprintf(" UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) <= '%d'", $this->dateTo);
        }

        if (!empty($this->order_statuses)) {
            $query_where_parts[] = sprintf(" posts.post_status IN ('%s')", implode("', '", $this->order_statuses));
        }

        if (!empty($this->status_list_hide)) {
            $query_where_parts[] = " posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        if (!empty($query_where_parts)) {
            $query_orders .= ' WHERE ' . implode(' AND ', $query_where_parts);
        }

        $orders_stat = $wpdb->get_results($query_orders, ARRAY_A);
        $orders_stat = array_shift($orders_stat);

        return array('orders_count' => $orders_stat['count_orders'], 'orders_total' => $orders_stat['total_sales']);
    }

    private function get_products_store_statistic()
    {
        global $wpdb;
        $query_where_parts = array();

        $query_products = "SELECT
              SUM(meta_items_qty.meta_value) AS count_products
            FROM `{$wpdb->posts}` AS posts
            LEFT JOIN `{$wpdb->prefix}woocommerce_order_items` AS order_items ON order_items.order_id = posts.ID AND order_items.order_item_type = 'line_item'
            LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS meta_items_qty ON meta_items_qty.order_item_id = order_items.order_item_id AND meta_items_qty.meta_key = '_qty'";

        if (isset($this->show_all_customers) && !$this->show_all_customers) {
            $query_for_registered_customers = " LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts.ID = meta.post_id AND meta.meta_key = '_customer_user' 
                               LEFT JOIN `{$wpdb->users}` AS c ON c.ID = meta.meta_value
                               LEFT JOIN `{$wpdb->usermeta}` AS cap ON cap.user_id = c.ID ";
            $query_products .= $query_for_registered_customers;
            $query_where_parts[] = " (cap.meta_key = '{$wpdb->prefix}capabilities' AND cap.meta_value LIKE '%customer%') ";
        }

        if (!function_exists('wc_get_order_status_name')) {
            $query = " LEFT JOIN `{$wpdb->term_relationships}` AS order_status_terms ON order_status_terms.object_id = posts.ID
                            AND order_status_terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM `{$wpdb->term_taxonomy}` WHERE taxonomy = 'shop_order_status')
                        LEFT JOIN `{$wpdb->terms}` AS status_terms ON status_terms.term_id = order_status_terms.term_taxonomy_id";
            $query_products .= $query;
        }

        $query_where_parts[] = " posts.post_type = 'shop_order' ";

        if ($this->dateFrom !== $this->dateTo) {
            $query_where_parts[] = sprintf(" UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) >= '%d'", $this->dateFrom);
            $query_where_parts[] = sprintf(" UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) <= '%d'", $this->dateTo);
        }

        if (!empty($this->order_statuses)) {
            if (function_exists('wc_get_order_status_name')) {
                $query_where_parts[] = sprintf(" posts.post_status IN ('%s')", implode("', '", $this->order_statuses));
            } else {
                $query_where_parts[] = sprintf(" status_terms.slug IN ('%s')", implode("', '", $this->order_statuses));
            }
        }

        if (!empty($this->status_list_hide)) {
            $query_where_parts[] = " posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        if (!empty($query_where_parts)) {
            $query_products .= ' WHERE ' . implode(' AND ', $query_where_parts);
        }

        $products_stat = $wpdb->get_results($query_products, ARRAY_A);
        $products_stat = array_shift($products_stat);

        return array('products_count' => (int)$products_stat['count_products']);
    }

    private function get_customers_store_statistic()
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

            if (!empty($this->order_statuses)) {
                if (function_exists('wc_get_order_status_name')) {
                    $query_post_status_in = sprintf(
                        " AND posts.post_status IN ('%s')",
                        implode("', '", $this->order_statuses)
                    );
                } else {
                    $query_post_status_in = sprintf(
                        " AND status_terms.slug IN ('%s')",
                        implode("', '", $this->order_statuses)
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
                        WHERE posts.post_type = 'shop_order' AND meta_customer.meta_value = 0";

                if (!empty($query_post_status_not)) {
                    $query_not_register .= $query_post_status_not;
                }

                if ($this->cust_with_orders && empty($this->order_statuses)) {
                    $query_not_register .= ' AND posts.ID IS NULL ';
                }

                if (!empty($query_post_status_in)) {
                    $query_not_register .= $query_post_status_in;
                }

                if ($this->dateFrom !== $this->dateTo) {
                    $query_not_register .= sprintf(" AND UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) >= '%d'", $this->dateFrom);
                    $query_not_register .= sprintf(" AND UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date, '+00:00', @@global.time_zone)) <= '%d'", $this->dateTo);
                }
            }
        }

        $query .= " WHERE (usermeta.meta_key = '{$wpdb->prefix}capabilities' AND usermeta.meta_value LIKE '%customer%')";

        if ($this->cust_with_orders && empty($this->order_statuses) && isset($this->show_all_customers)) {
            $query .= ' AND tot.ID is NULL ';
        }

        if ($this->dateFrom !== $this->dateTo) {
            $query .= sprintf(" AND UNIX_TIMESTAMP(CONVERT_TZ(c.user_registered, '+00:00', @@global.time_zone)) >= '%d'", $this->dateFrom);
            $query .= sprintf(" AND UNIX_TIMESTAMP(CONVERT_TZ(c.user_registered, '+00:00', @@global.time_zone)) <= '%d'", $this->dateTo);
        }

        if ((!empty($this->cust_with_orders) && !isset($this->show_all_customers))
            || (isset($this->show_all_customers) && !$this->cust_with_orders)
        ) {
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
        return array('customers_count' => $total_count);
    }

    private function getGraphsData() // todo add $this->shopId
    {
        global $wpdb;

        $startDate  = !empty($this->dateFrom) ? $this->dateFrom : time();
        $endDate    = !empty($this->dateTo) ? $this->dateTo : time();

        $startDateFormatted = date(self::GLOBAL_DATE_FORMAT, $startDate);
        $endDateFormatted   = date(self::GLOBAL_DATE_FORMAT, $endDate);

        $groupingByDay = false;
        if ($this->dashboardGrouping !== self::GROUP_BY_HOUR) {
            $groupingByDay = true;
        }

        $query_orders = "SELECT " .
            ($groupingByDay ? "UNIX_TIMESTAMP(TIMESTAMP(DATE(posts.`post_date_gmt`)))"
                : "UNIX_TIMESTAMP(CONCAT(DATE(posts.`post_date_gmt`), ' ', HOUR(CONVERT_TZ(posts.`post_date_gmt`, '+00:00', @@global.time_zone)), ':00:00'))") .
            " AS orders_date,
              COUNT(posts.ID) AS orders_count,
              SUM(meta_order_total.meta_value) AS orders_total
            FROM `{$wpdb->posts}` AS posts
            LEFT JOIN `{$wpdb->postmeta}` AS meta_order_total ON meta_order_total.post_id = posts.ID AND meta_order_total.meta_key = '_order_total'
            WHERE posts.post_type = 'shop_order'"
            . sprintf(" AND posts.post_date_gmt >= '%s'", $startDateFormatted)
            . sprintf(" AND posts.post_date_gmt <= '%s'", $endDateFormatted);

        if (!empty($this->order_statuses)) $query_orders .= sprintf(" AND posts.post_status IN ('%s')", implode("', '", $this->order_statuses));
        $query_orders .= ($groupingByDay ? ' GROUP BY DATE(posts.post_date_gmt)' : ' GROUP BY posts.post_date_gmt') . ' ORDER BY posts.post_date_gmt';
        $orders_stat = $wpdb->get_results($query_orders, ARRAY_A);

        $query_customers = "SELECT "
            . ($groupingByDay ? 'UNIX_TIMESTAMP(TIMESTAMP(DATE(c.user_registered)))'
                : "UNIX_TIMESTAMP(CONCAT(DATE(c.user_registered), ' ', HOUR(c.user_registered), ':00:00'))") .
            " AS customers_date, 
              COUNT(c.ID ) AS customers_count
            FROM `{$wpdb->users}` AS c 
            LEFT JOIN `{$wpdb->usermeta}` AS usermeta ON usermeta.user_id = c.ID 
            WHERE (usermeta.meta_key = '{$wpdb->prefix}capabilities' AND usermeta.meta_value LIKE '%customer%')"
            . sprintf(" AND c.user_registered >= '%s'", $startDateFormatted)
            . sprintf(" AND c.user_registered <= '%s'", $endDateFormatted)
            . ($groupingByDay ? " GROUP BY DATE(c.user_registered)" : " GROUP BY c.user_registered")
            . " ORDER BY c.user_registered";

        $customers_stat = $wpdb->get_results($query_customers, ARRAY_A);

        return $this->getGraphsValues($orders_stat, $customers_stat);
    }

    private function getGraphsValues($orders, $customers)
    {
        if (empty($orders) && empty($customers)) {
            $this->graphResponse();
        }

        if((!empty($orders) && !empty($customers)) || (empty($orders) && empty($customers))){
            try {
                $orderMinDate = $this->getDateTimeTimestamp(reset($orders)[self::KEY_ORDERS_DATE]);
                $orderMaxDate = $this->getDateTimeTimestamp(end($orders)[self::KEY_ORDERS_DATE]);

                $customerMinDate = $this->getDateTimeTimestamp(reset($customers)[self::KEY_CUSTOMERS_DATE]);
                $customerMaxDate = $this->getDateTimeTimestamp(end($customers)[self::KEY_CUSTOMERS_DATE]);

                $minDate = $orderMinDate > $customerMinDate ? $customerMinDate : $orderMinDate;
                $maxDate = $orderMaxDate > $customerMaxDate ? $orderMaxDate : $customerMaxDate;
            } catch (Exception $e) {
                throw new EM1Exception(
                    EM1Exception::ERROR_CODE_COULD_NOT_CREATE_DATETIME_OBJECT,
                    $e->getMessage()
                );
            }
        } elseif (!empty($orders)) {
            try {
                $minDate = $this->getDateTimeTimestamp(reset($orders)[self::KEY_ORDERS_DATE]);
                $maxDate = $this->getDateTimeTimestamp(end($orders)[self::KEY_ORDERS_DATE]);

            } catch (Exception $e) {
                throw new EM1Exception(
                    EM1Exception::ERROR_CODE_COULD_NOT_CREATE_DATETIME_OBJECT,
                    $e->getMessage()
                );
            }
        } elseif (!empty($customers)) {
            try {
                $minDate = $this->getDateTimeTimestamp(reset($customers)[self::KEY_CUSTOMERS_DATE]);
                $maxDate = $this->getDateTimeTimestamp(end($customers)[self::KEY_CUSTOMERS_DATE]);
            } catch (Exception $e) {
                throw new EM1Exception(
                    EM1Exception::ERROR_CODE_COULD_NOT_CREATE_DATETIME_OBJECT,
                    $e->getMessage()
                );
            }
        }

        if (empty($minDate) && $this->dateFrom > 0) {
            try {
                $minDate = $this->getDateTimeTimestamp($this->dateFrom);
            } catch (Exception $e) {
                throw new EM1Exception(
                    EM1Exception::ERROR_CODE_COULD_NOT_CREATE_DATETIME_OBJECT,
                    $e->getMessage()
                );
            }
        }

        $period = 0;
        $sumOrdersTotal = 0;
        $sumOrdersCount = 0;
        $sumCustomersCount = 0;
        $ordersResult = array();
        $newMaxDate = $maxDate;
        for ($timestamp = $minDate, $dateTime = new DateTime(),
             $dateInterval = new DateInterval(self::INTERVAL[$this->dashboardGrouping]);
             $timestamp <= $maxDate;
             $timestamp = $dateTime->setTimestamp($timestamp)->add($dateInterval)->getTimestamp()) {
            $newMaxDate = $dateTime->setTimestamp($timestamp)->add($dateInterval)->getTimestamp();
        }

        $maxDate = $newMaxDate;
        try {
            for ($timestamp = $minDate, $dateTime = new DateTime(),
                 $dateInterval = new DateInterval(self::INTERVAL[$this->dashboardGrouping]);
                 $timestamp <= $maxDate;
                 $timestamp = $dateTime->setTimestamp($timestamp)->add($dateInterval)->getTimestamp()) {
                $ordersTotal = 0;
                $ordersCount = 0;
                $customerCount = 0;

                foreach ($orders as $orderValue) {
                    if ($this->compareDatesByGrouping($timestamp, (int)$orderValue[self::KEY_ORDERS_DATE])) {
                        $ordersTotal += (float)$orderValue[self::KEY_ORDERS_TOTAL];
                        $ordersCount += (int)$orderValue[self::KEY_ORDERS_COUNT];
                        continue;
                    }
                }

                foreach ($customers as $customerValue) {
                    if ($this->compareDatesByGrouping($timestamp, (int)$customerValue[self::KEY_CUSTOMERS_DATE])) {
                        $customerCount += (int)$customerValue[self::KEY_CUSTOMERS_COUNT];
                        continue;
                    }
                }

                $ordersResult[] = array(
                    self::KEY_TIMESTAMP         => self::convertTimestampToMillisecondsTimestamp($timestamp),
                    self::KEY_ORDERS_TOTAL      => (float)$ordersTotal,
                    self::KEY_ORDERS_COUNT      => $ordersCount,
                    self::KEY_CUSTOMERS_COUNT   => $customerCount
                );

                $period++;
                $sumOrdersTotal += (float)$ordersTotal;
                $sumOrdersCount += $ordersCount;
                $sumCustomersCount += $customerCount;
            }
        } catch (Exception $e) {
            throw new EM1Exception(EM1Exception::ERROR_CODE_QUERY_EXECUTION_ERROR, $e->getMessage());
        }

        $averageOrdersTotal     = 0;
        $averageOrdersCount     = 0;
        $averageCustomersCount  = 0;
        if ($period > 0) {
            $averageOrdersTotal = (float)($sumOrdersTotal / $period);
            $averageOrdersCount = (float)($sumOrdersCount / $period);
            $averageCustomersCount = (float)($sumCustomersCount / $period);
        }
        $averageOrdersTotalPerCustomer = ($sumOrdersTotal > 0 && $sumCustomersCount > 0)
            ? $sumOrdersTotal / $sumCustomersCount
            : 0;

        $average = array(
            self::KEY_ORDERS_TOTAL              => mobassist_nice_price($averageOrdersTotal, false, false, false, true),
            self::KEY_ORDERS_COUNT              => $this->round($averageOrdersCount, 2),
            self::KEY_CUSTOMERS_COUNT           => $this->round($averageCustomersCount, 2),
            self::KEY_ORDERS_TOTAL_PER_CUSTOMER => mobassist_nice_price($averageOrdersTotalPerCustomer, false, false, false, true)
        );

        $total = array(
            self::KEY_ORDERS_TOTAL => mobassist_nice_price((float)$sumOrdersTotal, false, false, false, true),
            self::KEY_ORDERS_COUNT => $sumOrdersCount,
            self::KEY_PRODUCTS_COUNT => $this->get_products_store_statistic()['products_count'],
            self::KEY_CUSTOMERS_COUNT => $sumCustomersCount
        );

        return $this->graphResponse($ordersResult, $average, $total);
    }

    private function graphResponse($graphData = array(), $average = array(), $total = array())
    {
        $currency = get_woocommerce_currency();
        $currency_symbol = html_entity_decode(get_woocommerce_currency_symbol($currency));

        return array(
            self::KEY_GRAPH_DATA        => $graphData,
            self::KEY_AVERAGE           => $average,
            self::KEY_TOTAL             => $total,
            self::KEY_GROUP_BY          => self::GROUP_BY[$this->dashboardGrouping],
            self::KEY_CURRENCY_SYMBOL   => $currency_symbol
        );
    }

    private function getStatusStatistic()
    {
        $orderStatusesReturn = array();
        $statuses = mobassist_get_order_statuses();
        $statusStatistic = $this->getStatusStatisticData();

        foreach ($statusStatistic as $orderStatus) {
            $orderStateId = $orderStatus['id'];
            $statusName = '';
            foreach ($statuses as $code => $name) {
                if ($code == $orderStateId) {
                    $statusName = (string)$name;
                    break;
                }
            }

            $orderStatusesReturn[] = array(
                self::KEY_ID                        => $orderStateId,
                self::KEY_TITLE                     => $statusName,
                self::KEY_FORMATTED_ORDERS_TOTAL    => mobassist_nice_price((float)$orderStatus['total'], false, false, false, true),
                self::KEY_ORDERS_COUNT              => (int)$orderStatus['count'],
            );
        }

        return array(self::KEY_ORDER_STATUSES_STATISTICS => $orderStatusesReturn);
    }

    private function getStatusStatisticData()
    {
        global $wpdb;

        $query_orders = "SELECT
              posts.post_status AS `id`,
              COUNT(posts.ID) AS count,
              SUM(meta_order_total.meta_value) AS total
            FROM `{$wpdb->posts}` AS posts
            LEFT JOIN `{$wpdb->postmeta}` AS meta_order_total ON meta_order_total.post_id = posts.ID AND meta_order_total.meta_key = '_order_total'
            WHERE posts.post_type = 'shop_order'";

        if ($this->dateFrom !== $this->dateTo) {
            $query_orders .= sprintf(" AND UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date_gmt, '+00:00', @@session.time_zone)) >= '%d'", $this->dateFrom);
            $query_orders .= sprintf(" AND UNIX_TIMESTAMP(CONVERT_TZ(posts.post_date_gmt, '+00:00', @@session.time_zone)) <= '%d'", $this->dateTo);
        }

        if (!empty($this->order_statuses)) {
            $query_orders .= sprintf(" AND posts.post_status IN ('%s')", implode("', '", $this->order_statuses));
        }

        if (!empty($this->status_list_hide)) {
            $query_orders .= " AND posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        $query_orders .= " GROUP BY posts.post_status
             ORDER BY count DESC";

        return $wpdb->get_results($query_orders, ARRAY_A);
    }

    private function getDateTimeTimestamp($timestamp)
    {
        try {
            $dateTime = new DateTime();
            $dateTimeTimestamp = $dateTime->setTimestamp($timestamp)->getTimestamp();
        } catch (Exception $e) {
            throw new EM1Exception(
                EM1Exception::ERROR_CODE_COULD_NOT_CREATE_DATETIME_OBJECT,
                $e->getMessage()
            );
        }

        return $dateTimeTimestamp;
    }

    private function compareDatesByGrouping($timestamp, $comparedTimestamp)
    {
        switch ($this->dashboardGrouping) {
            case self::GROUP_BY_HOUR:
                return (
                    date('H', $timestamp) === date('H', $comparedTimestamp)
                    && date('d', $timestamp) === date('d', $comparedTimestamp)
                    && date('m', $timestamp) === date('m', $comparedTimestamp)
                    && date('Y', $timestamp) === date('Y', $comparedTimestamp)
                );
            case self::GROUP_BY_DAY:
                return $timestamp === $comparedTimestamp;
            case self::GROUP_BY_WEEK:
                return (
                    date('W', $timestamp) === date('W', $comparedTimestamp)
                    && date('Y', $timestamp) === date('Y', $comparedTimestamp)
                );
            case self::GROUP_BY_MONTH:
                return (
                    date('m', $timestamp) === date('m', $comparedTimestamp)
                    && date('Y', $timestamp) === date('Y', $comparedTimestamp)
                );
            default:
                return false;
        }
    }

    private function get_version()
    {
        return array(
            self::KEY_MODULE_VERSION    => self::PLUGIN_CODE,
            self::KEY_CART_VERSION      => WooCommerce::instance()->version
        );
    }

    private function get_currencies()
    {
        $all_currencies = array();

        $currency_code_options = get_woocommerce_currencies();
        $base_currency_code = get_woocommerce_currency();

        foreach ($currency_code_options as $code => $name) {
            $all_currencies[] = array(
                'code' => $code,
                'name' => $name,
                'symbol' => get_woocommerce_currency_symbol($code),
                'is_default_for_all_shops' => ($code == $base_currency_code ? 'true' : 'false'),
            );
        }

        return array('currencies' => $all_currencies);
    }

    private function get_carriers()
    {
        $carriersResponse = array();
        $shipping_methods = WC()->shipping() ? WC()->shipping()->load_shipping_methods() : array();

        foreach ($shipping_methods as $code => $method) {
            $carriersResponse[] = array(
                'carrier_id'  => $method->id,
                'name'        => $method->method_title,
                'status'      => $method->is_enabled()
            );
        }

        return array('carriers' => $carriersResponse);
    }

    private function get_store_title()
    {
        $title = get_option('blogname');

        return array('store_title' => $title);
    }

    private function _get_token()
    {
        if ($this->hash) {
            $user_data = Mobassistantconnector_Access::check_auth($this->hash);
            if ($user_data) {
                if ($this->token) {
                    if (Mobassistantconnector_Access::check_session_key($this->token, $user_data['user_id'])) {
                        $token = $this->token;
                    } else {
                        $token = Mobassistantconnector_Access::get_session_key($this->hash, $user_data['user_id']);
                    }
                } else {
                    $token = Mobassistantconnector_Access::get_session_key($this->hash, $user_data['user_id']);
                }

                if ($token && Mobassistantconnector_Access::check_session_key($token)) {
                    return array(self::KEY_TOKEN => $token);
                }
            }
        }

        $this->generate_output_error(self::RESPONSE_CODE_AUTH_ERROR);
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
            $temp_note = array(
                'note_id' => (int)$note['id'],
                'date' => self::convertTimestampToMillisecondsTimestamp((int)strtotime($note['created_at'])),
                'note' => html_entity_decode($note['note']),
                'note_type' => $note['note_type']);

            $notes[] = $temp_note;
        }

        return $notes;
    }

    private function _get_order_note($order_id, $id, $fields = null)
    {
        $id = absint($id);

        if (empty($id)) {
            return new WP_Error(
                'woocommerce_api_invalid_order_note_id',
                __('Invalid order note ID', 'mobile-assistant-connector'), array('status' => 400)
            );
        }

        $note = get_comment($id);

        if (is_null($note)) {
            return new WP_Error(
                'woocommerce_api_invalid_order_note_id',
                __('An order note with the provided ID could not be found', 'mobile-assistant-connector'),
                array('status' => 404)
            );
        }

        $order_note = array(
            'id' => $note->comment_ID,
            'created_at' => $this->_parse_datetime($note->comment_date_gmt),
            'note' => $note->comment_content,
            'customer_note' => get_comment_meta($note->comment_ID, 'is_customer_note', true) ? true : false,
        );

        if (get_comment_meta($note->comment_ID, 'is_customer_note', true)) $order_note['note_type'] = "customer";
        else if (__('WooCommerce', 'woocommerce') === $note->comment_author) $order_note['note_type'] = "system";
        else $order_note['note_type'] = "private";

        return array(
            'order_note' => apply_filters(
                'woocommerce_api_order_note_response', $order_note, $id, $fields, $note,
                $order_id, $this
            )
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
            $type = 'Grouped';

        } elseif ('external' == $product_type) {
            $type = 'External/Affiliate';

        } elseif ('simple' == $product_type) {
            if ($the_product->is_virtual() && $the_product->is_downloadable()) {
                $type = 'Virtual&Downloadable';

            } else if ($the_product->is_virtual()) {
                $type = 'Virtual';

            } elseif ($the_product->is_downloadable()) {
                $type = 'Downloadable';

            } else {
                $type = 'Simple';
            }

        } elseif ('variable' == $product_type) {
            $type = 'Variable';

        } else {
            $type = ucfirst($product_type);
        }

        return $type;
    }

    private function _get_order_products($order_id, $order_items, $currency_code)
    {
        $order_products = array();
        $order_item_loop_counter = 0;
        $order_item_count = count($order_items);

        $order = new WC_Order($order_id);

        if ($order_item_count > (($this->page_index - 1) * $this->page_size)) {
            foreach ($order_items as $item_id => $item) {
                $order_item_loop_counter++;
                if ($order_item_loop_counter <= (($this->page_index - 1) * $this->page_size)) {
                    continue;
                }

                if ($this->is_v3()) {
                    $product = $item->get_product();
                } else {
                    $product = $order->get_product_from_item($item);
                }

                $sku = '';
                if (is_object($product)) {
                    $sku = $product->get_sku();
                }

                $image_url = "";
				$product_id = 0;
				if (is_object($product)) {
                    $rp_product_id = new ReflectionProperty($product, 'id');
                    if (method_exists($item, 'get_product_id')) {
                        $product_id = (int)$item->get_product_id();
                    } else if (method_exists($product, 'get_id')) {
                        $product_id = $product->get_id();
                    } else if ($rp_product_id->isPublic()) {
                        $product_id = $product->id;
                    }

                    $var_id = $product->is_type('variation') ? $item->get_variation_id() : $product_id;

                    $thumbnail_id = get_post_thumbnail_id($var_id);
                    if (empty($thumbnail_id)) {
                        $thumbnail_id = get_post_thumbnail_id($product_id);
                    }

                    $image_url = get_image_url($thumbnail_id, 'thumbnail');
				}

                if (method_exists($item, 'get_subtotal')) {
                    $product_price = $item->{'get_subtotal'}();
                } else {
                    $product_price = is_object($product) ? $product->price : "";
                }

                $product_quantity = wc_stock_amount($item['qty']);
                if (method_exists($item, 'get_total')) {
                    $product_total = $item->{'get_total'}();
                } else {
                    $product_total = ($product_price * (int)$product_quantity);
                }

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

                $variation = json_encode($variation_data);

                $orderProduct = array(
                    'order_id' => $order_id,
                    'image_url' => $image_url,
                    'product_id' => $product_id,
                    'sku' => $sku,
                    'product_name' => $item['name'],
                    'quantity' => $product_quantity,
                    'price' => $product_total,
                    'formatted_price' => mobassist_nice_price($product_total, $currency_code, false, false, true),
                    'product_type' => is_object($product) ? $this->_get_product_type($product_id) : "",
                    'variations' => $variation,
                );

                $order_products[] = $orderProduct;
            }
        }

        return $order_products;
    }

    private function _get_order_currency($order)
    {
        if (method_exists($order, 'get_currency')) {
            $currency_code = $order->get_currency();
        } else if (method_exists($order, 'get_order_currency')) {
            $currency_code = $order->get_order_currency();
        } else {
            $currency_code = get_woocommerce_currency();
        }

        return $currency_code;
    }

    private function _get_order()
    {
        $this->id = mobassist_validate_post($this->id, 'shop_order');

        if (!$this->id || empty($this->id)) {
            $this->generate_output_error(self::ERROR_CODE_ORDER_NOT_FOUND);
            return false;
        }

        try {
            $order = new WC_Order($this->id);
        } catch (Exception $exception) {
            throw new EM1Exception(EM1Exception::ERROR_CODE_COULD_NOT_LOAD_ORDER_OBJECT);
        }

        return $order;
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
                    posts.post_date_gmt AS date_add,
                    meta_email.meta_value AS email,
                    meta_first_name.meta_value AS first_name,
                    meta_last_name.meta_value AS last_name,
                    (SELECT COUNT(order_items.order_item_id) FROM `{$wpdb->prefix}woocommerce_order_items` AS order_items WHERE order_items.order_item_type = 'line_item' AND order_items.order_id = posts.ID) AS pr_qty
                FROM `$wpdb->posts` AS posts
                    LEFT JOIN `{$wpdb->postmeta}` AS meta ON posts.ID = meta.post_id
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_email ON meta_email.post_id = posts.ID AND meta_email.meta_key = '_billing_email'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_first_name ON meta_first_name.post_id = posts.ID AND meta_first_name.meta_key = '_billing_first_name'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_last_name ON meta_last_name.post_id = posts.ID AND meta_last_name.meta_key = '_billing_last_name'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_total ON meta_total.post_id = posts.ID AND meta_total.meta_key = '_order_total'
                    LEFT JOIN `{$wpdb->postmeta}` AS meta_curr ON meta_curr.post_id = posts.ID AND meta_curr.meta_key = '_order_currency'
                    LEFT JOIN `{$wpdb->prefix}woocommerce_order_items` AS order_items on order_items.order_id = posts.ID AND order_item_type = 'line_item'
                WHERE posts.post_type = 'shop_order' AND meta.meta_key = '_customer_user'";

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

        $sql .= sprintf(" LIMIT %d, %d", (($this->page_index - 1) * $this->page_size), $this->page_size);

        $query = $wpdb->prepare($sql, $value);

        $orders = array();
        $results = $wpdb->get_results($query, ARRAY_A);
        foreach ($results as $order) {
            $order_details = array(
                'order_id' => $order['id_order'],
                'customer_id' => $id,
                'customer_email' => $order['email'],
                'customer_first_name' => $order['first_name'],
                'customer_last_name' => $order['last_name'],
                'status_id' => $order['order_status_id'],
                'total' => $order['total_paid'],
                'formatted_total' => mobassist_nice_price($order['total_paid'], $order['currency_code'], false, false, true),
                'date_add' => self::convertTimestampToMillisecondsTimestamp((int)strtotime($order['date_add'])),
                'products_count' => $order['pr_qty']
            );

            $orders[] = $order_details;
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

        return $orders_total;
    }

    private function getSortDirection($order_by, $default_direction = 'DESC')
    {
        if (isset($order_by) && !empty($order_by)) {
            $direction = $order_by;
        } else {
            $direction = $default_direction;
        }

        return ' ' . $direction;
    }

    private function _get_products($fields, $fields_total, $sql, $from_products = false, $all_customers_fiels = false)
    {
        global $wpdb;
        $query_where_parts = array();

        if (!empty($this->date_from) && !empty($this->date_to) && $this->date_from !== -1 && $this->date_to !== -1) {
            $this->dateFrom = self::convertMillisecondsTimestampToTimestamp($this->date_from);
            $this->dateTo   = self::convertMillisecondsTimestampToTimestamp($this->date_to);
        }

        if (isset($this->show_all_customers) && !$this->show_all_customers && $all_customers_fiels) {
            $query_where_parts[] = " (cap.meta_key = '{$wpdb->prefix}capabilities' AND cap.meta_value LIKE '%customer%') ";
        }

        $query = $fields . $sql;
        $query_total = $fields_total . $sql;

        if (!empty($this->search_phrase) && preg_match('/^\d+(?:,\d+)*$/', $this->search_phrase)) {
            $query_where_parts[] = sprintf(" posts.ID IN ('%s')", $this->search_phrase);
        } elseif (!empty($this->search_phrase)) {
            $query_params_parts[] = sprintf(
                " (posts.post_title LIKE '%%%s%%' OR meta_sku.meta_value LIKE '%%%s%%')",
                $this->search_phrase, $this->search_phrase
            );
        }

        if (!empty($this->status_list_hide)) {
            $query_where_parts[] = " posts.post_status NOT IN ( '" . implode("', '", $this->status_list_hide) . "' )";
        }

        if (!empty($this->order_statuses)) {
            if (function_exists('wc_get_order_status_name')) {
                $query_where_parts[] = sprintf(
                    " posts_orders.post_status IN ('%s')",
                    implode("', '", $this->order_statuses)
                );
            } else {
                $query_where_parts[] = sprintf(
                    " status_terms.slug IN ('%s')",
                    implode("', '", $this->order_statuses)
                );
            }
        }

        if ($this->dateFrom) {
            $query_where_parts[] = sprintf(
                " UNIX_TIMESTAMP(posts_orders.post_date_gmt) >= '%d'",
                $this->dateFrom
            );
        }

        if ($this->dateTo) {
            $query_where_parts[] = sprintf(
                " UNIX_TIMESTAMP(posts_orders.post_date_gmt) <= '%d'",
                $this->dateTo
            );
        }

        if (!empty($query_params_parts)) {
            $query_where_parts[] = ' ( ' . implode(' OR ', $query_params_parts) . ' )';
        }

        if (!empty($query_where_parts)) {
            $query .= ' AND ' . implode(' AND ', $query_where_parts);
            $query_total .= ' AND ' . implode(' AND ', $query_where_parts);
        }

        if (!empty($this->group_by_product_id)) {
            $query .= ' GROUP BY posts.ID ORDER BY ';
        } elseif ($from_products) {
            $query .= ' GROUP BY posts.ID ORDER BY ';
        } else {
            $query .= ' GROUP BY order_items.order_id, posts.ID, order_items.order_item_name ORDER BY ';
        }

        if (empty($this->sort_field)) $this->sort_field = self::ORDER_BY_ID;

        switch ($this->sort_field) {
            case self::ORDER_BY_ID:
                $dir = $this->getSortDirection($this->sort_direction);
                $query .= 'posts.ID ' . $dir;
                break;
            case self::ORDER_BY_PRODUCT_NAME:
                $dir = $this->getSortDirection('ASC');
                if ($from_products) {
                    $query .= 'posts.post_title ' . $dir;
                } else {
                    $query .= 'order_items.order_item_name ' . $dir;
                }
                break;
            case self::ORDER_BY_QUANTITY:
                $dir = $this->getSortDirection($this->sort_direction);
                if ($from_products) {
                    $query .= 'CAST(meta_stock.meta_value AS unsigned) ' . $dir;
                } else {
                    $query .= 'CAST(meta_qty.meta_value AS unsigned) ' . $dir;
                }
                break;
            case self::ORDER_BY_PRICE:
                $dir = $this->getSortDirection($this->sort_direction);
                if ($from_products) {
                    $query .= 'CAST(meta_price.meta_value AS unsigned) ' . $dir;
                }
                break;
            case self::ORDER_BY_STATUS:
                $dir = $this->getSortDirection($this->sort_direction);
                if ($from_products) {
                    $query .= 'meta_stock_status.meta_value ' . $dir;
                }
                break;
        }

        $query .= sprintf(" LIMIT %d, %d", (($this->page_index - 1) * $this->page_size), $this->page_size);

        $products_count = array('count_prods' => 0,);
        if ($row_total = $wpdb->get_row($query_total, ARRAY_A)) {
            $products_count = $row_total;
        }

        $products = array();
        $results = $wpdb->get_results($query, ARRAY_A);
        foreach ($results as $product) {
            $sale_price = isset($product['sale_price']) && !empty($product['sale_price']) ? $product['sale_price'] : false;
            $regular_price = isset($product['regular_price']) && !empty($product['regular_price']) ? $product['regular_price'] : false;

            $product['product_id'] = (int)$product['product_id'];
            $product['sale_price'] = $sale_price ? (float)$sale_price : null;
            $product['formatted_sale_price'] = $sale_price ? mobassist_nice_price($sale_price, false, false, false, true) : null;

            $product['regular_price'] = $regular_price ? (float)$regular_price : null;
            $product['formatted_regular_price'] = $regular_price ? mobassist_nice_price($regular_price, false, false, false, true) : null;

            $product['quantity'] = (int)$product['quantity'];
            $product['product_type'] = $this->_get_product_type($product['product_id']);
            $product['sku'] = $product['sku'] ? $product['sku'] : "";

            if (!in_array($product['product_type'], array('Simple', 'Grouped'))) {
                unset($product['status_code']);
            }

            $product['price_html'] = $this->_get_product_html_price($product['product_id'], $product['product_type']);
            $product['status'] = $this->_get_product_status($product['post_status']);
            unset($product['post_status']);

            if ($product['stock'] == null) {
                $productWP = new WC_Product((int)$product['product_id']);
                if ($productWP) {
                    if (method_exists($productWP, 'get_stock_status')) {
                        $product['stock'] = $productWP->get_stock_status();
                    } else {
                        $product['stock'] = $productWP->stock_status;
                    }
                }
            }

            $images = array();
            if (empty($this->without_thumbnails)) {
                $cover_id = get_post_thumbnail_id($product['product_id']);

                $pos = 1;
                $images[] = array(
                    "image_id" => (int)$cover_id,
                    "position" => $pos,
                    "image_url" => get_image_url($cover_id, 'shop_catalog'),
                    "cover" => true,
//                    "image_url_large" => get_image_url($cover_id, 'large')
                );
            }

            $product['images'] = $images;

            $products[] = $product;
        }

        return array('products_count' => $products_count['count_prods'], 'products' => $products);
    }

    private function _get_product_html_price($product_id, $product_type)
    {
        if ($product_type == 'Grouped') {
            $productWP = new WC_Product_Grouped((int)$product_id);
            $price_html = $productWP->get_price_html();
        } else if ($product_type == 'Variable') {
            $productWP = new WC_Product_Variable((int)$product_id);
            $price_html = $productWP->get_price_html();
        } else {
            $productWP = new WC_Product((int)$product_id);
            $price_html = $productWP->get_price_html();
        }

        return trim(strip_tags($price_html, '<del><ins>'));
    }

    private function _get_product_status($post_status)
    {
        $stat = 'Undefined';
        switch (strtolower($post_status)) {
            case 'publish' :
                $stat = __('Published', 'mobile-assistant-connector');
                break;
            case 'private' :
                $stat = __('Privately Published', 'mobile-assistant-connector');
                break;
            case 'future' :
                $stat = __('Scheduled', 'mobile-assistant-connector');
                break;
            case 'pending' :
                $stat = __('Pending', 'mobile-assistant-connector');
                break;
            case 'draft' :
                $stat = __('Draft', 'mobile-assistant-connector');
                break;
            case 'trash' :
                $stat = __('Trash', 'mobile-assistant-connector');
                break;
            default:
                $stat = $post_status;
        }

        return trim($stat);
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

        $sql .= " WHERE order_items.order_item_type = 'line_item' AND posts.post_type = 'product'";

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

        $product['sale_price'] = isset($product['sale_price']) ? mobassist_nice_price($product['sale_price'], $this->currency) : null;
        $product['price'] = mobassist_nice_price($product['price'], $this->currency, false, true);
        $product['quantity'] = (int)$product['quantity'];
        $product['total_ordered'] = (int)$product['total_ordered'];

        $product['forsale'] = $this->_get_product_status($product['post_status']);
        $product['product_type'] = $this->_get_product_type($this->product_id);

        // get product images
        $productWP = new WC_product($this->product_id);
        $attachment_ids = $productWP->get_gallery_image_ids();

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

    private static function getProductStatuses()
    {
        $statuses = get_post_statuses();

        if (array_key_exists('private', $statuses)) {
            //unset($statuses['private']);
            $statuses['private'] = "Privately Published";
        }

        return $statuses;
    }

    private static function getMaxFileUploadInBytes()
    {
        $max_file_upload =  self::calculateBytes(ini_get('upload_max_filesize'));
        if (is_multisite()){
            $max_file_upload_multistore = self::calculateBytes(get_site_option('fileupload_maxk') . 'k');
            if($max_file_upload >= $max_file_upload_multistore) {
                $max_file_upload = $max_file_upload_multistore;
            }
        }
        //select maximum upload size
        return $max_file_upload;

        //select maximum upload size
        /*$max_upload = self::calculateBytes(ini_get('upload_max_filesize'));

        //select post limit
        $max_post = self::calculateBytes(ini_get('post_max_size'));

        //select memory limit
        $memory_limit = self::calculateBytes(ini_get('memory_limit'));

        // return the smallest of them, this defines the real limit
        return min($max_upload, $max_post, $memory_limit);*/
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

    private static function saveProductData($product_data, $is_new_product)
    {
        global $wpdb;
        if (empty($product_data) || !isset($product_data['product_id']) || $product_data['product_id'] < 1) {
            return 'No product data or product_id';
        }

        $product_id = $product_data['product_id'];

        $post = get_post($product_id, ARRAY_A);
        $post['ID'] = $product_id;
        if(isset($product_data['product_name']) && $product_data['product_name'] !== null) $post['post_title'] = $product_data['product_name'];
        //if(isset($product_data['publish_status']) && $product_data['publish_status'] !== null) $post['post_status'] = $product_data['publish_status'];
        if(isset($product_data['menu_order']) && $product_data['menu_order'] !== null) $post['menu_order'] = $product_data['menu_order'];

        if (isset($product_data['publish_visibility']) && $product_data['publish_visibility'] !== null && $product_data['publish_visibility'] == 'private') {
            $post['post_password'] = '';
            $post['post_status'] = 'private';
        } else {
            if (isset($product_data['publish_visibility']) && $product_data['publish_visibility'] !== null && $product_data['publish_visibility'] == 'public') {
                $post['post_password'] = '';
                $post['post_status'] = 'publish';
            }
            if (isset($product_data['publish_visibility']) && $product_data['publish_visibility'] !== null && $product_data['publish_visibility'] == 'password') {
                $post['post_password'] = $product_data['publish_password_visibility'];
                $post['post_status'] = 'publish';
            }
            if (isset($product_data['publish_status']) && $product_data['publish_status'] !== null) {
                $post['post_status'] = $product_data['publish_status'];
            }
        }

        if (isset($product_data['enable_reviews']) && $product_data['enable_reviews'] !== null) {
            $post['comment_status'] = ($product_data['enable_reviews'] ? 'open' : 'closed');
        }

        $everything_updated = true;
        if (!wp_update_post($post)) {
            $everything_updated = false;
        }

        if ($product_data['product_type'] == 'Grouped') $product_data['product_type'] = 'grouped';
        else if ($product_data['product_type'] == 'External/Affiliate') $product_data['product_type'] = 'external';
        else if ($product_data['product_type'] == 'Variable') $product_data['product_type'] = 'variable';
        else if ($product_data['product_type'] == 'Virtual&Downloadable') {
            $product_data['product_type'] = 'simple';
            $product_data['virtual'] = 'yes';
            $product_data['downloadable'] = 'yes';
        } else if ($product_data['product_type'] == 'Virtual') {
            $product_data['product_type'] = 'simple';
            $product_data['virtual'] = 'yes';
            $product_data['downloadable'] = 'no';
        } else if ($product_data['product_type'] == 'Downloadable') {
            $product_data['product_type'] = 'simple';
            $product_data['downloadable'] = 'yes';
            $product_data['virtual'] = 'no';
        } else if ($product_data['product_type'] == 'Simple') {
            $product_data['product_type'] = 'simple';
            $product_data['downloadable'] = 'no';
            $product_data['virtual'] = 'no';
        }

        if(isset($product_data['sku'])) self::_update_post_meta($product_id, '_sku', $product_data['sku']);
        if(isset($product_data['virtual'])) self::_update_post_meta($product_id, '_virtual', $product_data['virtual']);
        if(isset($product_data['downloadable'])) self::_update_post_meta($product_id, '_downloadable', $product_data['downloadable']);
        if(isset($product_data['download_limit'])) self::_update_post_meta($product_id, '_download_limit', $product_data['download_limit']);
        if(isset($product_data['download_expiry'])) self::_update_post_meta($product_id, '_download_expiry', $product_data['download_expiry']);
        if(isset($product_data['manage_stock'])) self::_update_post_meta($product_id, '_manage_stock', $product_data['manage_stock'], '', true);
        if(isset($product_data['stock_quantity'])) self::_update_post_meta($product_id, '_stock', $product_data['stock_quantity']);
        if(isset($product_data['backorders_allow'])) self::_update_post_meta($product_id, '_backorders', $product_data['backorders_allow'], '', false);
        if(isset($product_data['stock_status'])) self::_update_post_meta($product_id, '_stock_status', $product_data['stock_status']);
        if(isset($product_data['sold_individually'])) self::_update_post_meta($product_id, '_sold_individually', $product_data['sold_individually'], '', true);
        if(isset($product_data['purchase_note'])) self::_update_post_meta($product_id, '_purchase_note', $product_data['purchase_note']);
        if(isset($product_data['product_url'])) self::_update_post_meta($product_id, '_product_url', $product_data['product_url']);
        if(isset($product_data['button_text'])) self::_update_post_meta($product_id, '_button_text', $product_data['button_text']);
        if(isset($product_data['tax_class'])) self::_update_post_meta($product_id, '_tax_class', $product_data['tax_class']);
        if(isset($product_data['tax_status'])) self::_update_post_meta($product_id, '_tax_status', $product_data['tax_status']);

        if (isset($product_data['publish_catalog_visibility']) && $product_data['publish_catalog_visibility'] != null) {
            $product_type = WC_Product_Factory::get_product_type($product_id);
            $classname = WC_Product_Factory::get_product_classname($product_id, $product_type);
            $product = new $classname($product_id);
            $product->set_catalog_visibility($product_data['publish_catalog_visibility']);
            $product->save();
        }

        if (isset($product_data['product_tags']) && $product_data['product_tags'] != null) {
            $product_tags = $product_data['product_tags'];
            $comma = _x(',', 'tag delimiter');
            if (',' !== $comma) {
                $product_tags = str_replace($comma, ',', $product_tags);
            }
            $product_tags = explode(',', trim($product_tags, " \n\t\r\0\x0B,"));
            wp_set_object_terms($product_id, $product_tags, 'product_tag');
        }

        // Correct Update of price fields
        $regular_price = isset($product_data['regular_price']) ? $product_data['regular_price'] : null;
        $sale_price = isset($product_data['sale_price']) ? $product_data['sale_price'] : null;
        if ($sale_price !== null && $regular_price !== null && (float)$regular_price < (float)$sale_price) {
            $sale_price = $regular_price;
        }

        self::_update_post_meta($product_id, '_regular_price', $regular_price);
        self::_update_post_meta($product_id, '_sale_price', $sale_price);
        if ((float)$sale_price > 0) {
            self::_update_post_meta($product_id, '_price', $sale_price);
        } else {
            self::_update_post_meta($product_id, '_price', $regular_price);
        }

        if (isset($product_data['sale_price_dates_from']) && $product_data['sale_price_dates_from'] !== null ) {
            $date_on_sale_from = wc_clean(wp_unslash($product_data['sale_price_dates_from']));
            if (!empty($date_on_sale_from)) {
                $date_on_sale_from = strtotime(date('Y-m-d 00:00:00', strtotime($date_on_sale_from)));
                self::_update_post_meta($product_id, '_sale_price_dates_from', $date_on_sale_from);
            }
        }

        if (isset($product_data['sale_price_dates_to']) && $product_data['sale_price_dates_to'] !== null ) {
            $date_on_sale_to = wc_clean(wp_unslash($product_data['sale_price_dates_to']));
            if (!empty($date_on_sale_to)) {
                $date_on_sale_to = strtotime(date('Y-m-d', strtotime($date_on_sale_to)));
                self::_update_post_meta($product_id, '_sale_price_dates_to', $date_on_sale_to);
            }
        }

        if (isset($product_data['changed_images']) && $product_data['changed_images'] != null && !empty($product_data['changed_images'])) {
            $image_ids = self::_getGalleryImageIds($product_id);
            $cover_id = get_post_thumbnail_id($product_id);

            // Set main image
            foreach ($product_data['changed_images'] as $changed_image) {
                if (isset($changed_image["cover"]) && is_bool($changed_image["cover"]) && $changed_image["cover"]) {
                    $new_cover_id = $changed_image["image_id"];
                    update_post_meta($product_id, '_thumbnail_id', $new_cover_id);

                    if ($cover_id != $new_cover_id) {
                        $key = array_search($new_cover_id, $image_ids);
                        if ($key !== false) unset($image_ids[$key]);

                        $image_ids[] = $cover_id;
                    }
                }

                $update_image_data = array(
                    'ID' => $changed_image['image_id'],
                    'post_excerpt' => $changed_image['caption'],
                    'post_title' => $changed_image['title'],
                    'post_content' => $changed_image['description']
                );

                wp_update_post($update_image_data);
                update_post_meta($changed_image['image_id'], '_wp_attachment_image_alt', $changed_image['alt_text']);



            }

            $image_ids = array_unique($image_ids);
            update_post_meta($product_id, '_product_image_gallery', implode(',', $image_ids));
        }

        // Update product gallery
        // "deleted_images_ids":[{
        //     "first":157, // woo image id
        //     "second":125, // assistant internal id
        //     "third":false // is cover
        //}]
        if (isset($product_data['deleted_images_ids']) && $product_data['deleted_images_ids'] != null && !empty($product_data['deleted_images_ids'])) {
            $image_ids = self::_getGalleryImageIds($product_id);

            // Delete main image
            $cover_id = get_post_thumbnail_id($product_id);
            foreach ($product_data['deleted_images_ids'] as $deleted_image) {
                $image_id = $deleted_image["first"];

                if ($deleted_image["third"] && $cover_id == $image_id) {
                    delete_post_meta($product_id, '_thumbnail_id');
                } else {
                    $key = array_search($image_id, $image_ids);
                    if ($key !== false) unset($image_ids[$key]);
                }
            }
            update_post_meta($product_id, '_product_image_gallery', implode(',', $image_ids));
        }

        // Update downloadable files
//        $downloadable_files = !empty($product_data['downloadable_files'])
//            ? json_decode($product_data['downloadable_files'], true)
//            : array();
//        self::updateDownloadableFiles($downloadable_files, $product_id);

        if ($product_data['product_type'] != null) {
            wp_set_object_terms($product_id, $product_data['product_type'], 'product_type');
//            $wpdb->update(
//                $wpdb->term_relationships,
//                array(
//                    'term_taxonomy_id' => $wpdb->get_var(
//                        "SELECT `term_id` FROM `$wpdb->terms` WHERE `slug` = '{$product_data['product_type']}'"
//                    )
//                ),
//                array('object_id' => $product_id)
//            );
        }


        if (!$is_new_product) {
            $categories = wp_get_post_terms($product_id, 'product_cat', array('fields'=>'ids'));
            if (empty($categories)) {
                $classname = WC_Product_Factory::get_product_classname($product_id, $product_data['product_type']);
                $product = new $classname($product_id);
                $default_category = (int)get_option('default_product_cat');
                $product->set_category_ids($default_category);
                $product->save();
            }

            return $everything_updated;
        }

        $classname = WC_Product_Factory::get_product_classname($product_id, $product_data['product_type']);
        $product = new $classname($product_id);
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

        if (empty(get_the_category($product_id))) {
            $default_category = (int)get_option('default_product_cat');
            $product->set_category_ids($default_category);
        }

        if (!is_wp_error($errors)) {
            $product->save();
        }

        return $everything_updated;
    }

    private static function saveProductDescr($product_data, $is_short)
    {
        if (empty($product_data) || !isset($product_data['product_id']) || $product_data['product_id'] < 1) {
            return 'No product data or product_id';
        }

        $post = get_post($product_data['product_id'], ARRAY_A);
        $post['ID'] = $product_data['product_id'];

        if ($is_short) {
            $post['post_excerpt'] = $product_data['description'];
        } else {
            $post['post_content'] = $product_data['description'];
        }

        return wp_update_post($post);
    }

    private static function _update_post_meta($post_id, $meta_key, $meta_value, $prev_value = '', $is_yes_no = false)
    {
        if(!is_bool($meta_value) && $meta_value === null) return;
        if($is_yes_no) $meta_value = ($meta_value ? 'yes' : 'no');
        update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
    }

    private static function storeDownloadableFile($product_data, $downloadable_file)
    {
        if ($downloadable_file['file']['error'] != UPLOAD_ERR_OK) {
            return array('error' => 'downloadable_file_error: ' . $downloadable_file['file']['error']);
        }

        $product_id = $product_data['product_id'];
        $time = current_time('mysql');
        if ($post = get_post($product_id)) {
            if (substr($post->post_date, 0, 4) > 0) {
                $time = $post->post_date;
            }
        }

        if (!($upload_dir = wp_upload_dir($time))) {
            return array('error' => 'wp_upload_dir');
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
            return array('error' => 'move_uploaded_file');
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

        if (isset($product_data['file_name'])) {
            $name = trim($product_data['file_name']);
        } else {
            $name = $downloadable_file['file']['name'];
            $name_parts = pathinfo($name);
            $name = trim(substr($name, 0, -(1 + strlen($name_parts['extension']))));
        }

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
            'post_excerpt' => ''
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

            return array('file_info' => array("file_id" => $id, "mime" => $type));
        }

        return array('error' => 'insert_attachment');
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
            return array('error' => 'upload_error: ' . $image_file['image']['error']);
        }

        $time = current_time('mysql');
        if ($post = get_post($product_id)) {
            if (substr($post->post_date, 0, 4) > 0) {
                $time = $post->post_date;
            }
        }

        if (!($uploads = wp_upload_dir($time))) {
            return array('error' => 'Get the uploads dir');
        }

        $filename = wp_unique_filename($uploads['path'], $image_file['image']['name']);

        // Move the file to the uploads dir
        $new_file = $uploads['path'] . "/$filename";
        if (!@move_uploaded_file($image_file['image']['tmp_name'], $new_file)) {
            return array('error' => 'Move the file to the uploads dir');
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
        $new_id = wp_insert_attachment($attachment, $new_file, $product_id);
        if (!is_wp_error($new_id) && wp_update_attachment_metadata($new_id, self::wp_generate_attachment_metadata($new_id, $new_file))) {
            if (!$is_main_image) {
                $product = new WC_Product($product_id);
                $image_ids = $product->get_gallery_image_ids();
                $image_ids[] = $new_id;
                update_post_meta($product_id, '_product_image_gallery', implode(',', $image_ids));
            } else {
                update_post_meta($product_id, '_thumbnail_id', $new_id);
            }

            return array('image_info' => array('image_id' => $new_id, "image_url" => $url));
        }

        return array('error' => 'Save the image data');
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
            $support = current_theme_supports('post-thumbnails', 'attachment:video') || post_type_supports('attachment:video', 'thumbnail');
        } elseif (wp_attachment_is('audio', $attachment)) {
            $metadata = wp_read_audio_metadata($file);
            $support = current_theme_supports('post-thumbnails', 'attachment:audio') || post_type_supports('attachment:audio', 'thumbnail');
        }

        if ($support && !empty($metadata['image']['data'])) {
            // Check for existing cover.
            $hash = md5($metadata['image']['data']);
            $posts = get_posts(
                array(
                    'fields' => 'ids',
                    'post_type' => 'attachment',
                    'post_mime_type' => $metadata['image']['mime'],
                    'post_status' => 'inherit',
                    'posts_per_page' => 1,
                    'meta_key' => '_cover_hash',
                    'meta_value' => $hash
                )
            );
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
                    $image_attachment = apply_filters('attachment_thumbnail_args', $image_attachment, $metadata, $uploaded);

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
        if (!$data['push_new_order'] && !$data['push_order_statuses'] && !$data['push_new_customer']) {
            $sql_del = "DELETE FROM `{$wpdb->prefix}mobileassistant_push_settings` WHERE registration_id = '%s' 
                        AND app_connection_id = '%s'";
            $sql_del = sprintf($sql_del, $data['registration_id'], $data['app_connection_id']);

            $wpdb->query($sql_del);
            return true;
        }

        // Check if device could have higher permissions
        if (in_array('push_notification_settings_new_order', $data['user_actions'])) {
            $data['push_new_order'] = $data['push_new_order'];
        } else {
            $data['push_new_order'] = 0;
        }

        if (in_array('push_notification_settings_new_customer', $data['user_actions'])) {
            $data['push_new_customer'] = $data['push_new_customer'];
        } else {
            $data['push_new_customer'] = 0;
        }

        if (in_array('push_notification_settings_order_statuses', $data['user_actions'])) {
            $data['push_order_statuses'] = (string)$this->push_order_statuses;
            $data['not_notified_order_statuses_ids'] = implode(',', $data['not_notified_order_statuses_ids']);
        } else {
            $data['not_notified_order_statuses_ids'] = 0;
        }

        $query_values[] = sprintf(" push_new_order = '%d'", $data['push_new_order']);
        $query_values[] = sprintf(" push_order_statuses = '%d'", $data['push_order_statuses']);
        $query_values[] = sprintf(" not_notified_order_statuses_ids = '%s'", $data['not_notified_order_statuses_ids']);
        $query_values[] = sprintf(" push_new_customer = '%d'", $data['push_new_customer']);
        $query_values[] = sprintf(" push_currency_code = '%s'", $data['push_currency_code']);
        if(isset($data['device_unique_id'])) $query_values[] = sprintf(" `device_unique_id` = %d", $data['device_unique_id']);

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

    private function _is_action_allowed()
    {
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
            'get_settings',
            'get_token'
        );

        if (in_array($this->call_function, $allowed_functions_always)) {
            return true;
        }

        $user_allowed_actions = Mobassistantconnector_Access::get_allowed_actions_by_session_key($this->token);

        $all_actions = Mobassistantconnector_Functions::get_default_actions();

        if ($this->call_function == 'set_order_action') {
            if ($this->action == 'change_status' && in_array('update_order_status', $user_allowed_actions)) {
                return true;
            } elseif ($this->action == 'update_track_number'
                && in_array('update_order_tracking_number', $user_allowed_actions)
            ) {
                return true;
            }
        } else {
            foreach ($all_actions as $action_group) {
                foreach ($action_group as $action) {
                    if (in_array($this->call_function, $action['functions'])) {
                        if (in_array($action['code'], $user_allowed_actions)) {
                            return true;
                        }

                        break 2;
                    }
                }
            }
        }

        return false;
    }

    private function _check_allowed_actions()
    {
        if (!$this->_is_action_allowed()) {
            $this->generate_output_error('action_forbidden');
        }
    }

    public function generate_output_error($code = self::RESPONSE_CODE_ERROR, $error = '')
    {
        $this->generate_output(array(), $code, $error);
    }

    public function generate_output($data, $code = self::RESPONSE_CODE_SUCCESS, $error = '')
    {
        $add_connector_version = false;
        if (is_array($data)
            && $data != self::RESPONSE_CODE_AUTH_ERROR
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
        }

        if (is_array($data)) {
            array_walk_recursive($data, 'reset_null');
        }

        if ($add_connector_version) {
            $data['module_version'] = self::PLUGIN_CODE;
        }

        $data = array_merge(
            $data,
            array(self::RESPONSE_CODE => (string)$code),
            array(self::ERROR_MESSAGE => (string)$error)
        );

        header('Content-Type: application/json;');
        status_header(200);
        $data = wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo wp_kses_data($data);
        die();
    }

    public static function convertTimestampToMillisecondsTimestamp($timestamp)
    {
        return (int)$timestamp * 1000;
    }

    public static function convertMillisecondsTimestampToTimestamp($timestamp)
    {
        return (int)$timestamp/1000;
    }

    public function round($number, $precision = 6)
    {
        return round((float)$number, $precision);
    }

    private function _getDashboardGrouping($timestampFrom, $timestampTo)
    {
        try {
            $dateTo     = new DateTime(date(self::GLOBAL_DATE_FORMAT, $timestampTo));
            $dateFrom   = new DateTime(date(self::GLOBAL_DATE_FORMAT, $timestampFrom));
        } catch (Exception $e) {
            throw new EM1Exception(
                EM1Exception::ERROR_CODE_COULD_NOT_CREATE_DATETIME_OBJECT,
                $e->getMessage()
            );
        }

        /** @var DateTime $dateTo */
        /** @var DateTime $dateFrom */
        $timestampDifferences = date_diff($dateTo, $dateFrom);
        switch (true) {
            case ($timestampDifferences->y >= 1):
                return self::GROUP_BY_MONTH;
            case ($timestampDifferences->y === 0 && $timestampDifferences->m >= 3):
                return self::GROUP_BY_WEEK;
            case ($timestampDifferences->y === 0 && $timestampDifferences->days <= 3):
                return self::GROUP_BY_HOUR;
            default:
                return self::GROUP_BY_DAY;
        }
    }

    private function  _sanitize_files_array()
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
