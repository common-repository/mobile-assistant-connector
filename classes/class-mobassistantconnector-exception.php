<?php
/**
 *    This file is part of eMagicOne Store Manager Bridge Connector.
 *
 *   eMagicOne Store Manager Bridge Connector is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   eMagicOne Store Manager Bridge Connector is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with eMagicOne Store Manager Bridge Connector. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    eMagicOne <contact@emagicone.com>
 * @copyright 2014-2022 eMagicOne
 * @license   http://www.gnu.org/licenses   GNU General Public License
 */

class EM1Exception extends Exception
{
    const ERROR_CODE_ORDER_ID_IS_INCORRECT                          = 'order_id_is_incorrect';
    const ERROR_CODE_ADDRESS_ID_IS_INCORRECT                        = 'address_id_is_incorrect';
    const ERROR_CODE_ORDER_NOT_FOUND                                = 'order_not_found';
    const ERROR_CODE_QUERY_EXECUTION_ERROR                          = 'query_execution_error';
    const ERROR_CODE_SEARCH_PHRASE_NOT_SET                          = 'search_phrase_not_set';
    const ERROR_CODE_CHANGE_STATUS_ORDER_FAILED                     = 'change_status_order_failed';
    const ERROR_CODE_ORDER_OBJECT_EXECUTION_ERROR                   = 'order_object_execution_error';
    const ERROR_CODE_INCORRECT_SORT_DATA                            = 'incorrect_sort_data';
    const ERROR_CODE_COULD_NOT_LOAD_ORDER_OBJECT                    = 'could_not_load_order_object'; //could_not_load_prestashop_order_object
    const ERROR_CODE_FAILED_WHEN_LOAD_ORDER_CARRIER                 = 'failed_when_load_order_carrier';
    const ERROR_CODE_CART_ID_IS_INCORRECT                           = 'cart_id_is_incorrect';
    const ERROR_CODE_CART_NOT_FOUND                                 = 'cart_not_found';
    const ERROR_CODE_USER_ID_INCORRECT                              = 'customer_id_incorrect';
    const ERROR_CODE_CUSTOMER_NOT_FOUND                             = 'customer_not_found';
    const ERROR_CODE_CUSTOMER_ID_IS_INCORRECT                       = 'customer_id_is_incorrect';
    const ERROR_CODE_PRODUCT_ID_IS_INCORRECT                        = 'product_id_incorrect';
    const ERROR_CODE_PRODUCT_NOT_FOUND                              = 'product_not_found';
    const ERROR_CODE_PRODUCT_NOT_ASSIGNED_TO_SHOP                   = 'product_not_assigned_to_shop';
    const ERROR_CODE_COULD_NOT_CREATE_DATETIME_OBJECT               = 'error_while_creating_date_time_object';
    const ERROR_CODE_FAILED_TO_SET_SHOP_CONTEXT                     = 'failed_to_set_shop_context';
    const ERROR_CODE_BAD_TOKEN                                      = 'bad_token';
    const ERROR_CODE_AUTH_ERROR                                     = 'auth_error';
    const ERROR_CODE_FORBIDDEN                                      = 'forbidden';
    const ERROR_CODE_UNKNOWN_REQUEST                                = 'unknown_request';
    const ERROR_CODE_MODULE_DISABLED                                = 'module_disabled';
    const ERROR_CODE_CONNECTION_ID_INCORRECT                        = 'connection_id_incorrect';
    const ERROR_CODE_MISSING_PARAMETERS                             = 'missing_parameters';
    const ERROR_CODE_CANNOT_DELETE_PUSH_CONFIG                      = 'cannot_delete_push_config';
    const ERROR_CODE_DATA_NOT_UPDATED                               = 'data_not_updated';
    const ERROR_CODE_UNKNOWN                                        = 'unknown_error';
    const ERROR_PRODUCT_ID_IS_INCORRECT                             = 'product_id_incorrect';
    const ERROR_PRODUCT_NOT_FOUND                                   = 'product_not_found';
    const ERROR_COMBINATION_NOT_FOUND                               = 'combination_not_found';
    const ERROR_PRODUCT_NOT_ASSIGNED_TO_SHOP                        = 'product_not_assigned_to_shop';
    const ERROR_PRODUCT_TYPE_IS_INCORRECT                           = 'product_type_incorrect';
    const ERROR_IMAGE_NOT_FOUND                                     = 'image_id_incorrect';
    const ERROR_THE_ORDER_CARRIER_ID_IS_INVALID                     = 'the_order_carrier_id_is_invalid';
    const ERROR_THE_TRACKING_NUMBER_IS_INCORRECT                    = 'the_tracking_number_is_incorrect';
    const ERROR_WHILE_UPDATING_ORDER_CARRIER                        = 'error_while_updating_order_carrier';
    const ERROR_WHILE_LOADING_REFRESHED_ORDER_CARRIER_OBJECT
        = 'error_while_loading_refreshed_order_carrier_object';
    const ERROR_THE_ORDER_CANNOT_BE_UPDATED                         = 'the_order_cannot_be_updated';
    const ERROR_THE_ORDER_CARRIER_CANNOT_BE_UPDATED                 = 'the_order_carrier_cannot_be_updated';
    const ERROR_AN_ERROR_OCCURRED_WHILE_SENDING_AN_EMAIL_TO_THE_CUSTOMER
        = 'an_error_occurred_while_sending_an_email_to_the_customer';
    const ERROR_AN_ERROR_OCCURRED_WHILE_TRIGGERING_ORDER_TRACKING_NUMBER_UPDATE_HOOK
        = 'an_error_occurred_while_triggering_order_tracking_number_update_hook';

