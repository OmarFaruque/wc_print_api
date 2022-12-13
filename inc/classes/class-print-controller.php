<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * PRINT_Controller class.
 * 
 * @author Omar faruque <ronymaha@gmail.com>
 *
 */

//  Necessary component 


class PRINT_Controller
{

    public static $bareerToken;

    public static $guzzleClient;

    public static $printProducts;

    /**
     * Initial callback function for BCDN_Controller class
     * Load all hook for upload and download Bunny CDN
     * 
     * @access  public 
     * 
     */
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'print_load_dependant_classes'));
    }


    /**
     * Load plugin necessary classes
     * @access  public 
     * 
     */
    public function print_load_dependant_classes()
    {
        require_once(PRINT_PATH . DIRECTORY_SEPARATOR . 'vendor/autoload.php');

        $this->_initial_config();
        new PRINT_Settings();
    }


    /**
     * get bareer token 
     * 
     * @return token
     */
    function _get_bareerToken()
    {
        if (!PRINT_Settings::_get_option('print_token'))
            return;

        $token = false;
        if (!get_transient('print_token')) {
            $client = self::$guzzleClient;
            $response = $client->request('POST', PRINT_API_URL . 'login', [
                'body' => '{"credentials":{"username":"info@prezu.nl","password":"#kFa6MB39Z#5"}}',
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Bearer ' . PRINT_Settings::_get_option('print_token'),
                    'content-type' => 'application/json',
                ],
            ]);
            if ($response->getStatusCode() == 200) {
                $token = $response->getBody();
                $token = (string) $token;
            }
        } else {
            $token = get_transient('print_token');
        }

        return $token;
    }


    public static function print_get_product_lists(){
        $token = json_decode(self::$bareerToken);
        $response = self::$guzzleClient->request('GET', PRINT_API_URL . 'products', [
            'headers' => [
              'accept' => 'application/json',
              'authorization' => 'Bearer '.$token.'',
            ],
          ]);

        $res = $response->getBody();
        $res = (string) $res;
        return json_decode($res);
    }

    /**
     * initial callback function for set some default data
     * 
     * @return void
     */
    public function _initial_config()
    {

        self::$guzzleClient = new \GuzzleHttp\Client();
        self::$bareerToken = $this->_get_bareerToken();
        self::$printProducts = self::print_get_product_lists();

    }
}