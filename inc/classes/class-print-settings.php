<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * PRINT_Settings class.
 *
 */

if (!class_exists('PRINT_Settings')) {
    class PRINT_Settings
    {

        var $screenid = array();

        var $token;

        

        /**
         * Default callback function and load all settings for backend
         * @access  public
         */
        public function __construct()
        {
            $this->token = PRINT_TOKEN;
            add_action('admin_menu',  array($this, 'print_admin_settings_menu'));
            add_action('admin_init', __CLASS__ . '::print_settings_init');

            //Register assets
            add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'), 10, 1);
            add_action('admin_enqueue_scripts', array($this, 'adminEnqueueStyles'), 10, 1);

        }


        public static function _get_option($option_name = false)
        {
            $options = get_option('print_options');

            if ($option_name && isset($options[$option_name]))
                return $options[$option_name];

            if ($option_name)
                return $options;

            return false;
        }


        /**
         * Register all styles for settings page 
         * 
         * @return void
         */
        public function adminEnqueueStyles()
        {
            $pagescreen = get_current_screen();

            if (!in_array($pagescreen->id, $this->screenid, true))
                return;

            wp_register_style($this->token . '_selectCSS', esc_url(plugin_dir_url(PRINT_FILE) . 'assets/css/select2.min.css'), array(), time(), 'all');
            wp_register_style($this->token . '_printCSS', esc_url(plugin_dir_url(PRINT_FILE) . 'assets/css/backend.css'), array(), time(), 'all');
            wp_enqueue_style($this->token . '_selectCSS');
            wp_enqueue_style($this->token . '_printCSS');
        }

        /**
         * Load admin Javascript.
         *
         * @access public
         */
        public function adminEnqueueScripts()
        {
            $pagescreen = get_current_screen();

            if (!in_array($pagescreen->id, $this->screenid, true))
                return;

            wp_enqueue_script($this->token . '_select2', esc_url(plugin_dir_url(PRINT_FILE) . 'assets/js/select2.min.js'), array(), time(), true);
            wp_enqueue_script($this->token . '_scripts', esc_url(plugin_dir_url(PRINT_FILE) . 'assets/js/settings.js'), array($this->token . '_select2'), time(), true);
            
        }


        /**
         * Get settings 
         */
        public static function get_option($name = false)
        {
            $options = get_option('print_options');
            if ($name)
                return isset($options[$name]) ? $options[$name] : false;

            return $options;
        }

        /**
         * Bunny CDN settings page fields array
         * @access  public
         * @return array
         */
        public static function print_fields()
        {
            return array(
                array(
                    'label' => __('Username', 'wc-print'),
                    'label_for' => 'print_username',
                    'type' => 'text',
                    'default' => 'info@prezu.nl',
                    'desc' => esc_html('Print api username should be set here.', 'wc-print')
                ),
                array(
                    'label' => __('Password', 'wc-print'),
                    'label_for' => 'print_password',
                    'type' => 'password',
                    'default' => '#kFa6MB39Z#5',
                    'desc' => esc_html('Print api password should be set here.', 'wc-print')
                ),
                array(
                    'label' => __('Bearer Token', 'wc-print'),
                    'label_for' => 'print_token',
                    'type' => 'text',
                    'default' => '',
                    'desc' => esc_html('Print api initial Bearer Token should be set here.', 'wc-print')
                ),
                array(
                    'label' => __('Products', 'wc-print'),
                    'label_for' => 'print_product',
                    'type' => 'multiple-select',
                    'options' => PRINT_Controller::$printProducts,
                    'desc' => esc_html('Print api password should be set here.', 'wc-print')
                ),
                array(
                    'label' => __('Allow Cronjob', 'wc-print'),
                    'label_for' => 'print_enable_cronjob',
                    'type' => 'checkbox',
                    'default' => false,
                    'desc' => esc_html('If checked, bunny.net Url Token in url as parameter for return asset.', 'wc-print')
                ),
                array(
                    'label' => __('Authentication Key', 'wc-print'),
                    'label_for' => 'print_url_authentication_key',
                    'type' => 'text',
                    'default' => '',
                    'desc' => esc_html('Token authentication allows you to secure file URLs with a token and expiry date. If enabled, requests without a valid token and expiry timestamp will be rejected.', 'wc-print')
                ),


            );
        }


        /**
         * Register settings for bcdn admin submanu page located in settings menu
         * @access  public
         */
        public static function print_settings_init()
        {
            // Register a new setting for "print-settings" page.
            register_setting('print-settings', 'print_options');

            add_settings_section(
                'print_setting_section',
                __('WC PRINT API Settings.', 'wc-print'), __CLASS__ . '::print_setting_section_callback',
                'print-settings'
            );

            foreach (self::print_fields() as $s_fiel) {
                $section = 'print_setting_section';

                add_settings_field(
                    'print_field_' . $s_fiel['label_for'], // As of WP 4.6 this value is used only internally.
                    // Use $args' label_for to populate the id inside the callback.
                    $s_fiel['label'],
                    __CLASS__ . '::print_field_callback',
                    'print-settings',
                    $section,
                    array(
                        'label_for' => $s_fiel['label_for'],
                        'class' => 'print_row',
                        'type' => $s_fiel['type'],
                        'desc' => $s_fiel['desc'],
                        'default' => isset($s_fiel['default']) ? $s_fiel['default'] : false,
                        'options' => isset($s_fiel['options']) ? $s_fiel['options'] : false
                    )
                );
            }

        }


        /**
         * Developers section callback function.
         *
         * @param array $args  The settings array, defining title, id, callback.
         */
        public static function print_setting_section_callback($args)
        {
?>
            <p id="<?php echo esc_attr($args['id']); ?>">
                <?php echo sprintf(__('Get PRINT API settings from %sprint.com%s and set below fields.', 'wc-print'), '<a href="https://www.print.com/en/api/">', '</a>'); ?>
            </p>
            <?php
        }



        /**
         * Create Admin menu inside settings main menu 
         * @access  public 
         */
        public function print_admin_settings_menu()
        {
            $this->screenid[] = add_submenu_page(
                'options-general.php',
                __('Print API Settings', 'wc-print'),
                __('Print API Settings', 'wc-print'),
                'administrator',
                'print-api-settings',
                array($this, 'print_settings_callback'),
                55
            );
        }


        /**
         * Process Print product and store to woocommerce database based on options
         * 
         * @param $args array
         * 
         * @return bolian (true/false)
         * 
         */
        private function printProductToWooCommerce($args) 
        {
            $token = json_decode(PRINT_Controller::$bareerToken);

            $products = array();
            foreach($args as $slug):
                $response = PRINT_Controller::$guzzleClient->request('GET', 'https://api.print.com/products/' . $slug, [
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Bearer ' . $token,
                ],
                ]);
                $product =  json_decode($response->getBody());
                array_push($products, $product);

                //Process to insert WooCommerce DB 
                $wcProduct = new WC_Product_Simple();
                $wcProduct->set_name($product->titleSingle);
                $wcProduct->set_slug($product->sku);
                // $wcProduct->save();
            endforeach;

            
            echo 'product lists <br/><pre>';
            print_r($products);
            echo '</pre>';

        }



        /**
         * Bunny CDN admin setting page html
         * @access public
         */
        public function print_settings_callback()
        {
            // check user capabilities
            if (!current_user_can('manage_options')) {
                return;
            }

            
            $options = get_option('print_options');
            echo 'options <br/><pre>';
            print_r($options);
            echo '</pre>';


            $this->printProductToWooCommerce($options['print_product']);
            // add error/update messages

            // check if the user have submitted the settings
            // WordPress will add the "settings-updated" $_GET parameter to the url
            if (isset($_GET['settings-updated'])) {
                

            }
            include_once PRINT_PATH . DIRECTORY_SEPARATOR . 'temp/print-setting-page.php';
        }


        /**
         * Checkbox field for bunny cdn settings page
         * 
         * @access  public
         * $param array
         */
        public static function print_checkbox_fields($options, $args)
        {
            include_once PRINT_PATH . DIRECTORY_SEPARATOR . 'temp/print-checkbox.php';
        }


        public static function print_multiselect_fields($options, $args)
        {
            include_once PRINT_PATH . DIRECTORY_SEPARATOR . 'temp/product-dropdown.php';
        }


        /**
         * Default field for bunny cdn settings page
         * @access  public
         * $param array
         */
        public static function print_default_fields($options, $args)
        {
            
            include PRINT_PATH . DIRECTORY_SEPARATOR . 'temp/print-default-field.php';
        }


        /**
         * Pill field callbakc function.
         *
         * WordPress has magic interaction with the following keys: label_for, class.
         * - the "label_for" key value is used for the "for" attribute of the <label>.
         * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
         * Note: you can add custom key value pairs to be used inside your callbacks.
         *
         * @param array $args
         */
        public static function print_field_callback($args)
        {
            // Get the value of the setting we've registered with register_setting()
            $options = get_option('print_options');
            switch ($args['type']) {
                case 'checkbox':
                    self::print_checkbox_fields($options, $args);
                    break;

                case 'multiple-select':
                    self::print_multiselect_fields($options, $args);
                    break;
                default:
                    self::print_default_fields($options, $args);
            }
        }
    }
}