    const ERROR_CODES   = array(
        self::ERROR_CODE_ORDER_ID_IS_INCORRECT,
        self::ERROR_CODE_ADDRESS_ID_IS_INCORRECT,
        self::ERROR_CODE_ORDER_NOT_FOUND,
        self::ERROR_CODE_QUERY_EXECUTION_ERROR,
        self::ERROR_CODE_SEARCH_PHRASE_NOT_SET,
        self::ERROR_CODE_CHANGE_STATUS_ORDER_FAILED,
        self::ERROR_CODE_ORDER_OBJECT_EXECUTION_ERROR,
        self::ERROR_CODE_INCORRECT_SORT_DATA,
        self::ERROR_CODE_COULD_NOT_LOAD_ORDER_OBJECT,
        self::ERROR_CODE_FAILED_WHEN_LOAD_ORDER_CARRIER,
        self::ERROR_CODE_CART_ID_IS_INCORRECT,
        self::ERROR_CODE_CART_NOT_FOUND,
        self::ERROR_CODE_USER_ID_INCORRECT,
        self::ERROR_CODE_CUSTOMER_NOT_FOUND,
        self::ERROR_CODE_CUSTOMER_ID_IS_INCORRECT,
        self::ERROR_CODE_PRODUCT_ID_IS_INCORRECT,
        self::ERROR_CODE_PRODUCT_NOT_FOUND,
        self::ERROR_CODE_PRODUCT_NOT_ASSIGNED_TO_SHOP,
        self::ERROR_CODE_COULD_NOT_CREATE_DATETIME_OBJECT,
        self::ERROR_CODE_FAILED_TO_SET_SHOP_CONTEXT,
        self::ERROR_CODE_BAD_TOKEN,
        self::ERROR_CODE_AUTH_ERROR,
        self::ERROR_CODE_FORBIDDEN,
        self::ERROR_CODE_UNKNOWN_REQUEST,
        self::ERROR_CODE_MODULE_DISABLED,
        self::ERROR_CODE_CONNECTION_ID_INCORRECT,
        self::ERROR_CODE_MISSING_PARAMETERS,
        self::ERROR_CODE_CANNOT_DELETE_PUSH_CONFIG,
        self::ERROR_CODE_DATA_NOT_UPDATED,
        self::ERROR_CODE_UNKNOWN,
        self::ERROR_PRODUCT_ID_IS_INCORRECT,
        self::ERROR_PRODUCT_NOT_FOUND,
        self::ERROR_COMBINATION_NOT_FOUND,
        self::ERROR_PRODUCT_NOT_ASSIGNED_TO_SHOP,
        self::ERROR_PRODUCT_TYPE_IS_INCORRECT,
        self::ERROR_IMAGE_NOT_FOUND,
        self::ERROR_THE_ORDER_CARRIER_ID_IS_INVALID,
        self::ERROR_THE_TRACKING_NUMBER_IS_INCORRECT,
        self::ERROR_WHILE_UPDATING_ORDER_CARRIER,
        self::ERROR_WHILE_LOADING_REFRESHED_ORDER_CARRIER_OBJECT,
        self::ERROR_THE_ORDER_CANNOT_BE_UPDATED,
        self::ERROR_THE_ORDER_CARRIER_CANNOT_BE_UPDATED,
        self::ERROR_AN_ERROR_OCCURRED_WHILE_SENDING_AN_EMAIL_TO_THE_CUSTOMER,
        self::ERROR_AN_ERROR_OCCURRED_WHILE_TRIGGERING_ORDER_TRACKING_NUMBER_UPDATE_HOOK
    );

