<?php

class BCDN_Filter
{
	var $baseUrl = null;
	var $cdnUrl = null;
	var $excludedPhrases = null;
	var $directories = null;
	var $disableForAdmin = null;
	var $allow_token_url = null;
	var $cdn_token_key = null;

	/**
		*Create a new BunnyCDNFilter object.

		* @param $baseUrl string 			- The base URL of the website that we will be looking to replace.
		* @param $cdnUrl string 			- The CDN url that will be used to replace the base URL.
		* @param $excludedPhrases array 	- An array of phrases that will be used to exclude specific URLs
		* @param $disableForAdmin booleab	- True if the CDN should be disabled while logged in as admin
	*/
	function __construct($baseUrl, $cdnUrl, $directories, $excludedPhrases, $disableForAdmin) 
	{
		$this->baseUrl = $baseUrl;
		$this->cdnUrl = $cdnUrl;
		$this->disableForAdmin = $disableForAdmin;
		$this->allow_token_url = BCDN_Settings::get_option('bcdn_enable_url_token');
		$this->cdn_token_key = BCDN_Settings::get_option('bcdn_url_authentication_key');
		
		// Prepare the excludes
		if(trim($excludedPhrases) != '')
		{
			$this->excludedPhrases = explode(',', $excludedPhrases);
			$this->excludedPhrases = array_map('trim', $this->excludedPhrases);
		}
		array_push($this->excludedPhrases, "]");
		array_push($this->excludedPhrases, "(");
		
		// Validate the directories
		if (trim($directories) == '') 
		{
			$directories = BUNNYCDN_DEFAULT_DIRECTORIES;
		}
		// Create the array
		$directoryArray = explode(',', $directories);
		if(count($directoryArray) > 0)
		{
			$directoryArray = array_map('trim', $directoryArray);
			$directoryArray = array_map('quotemeta', $directoryArray);
			$directoryArray = array_filter($directoryArray);
		}
		$this->directories = $directoryArray;
	}



	/**
	 * Token based url get from BunnyCDN
	 * @access	public
	 */
	protected function sign_bcdn_url($url, $securityKey, $expiration_time = 3600, $user_ip = NULL, $is_directory_token = false, $path_allowed = NULL, $countries_allowed = NULL, $countries_blocked = NULL, $referers_allowed = NULL)
	{    
		if(!is_null($countries_allowed))
		{
			$url .= (parse_url($url, PHP_URL_QUERY) == "") ? "?" : "&";
			$url .= "token_countries={$countries_allowed}";
		}
		if(!is_null($countries_blocked))
		{
			$url .= (parse_url($url, PHP_URL_QUERY) == "") ? "?" : "&";
			$url .= "token_countries_blocked={$countries_blocked}";
		}
		if(!is_null($referers_allowed))
		{
			$url .= (parse_url($url, PHP_URL_QUERY) == "") ? "?" : "&";
			$url .= "token_referer={$referers_allowed}";
		}
	
		$url_scheme = parse_url($url, PHP_URL_SCHEME);
		$url_host = parse_url($url, PHP_URL_HOST);
		$url_path = parse_url($url, PHP_URL_PATH);
		$url_query = parse_url($url, PHP_URL_QUERY);
	
	
		$parameters = array();
		parse_str($url_query, $parameters);
	
		// Check if the path is specified and ovewrite the default
		$signature_path = $url_path;
	
		if(!is_null($path_allowed))
		{
			$signature_path = $path_allowed;
			$parameters["token_path"] = $signature_path;
		}
	
		// Expiration time
		$expires = time() + $expiration_time; 
	
		// Construct the parameter data
		ksort($parameters); // Sort alphabetically, very important

		
			 
		$parameter_data = "";
		$parameter_data_url = "";
		if(sizeof($parameters) > 0)
		{
			foreach ($parameters as $key => $value) 
			{
				if(strlen($parameter_data) > 0)
					$parameter_data .= "&";
	
				$parameter_data_url .= "&";
	
				$parameter_data .= "{$key}=" . $value;
				$parameter_data_url .= "{$key}=" . urlencode($value); // URL encode everything but slashes for the URL data
			}
		}
	
		// Generate the toke
		$hashableBase = $securityKey.$signature_path.$expires;
	
		// If using IP validation
		if(!is_null($user_ip))
		{
			$hashableBase .= $user_ip;
		}
	
		$hashableBase .= $parameter_data;
	
		// Generate the token
		$token = hash('sha256', $hashableBase, true);
		$token = base64_encode($token);
		$token = strtr($token, '+/', '-_');
		$token = str_replace('=', '', $token); 
	
		if($is_directory_token)
		{
			return "{$url_scheme}://{$url_host}/bcdn_token={$token}&expires={$expires}{$parameter_data_url}{$url_path}";
		}
		else 
		{	
			return "{$url_scheme}://{$url_host}{$url_path}?token={$token}{$parameter_data_url}&expires={$expires}";
		}
	}

	

	/**
		* The rewrite method called during the rewrite preg_replace_callback call.
		* It validates and replaces the old base URL with the CDN url.
	*/
    protected function rewriteUrl($asset) 
	{

		// update_option('tests', array('assets' => $asset) );
		$foundUrl = $asset[0];
		

		// Don't rewrite URLs in the admin preview
		if(is_admin_bar_showing() && $this->disableForAdmin)
		{
			return $asset[0];
		}

		// If the URL contains an excluded phrase don't rewrite it
		foreach($this->excludedPhrases as $exclude)
		{
			if($exclude == '')
				continue;

			if(stristr($foundUrl, $exclude) != false)
				return $foundUrl;
		}
		

		// Check if image srcset, then return with local url
		$checkMultipleElement = explode(' ', $foundUrl);
		if(is_array($checkMultipleElement) && count($checkMultipleElement) > 1){
			return $foundUrl;
		}

		// If this is NOT a relative URL
		if (strstr($foundUrl, $this->baseUrl)) 
		{
			$return_url = str_replace($this->baseUrl, $this->cdnUrl, $foundUrl);
			//Authontication tokken 
			if($this->allow_token_url && !empty($this->cdn_token_key)){
				$return_url = $this->sign_bcdn_url($return_url, $this->cdn_token_key, 3600);
			}	

			return $return_url;
			
		}

		$return_url = $this->cdnUrl . $foundUrl;

		//Authontication tokken 
		if($this->allow_token_url && !empty($this->cdn_token_key)){
			$return_url = $this->sign_bcdn_url($return_url, $this->cdn_token_key, 3600);
		}

		return $return_url;
	}




	/**
	* Performs the actual rewrite logic
	*/
	protected function rewrite($html) 
	{
		
		// Prepare the included directories regex
		

		$directoriesRegex = implode('|', $this->directories);
		$regex = '#(?<=[(\"\'])(?:'. quotemeta($this->baseUrl) .')?/(?:((?:'.$directoriesRegex.')[^\"\')]+)|([^/\"\']+\.[^/\"\')]+))(?=[\"\')])#';
		return preg_replace_callback($regex, array(&$this, "rewriteUrl"), $html);
	}
	
	/**
		* Begins the rewrite process with the currently configured settings
	*/
	public function startRewrite()
	{
		
		ob_start(array($this,'rewrite'));
	}
}
