<?php
/**
 *	This file is part of Mobile Assistant Connector.
 *
 *   Mobile Assistant Connector is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Mobile Assistant Connector is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Mobile Assistant Connector.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @author    eMagicOne <contact@emagicone.com>
 *  @copyright 2014-2022 eMagicOne
 *  @license   http://www.gnu.org/licenses   GNU General Public License
 */

class Mobassistantconnector_Functions
{
//    const TABLE_DEVICES         = 'mobileassistant_devices';
//    const TABLE_ACCOUNTS        = 'mobileassistant_accounts';
//    const TABLE_PUSH_SETTINGS   = 'mobileassistant_push_settings';

//    public static function delete_empty_devices()
//    {
//        global $wpdb;
//
//        $sql
//            = "DELETE md FROM `" . $wpdb->prefix . self::TABLE_DEVICES . "` md
//			LEFT JOIN `" . $wpdb->prefix . self::TABLE_PUSH_SETTINGS . "` mpn ON mpn.`device_unique_id` = md.`device_unique_id`
//			WHERE mpn.`device_unique_id` IS NULL";
//        $wpdb->query($sql);
//    }
//
//    public static function delete_empty_accounts()
//    {
//        global $wpdb;
//
//        $sql
//            = "DELETE ma FROM `" . $wpdb->prefix . self::TABLE_ACCOUNTS . "` ma
//        LEFT JOIN `" . $wpdb->prefix . self::TABLE_DEVICES . "` md ON md.`account_id` = ma.`id`
//        WHERE ma.`id` IS NULL";
//        $wpdb->query($sql);
//    }

    public static function get_default_actions()
    {
        $restricted_actions = array(
            'Push notification settings' => array(
                array(
                    'code'      => 'push_notification_settings_new_order',
                    'name'      => 'New order',
                    'functions' => array(
                        'push_notification_settings',
                        'delete_push_config',
                    ),
                ),
                array(
                    'code' => 'push_notification_settings_new_customer',
                    'name' => 'New customer',
                    'functions' => array(
                        'push_notification_settings',
                        'delete_push_config',
                    ),
                ),
                array(
                    'code' => 'push_notification_settings_order_statuses',
                    'name' => 'Order statuses',
                    'functions' => array(
                        'push_notification_settings',
                        'delete_push_config',
                    ),
                ),
            ),
            'Store statistics'           => array(
                array(
                    'code' => 'store_stats',
                    'name' => 'Store statistics',
                    'functions' => array(
                        'get_store_stats',
                        'get_data_graphs',
                        'get_status_stats',
                        'get_dashboard',
                        'get_widget_data',
                    ),
                ),
            ),
            'Store settings'           => array(
                array(
                    'code' => 'get_settings',
                    'name' => 'Store settings',
                    'functions' => array(
                        'get_settings',
                    ),
                ),
                array(
                    'code' => 'get_token',
                    'name' => 'Store token',
                    'functions' => array(
                        'get_token',
                    ),
                )
            ),
            'Products'                   => array(
                array(
                    'code' => 'products_list',
                    'name' => 'Product list',
                    'functions' => array(
                        'search_products',
                        'get_products',
                        'search_products_ordered',
                    ),
                ),
                array(
                    'code' => 'product_details',
                    'name' => 'Product details',
                    'functions' => array(
                        'get_products_info',
                        'get_products_descr',
                        'get_product_edit_description',
                        'get_product_edit_short_description',
                        'get_product_details',
                    ),
                ),
                array(
                    'code' => 'product_edit',
                    'name' => 'Product editing',
                    'functions' => array(
                        'get_product_to_edit',
                        'get_product_edit_data',
                        'get_product_edit_assigned_categories',
                        'get_product_edit_categories_to_assign',
                        'update_product',
                        'save_product',
                        'save_product_description',
                        'save_product_short_description',
                        'upload_product_image',
                        'update_assigned_product_categories',
                        'upload_product_attached_file',
                        'get_product_edit_downloadable_files',
                        'update_product_attached_file',
                        'delete_product_edit_downloadable_file',
                        'delete_product',
                        'get_new_variation_data',
                        'get_product_edit_variations',
                        'save_product_variations',
                        'delete_product_variation',
                        'add_product_attribute',
                        'get_product_edit_attributes',
                        'save_product_attribute',
                        'delete_product_attribute',
                        'generate_product_variations',
                        'get_data_for_new_product',
                        'get_new_product_data',
                        'add_product',
                    ),
                ),
            ),
            'Customers'                  => array(
                array(
                    'code' => 'customers_list',
                    'name' => 'Customer list',
                    'functions' => array(
                        'get_customers',
                        'search_customers',
                    ),
                ),
                array(
                    'code' => 'customer_details',
                    'name' => 'Customer details',
                    'functions' => array(
                        'get_customers_info',
                        'get_customer_details',
                        'get_customer_orders',
                    ),
                ),
            ),
            'Orders'                     => array(
                array(
                    'code' => 'orders_list',
                    'name' => 'Order list',
                    'functions' => array(
                        'get_orders',
                        'search_orders',
                        'get_shipping_methods',
                    ),
                ),
                array(
                    'code' => 'order_details',
                    'name' => 'Order details',
                    'functions' => array(
                        'get_orders_info',
                        'get_order_details',
                        'get_order_products',
                    ),
                ),
                array(
                    'code' => 'order_details_pdf',
                    'name' => 'Order details PDF',
                    'functions' => array(
                        'get_order_pdf',
                        'check_download_order_invoice_availability',
                        'download_order_invoice',
                    ),
                ),
                array(
                    'code' => 'update_order_status',
                    'name' => 'Order status updating',
                    'functions' => array(
                        'set_order_action',
                        'change_status',
                        'change_order_status',
                        'update_order_shipping_details',
                    ),
                ),
            ),
        );

        return $restricted_actions;
    }

    public static function getActionsToPluginVersion()
    {
        return array(
            'product_edit' => '1.4.0',
        );
    }
}