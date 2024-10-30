<?php if (defined('MOBASSIS_KEY')) { ?>
    <div style="margin-bottom: 15px ;">
        <div style="display: inline-block; margin-right: 18px; float: right">
                        <span style="margin-right: 15px">
                            <?php printf(__("Module version: <b>%s</b>", 'mobile-assistant-connector'), MobileAssistantConnector::PLUGIN_VERSION); ?>
                        </span>
            Useful links:
            <a href="https://wordpress.org/plugins/mobile-assistant-connector/" class="link" target="_blank"><?php _e('Check new version', 'mobile-assistant-connector'); ?></a> |
            <a href="https://support.emagicone.com/submit_ticket" class="link" target="_blank"><?php _e('Submit a ticket', 'mobile-assistant-connector'); ?></a> |
            <a href="http://mobile-store-assistant-help.emagicone.com/woocommerce-mobile-assistant-installation-instructions" class="link" target="_blank"><?php _e('Documentation', 'mobile-assistant-connector'); ?></a>
        </div>
    </div>
    <div class="card-body" style="margin-top: 30px ;">
        <!-- Modal -->
        <div class="modal fade" id="emoModalNewUser" role="dialog">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><?php _e('New user', 'mobile-assistant-connector'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <!-- Modal Body -->
                    <div class="modal-body">
                        <form id="add_new_user" data-async role="form" method="POST" action="<?php echo sanitize_textarea_field(plugins_url('/../../functions/ajax.php', __FILE__)) ?>">
                            <fieldset>
                                <input type="hidden" name="key" id="mobassistantconnector_key" value="<?php echo sanitize_textarea_field((hash('sha256', MOBASSIS_KEY  . AUTH_KEY))); ?>">
                                <input type="hidden" name="action" id="action" value="ema_callback">
                                <div class="alert alert-success" id="new-user-success">
                                    <?php _e('<strong>Success!</strong> User has been created', 'mobile-assistant-connector'); ?>
                                </div>
                                <div class="alert alert-warning" id="new-user-warning">
                                </div>
                                <div class="form-group row">
                                    <label  class="col-sm-3 col-form-label"
                                            for="inputLogin3"><?php _e('Login', 'mobile-assistant-connector'); ?></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="new_user_login"
                                               id="inputLogin3" placeholder="Login" autocomplete="off"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label"
                                           for="inputPassword3" ><?php _e('Password', 'mobile-assistant-connector'); ?></label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control" name="new_user_password"
                                               id="inputPassword3" placeholder="Password" autocomplete="off"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label"
                                           for="confirmPassword3" ><?php _e('Confirm password', 'mobile-assistant-connector'); ?></label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control" name="new_user_confirm_password"
                                               id="confirmPassword3" placeholder="Confirm password" autocomplete="off"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <input type="hidden" class="form-control" name="call_function"
                                           id="call_function" autocomplete="off" value="mac_add_user"/>
                                    <div class="col-sm-12">
                                        <button type="submit" class="btn btn-secondary btn-block" style="background-color: #005684;"><span style="color: white;"><?php _e('Add', 'mobile-assistant-connector'); ?></span></button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                <!--<div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php /*_e( 'Add', 'mobile-assistant-connector' ); */?></button>
                </div>-->
                </div>
            </div>
        </div>

        <!-- Modal HTML -->
        <div id="emoModalDeleteUser" class="modal fade">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><?php _e('Confirmation', 'mobile-assistant-connector'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p><?php _e('Do you want to delete current user?', 'mobile-assistant-connector'); ?></p>
                        <p class="text-warning">
                            <small><?php _e('All linked devices will be deleted too.', 'mobile-assistant-connector'); ?></small>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <form id="delete_user" data-async role="form" method="POST" action="<?php echo sanitize_textarea_field(plugins_url('/../../functions/ajax.php', __FILE__)) ?>">
                            <input type="hidden" name="key" id="mobassistantconnector_key" value="<?php echo sanitize_textarea_field((hash('sha256', MOBASSIS_KEY  . AUTH_KEY))); ?>">
                            <input type="hidden" name="action" id="action" value="ema_callback">
                            <input type="hidden" class="form-control" name="call_function"
                                   id="call_function" autocomplete="off" value="mac_delete_user"/>
                            <input type="hidden" class="form-control" name="mac_del_user_id"
                                   id="mac_del_user_id" autocomplete="off" value=""/>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('Cancel', 'mobile-assistant-connector'); ?></button>
                            <button type="submit" class="btn btn-danger"><?php _e( 'Delete', 'mobile-assistant-connector' ); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <form action="<?php echo sanitize_textarea_field(plugins_url('/../../functions/ajax.php', __FILE__)) ?>" method="post" enctype="multipart/form-data" id="form_mobassist">
            <input type="hidden" name="save_continue" id="save_continue" value="0">
            <input type="hidden" name="action" id="action" value="ema_callback">
            <input type="hidden" name="bulk_actions" id="bulk_actions" value="0">
            <input type="hidden" name="mobassistantconnector_base_url" id="mobassistantconnector_base_url" value="<?php print(get_site_url()); ?>">
            <input type="hidden" class="form-control" name="call_function" id="call_function" autocomplete="off" value="mac_save_user"/>
            <input type="hidden" class="form-control" name="mac_save_user_id" id="mac_save_user_id" autocomplete="off" value=""/>
            <input type="hidden" name="mobassistantconnector_key" id="mobassistantconnector_key" value="<?php echo sanitize_textarea_field((hash('sha256', MOBASSIS_KEY  . AUTH_KEY))); ?>">
            <input type="hidden" name="key" id="sec_key" value="<?php echo sanitize_textarea_field((hash('sha256', MOBASSIS_KEY  . AUTH_KEY))); ?>">


            <div class="alert alert-success collapse" role="alert" id="save-user-success">
                <?php _e('<strong>Success!</strong> User has been saved.', 'mobile-assistant-connector'); ?>
            </div>
            <div class="alert alert-warning collapse" id="save-user-warning"></div>
            <div class="alert alert-warning collapse" id="default-user-credentials">
                <?php _e('<strong>Warning!</strong>&nbspMobile Assistant Connector: Change default login credentials to make your connection secure!', 'mobile-assistant-connector'); ?>
            </div>
            <div class="alert alert-warning collapse" id="ema-system-warnings">
                <?php _e('<strong>Warning!</strong>&nbspMobile Assistant Connector:', 'mobile-assistant-connector'); ?>
            </div>

            <div class="form-row">
            <div class="col-sm-2" style="font-weight: bold;">
                <span class="col-form-label"><?php _e('Users:', 'mobile-assistant-connector'); ?></span><hr />
                <ul class="nav flex-column nav-pills" id="users">
                    <li id="user-add" class ="nav-item" style="cursor:pointer;"  data-toggle="modal" data-target="#emoModalNewUser"><a class="nav-link">
                            <img src="<?php echo sanitize_textarea_field(plugins_url('/../../images/add.png', __FILE__)) ?>" title="Add user"><span style="color:green;"> <?php _e( 'Add user ', 'mobile-assistant-connector' ); ?></span></a>
                    </li>
                </ul>
                <hr>

                <div style="margin-top: 25px; text-align: center;">
                    <span><?php _e('Get the App from Google Play', 'mobile-assistant-connector'); ?></span>
                    <a class="ma_play" href="https://play.google.com/store/apps/details?id=com.emagicone.assistant.wooCommerce" target="_blank">
                        <div id="mobassist_app_url_qr" style="margin-top: 7px; margin-bottom: 4px; display: inline-block;">
                            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAACWAQMAAAAGz+OhAAAABlBMVEX///8AAABVwtN+AAAB90lEQVRIia2WMY7jMAxFKahwN7pAIF1jCgG60pTp7C7lXkmAL2IjF1A6F4b/fsqzu1Nmw6iwgFfI1OcnKRHDSsDuqwxbQHDrCqCaWBTJZd54cmgtukW8kaUje9TrdQQkujtmOyvcRwjjXd/CGDVuaOGxvoFRg5wHYEL7SOtfXZ5hmo+cP6/Xr9YeP3L0KjtXuU1fobnjp2teYwlHwf453ibBEeMlZxuLDpVb99+HWy5ltzG3xDJjk2EKEoBFc21hktbqZ/qPdnGrCiwmlnis5mWcGv9w+vlZ5rD42r2GwNMvXu9rYOmQ7Hf1X2sOR5rVQwYmaeGx9EsIaGm5ZG9kUUrN6pfW+IuugYlJ0i+DboHJ7hpYGNtbmWf1C8QdDqrBs4x77X2NfZK+S9XbWDoSZo0wCOjd+5mj11mMbmY+MI1gX1vOWjWxyPJU+Zo8mJC9VBNjPTHkcttCaBTk1MXCDtZ+/fbLQr/AxlKvN+Gc+eeXZ1lMK6rfBq0tXv7UwMRYW77SLqE92IbOmWxg4vaCOty0N+EcMxamK+eyqQbs42e5GZjOwcLeNAk1cOu9lmpiOqc54ycWCN9IzNf/MH1v0Bs8UGur15mV5e4XhIPx9feakRVswyZ9xl+kGJm+/1BHzmQQqndMrOdjpl9+UVX2uj/vnFfZm9dvYSY9Q2xPNV0AAAAASUVORK5CYII=" style="display: block;">
                        </div><br>
                        <span><?php _e('Click or use your device camera to read the qr-code', 'mobile-assistant-connector'); ?></span>
                    </a>
                </div>
            </div>

            <div class="col-sm-10">
                <div class="tab-content">
                    <?php $user_row = 1; $active_user_row = 1; ?>
                    <div class="tab-pane container active" id="tab-user<?php /*echo esc_html($user['user_id']);*/ ?>">
                        <input type="hidden" name="user[user_id]" value="<?php /*echo esc_html($user['user_id']);*/ ?>" />

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label" for="input-status"><?php _e('Status:', 'mobile-assistant-connector'); ?></label>
                            <div class="col-sm-10">
                                <select name="user[status]" id="input-status" class="form-control">
                                    <option value="1"><?php _e('Enabled', 'mobile-assistant-connector'); ?></option>
                                    <option value="0""><?php _e('Disabled', 'mobile-assistant-connector'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row required">
                            <label class="col-sm-2 col-form-label" for="mobassist_login<?php /*echo esc_html($user_row)*/; ?>"><span data-toggle="tooltip" title="Login"><?php _e('Login', 'mobile-assistant-connector'); ?></span></label>
                            <div class="col-sm-10">
                                <!--                                <input type="hidden" id="mobassist_login_old--><?php ///*echo esc_html($user_row);*/ ?><!--" value="--><?php ///*echo esc_html($user['username']);*/ ?><!--"/>-->
                                <input type="text" id="mobassist_login<?php /*echo esc_html($user_row);*/ ?>" class="form-control mobassist_login" data-user_row="" name="user[username]" value="<?php /*echo esc_html($user['username']);*/ ?>" autocomplete="off" placeholder="<?php /*echo esc_html($entry_login);*/ ?>" <?php _e('required', 'mobile-assistant-connector'); ?> />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label" for="mobassist_pass"><span data-toggle="tooltip" title="Password"><?php _e('Password', 'mobile-assistant-connector'); ?></span></label>
                            <div class="col-sm-10">
                                <!--                                <input type="hidden" id="mobassist_pass_old--><?php ///*echo esc_html($user_row); */?><!--" value="--><?php ///*echo esc_html($user['password']);*/ ?><!--"/>-->
                                <input type="password" id="mobassist_pass" class="form-control mobassist_pass" data-user_row="" name="user[password]" value="<?php /*echo esc_html($user['password']);*/ ?>" autocomplete="off" placeholder="<?php /*echo esc_html($entry_pass);*/ ?>" />
                            </div>
                        </div>

                        <div class="form-group row" style="border-top: 1px solid #eee;">
                            <label class="col-sm-2 col-form-label" for="mobassist_qr"><span data-toggle="tooltip" title="QR Code (configuration)"><?php _e('QR Code (configuration)', 'mobile-assistant-connector'); ?></span></label>
                            <div class="col-sm-10">
                                <div style="position: relative; width: 250px; margin-top: 10px;">
                                    <div id="mobassist_qr_code" class="qr-code"><?php _e('QR Code (configuration)', 'mobile-assistant-connector'); ?></div>
                                    <div id="mobassist_qr_code_changed" style="display: none; z-index: 1000; text-align: center; position: absolute; top: 0; left: 0; height: 100%;">
                                        <div style="position: relative; width: 100%; height: 100%;">
                                            <div style="background: #fff; opacity: 0.9; position: absolute; height: 100%; width: 100%">&nbsp;</div>
                                            <div style="font-size: 16px; color: #DF0101; width: 100%; text-align: center; padding-top: 45px; position: absolute; font-weight: bold;"><?php _e('Login details have been changed.<br>Save changes for code to be regenerated', 'mobile-assistant-connector'); ?></div>
                                        </div>
                                    </div>
                                    <span id="mobassistantconnector_qr_code_url"><a id="qr_code_url" href="" target="_blank"><?php _e('URL to share current QR-code', 'mobile-assistant-connector'); ?></a></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row" style="border-top: 1px solid #eee;">
                            <label class="col-sm-2 col-form-label"><?php _e('Permissions', 'mobile-assistant-connector'); ?></label>
                            <div class="col-sm-10 perms_group" id="user_permissions">
                                <div class="perms_group"><?php _e('Push notification', 'mobile-assistant-connector'); ?><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_push_new_order" name="user_allowed_actions[push_notification_settings_new_order]" class="perms" value="0"/> <?php _e('New order created', 'mobile-assistant-connector' ); ?></label><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_push_order_status_changed" name="user_allowed_actions[push_notification_settings_order_statuses]" class="perms" value="0"/> <?php _e('Order status changed', 'mobile-assistant-connector' ); ?></label><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_push_new_customer" name="user_allowed_actions[push_notification_settings_new_customer]" class="perms" value="0"/> <?php _e('New customer created', 'mobile-assistant-connector' ); ?></label><br/>
                                </div>
                                <br/>
                                <div class="perms_group"><?php _e('Store statistics', 'mobile-assistant-connector'); ?><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_store_stats" name="user_allowed_actions[store_stats]" class="perms" value="0" > <?php _e('View store statistics', 'mobile-assistant-connector'); ?></label><br/>
                                </div>
                                <br/>
                                <div class="perms_group"><?php _e('Orders', 'mobile-assistant-connector'); ?><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_order_list" name="user_allowed_actions[orders_list]" data-user_row="" class="perms perm_order_list" value="0" > <?php _e('View order list', 'mobile-assistant-connector'); ?></label><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_order_details" name="user_allowed_actions[order_details]" class="perms perm_order_list_child" value="0" > <?php _e('View order details', 'mobile-assistant-connector'); ?></label><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_order_status_updating" name="user_allowed_actions[update_order_status]" class="perms perm_order_list_child" value="0" > <?php _e('Change order status', 'mobile-assistant-connector'); ?></label><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_order_details_pdf" name="user_allowed_actions[order_details_pdf]" class="perms perm_order_list_child" value="0" > <?php _e('Download order invoice PDF', 'mobile-assistant-connector'); ?></label><br/>
                                </div>
                                <br/>
                                <div class="perms_group"><?php _e('Customers', 'mobile-assistant-connector'); ?><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_customers_list" name="user_allowed_actions[customers_list]" data-user_row="" class="perms perm_customer_list" value="0" > <?php _e('View customer list', 'mobile-assistant-connector'); ?></label><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_customer_details" name="user_allowed_actions[customer_details]" class="perms perm_customer_list_child" value="0" > <?php _e('View customer details', 'mobile-assistant-connector'); ?></label><br/>
                                </div>
                                <br/>
                                <div class="perms_group"><?php _e('Products', 'mobile-assistant-connector'); ?><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_products_list" name="user_allowed_actions[products_list]" data-user_row="" class="perms perm_product_list" value="0" > <?php _e('View product list', 'mobile-assistant-connector'); ?></label><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_product_details" name="user_allowed_actions[product_details]" class="perms perm_product_list_child" value="0" > <?php _e('View product details', 'mobile-assistant-connector'); ?></label><br/>
                                    <label class="perms_label"><input type="checkbox" id="user_allowed_actions_product_edit" name="user_allowed_actions[product_edit]" class="perms perm_product_list_child" value="0" > <?php _e('Product editing and adding', 'mobile-assistant-connector'); ?></label><br/>
                                </div>
                            </div>
                        </div>


                        <div class="form-group row"  style="border-top: 1px solid #eee;">
                            <div class="col-sm-10">
                                <div class="tablenav bottom">
                                    <button type="button" data-href="/delete.php?id=23" data-toggle="modal" data-target="#emoModalDeleteUser" class="btn btn-danger btn-sm float-left launch-modal" id="delete-user" style="">
                                        <span class=""><img src="<?php echo sanitize_textarea_field(plugins_url('/../../images/trash.png', __FILE__)) ?>" title="Delete user"></span>&nbsp;&nbsp;<?php _e('Delete User', 'mobile-assistant-connector'); ?></button>
                                    <!--                                    --><?php //submit_button('Delete User', 'delete', 'submit-form', false); ?>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="tablenav bottom float-right">
                                    <?php submit_button(__('Save User Details', 'mobile-assistant-connector'), 'primary', 'submit-form', false); ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}