    const ERROR_MESSAGE = array(
        self::ERROR_CODE_ORDER_ID_IS_INCORRECT                     => '',
        self::ERROR_CODE_ADDRESS_ID_IS_INCORRECT                   => '',
        self::ERROR_CODE_ORDER_NOT_FOUND                           => '',
        self::ERROR_CODE_QUERY_EXECUTION_ERROR                     => '',
        self::ERROR_CODE_SEARCH_PHRASE_NOT_SET                     => '',
        self::ERROR_CODE_CHANGE_STATUS_ORDER_FAILED                => '',
        self::ERROR_CODE_ORDER_OBJECT_EXECUTION_ERROR              => '',
        self::ERROR_CODE_INCORRECT_SORT_DATA                       => '',
        self::ERROR_CODE_COULD_NOT_LOAD_ORDER_OBJECT    => '',
        self::ERROR_CODE_FAILED_WHEN_LOAD_ORDER_CARRIER            => '',
        self::ERROR_CODE_CART_ID_IS_INCORRECT                      => '',
        self::ERROR_CODE_CART_NOT_FOUND                            => '',
        self::ERROR_CODE_USER_ID_INCORRECT                         => '',
        self::ERROR_CODE_CUSTOMER_NOT_FOUND                        => '',
        self::ERROR_CODE_CUSTOMER_ID_IS_INCORRECT                  => '',
        self::ERROR_CODE_PRODUCT_ID_IS_INCORRECT                   => '',
        self::ERROR_CODE_PRODUCT_NOT_FOUND                         => '',
        self::ERROR_CODE_PRODUCT_NOT_ASSIGNED_TO_SHOP              => '',
        self::ERROR_CODE_COULD_NOT_CREATE_DATETIME_OBJECT          => '',
        self::ERROR_CODE_FAILED_TO_SET_SHOP_CONTEXT                => '',
        self::ERROR_CODE_BAD_TOKEN                                 => '',
        self::ERROR_CODE_AUTH_ERROR                                => '',
        self::ERROR_CODE_FORBIDDEN                                 => '',
        self::ERROR_CODE_UNKNOWN_REQUEST                           => '',
        self::ERROR_CODE_MODULE_DISABLED                           => '',
        self::ERROR_CODE_CONNECTION_ID_INCORRECT                   => '',
        self::ERROR_CODE_MISSING_PARAMETERS                        => '',
        self::ERROR_CODE_CANNOT_DELETE_PUSH_CONFIG                 => '',
        self::ERROR_CODE_DATA_NOT_UPDATED                          => '',
        self::ERROR_CODE_UNKNOWN                                   => '',
        self::ERROR_PRODUCT_ID_IS_INCORRECT                        => '',
        self::ERROR_PRODUCT_NOT_FOUND                              => '',
        self::ERROR_COMBINATION_NOT_FOUND                              => '',
        self::ERROR_PRODUCT_NOT_ASSIGNED_TO_SHOP                   => '',
        self::ERROR_PRODUCT_TYPE_IS_INCORRECT                      => '',
        self::ERROR_IMAGE_NOT_FOUND                                => '',
        self::ERROR_THE_ORDER_CARRIER_ID_IS_INVALID                => '',
        self::ERROR_THE_TRACKING_NUMBER_IS_INCORRECT               => '',
        self::ERROR_WHILE_UPDATING_ORDER_CARRIER                   => '',
        self::ERROR_WHILE_LOADING_REFRESHED_ORDER_CARRIER_OBJECT   => '',
        self::ERROR_THE_ORDER_CANNOT_BE_UPDATED                    => '',
        self::ERROR_THE_ORDER_CARRIER_CANNOT_BE_UPDATED            => '',
        self::ERROR_AN_ERROR_OCCURRED_WHILE_SENDING_AN_EMAIL_TO_THE_CUSTOMER => '',
        self::ERROR_AN_ERROR_OCCURRED_WHILE_TRIGGERING_ORDER_TRACKING_NUMBER_UPDATE_HOOK => ''
    );

    private $errorCode;

    public function __construct($errorCode, $message = '', $code = 0, Throwable $previous = null)
    {
        $this->errorCode = $errorCode;
        parent::__construct($message, $code, $previous);
    }
}
