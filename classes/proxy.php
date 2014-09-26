<?php

class Proxy {

	protected $_user_agent = 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36';

	public function connect($url, $additional_options = array())
	{
		do
		{
			$response = NULL;
			$success = FALSE;

			try
			{
				$proxy = new Model_Proxy;
				$proxy->find_oldest_used();
				$response = $this->do_request($proxy->get_full_host(), $url, $proxy->get_curl_auth(), $additional_options);
				$success = TRUE;
				$proxy->mark_used(TRUE, NULL, 'Success');
			}
			catch(Kohana_Request_Exception $e)
			{
				$proxy->mark_used(False, Date::HOUR, $e->getMessage());
			}
		}
		while($success === FALSE || $proxy->loaded() === FALSE);

		if($success === FALSE)
		{
			throw new Proxy_Exception('Could not get a response. Proxy list could be exhausted or a problem with target url');
		}

		return $response;
	}

	protected function do_request($proxy_url, $url, $username_password = NULL, $additional_options = array())
	{
		$options = $additional_options +
			array(CURLOPT_PROXY => $proxy_url,CURLOPT_PROXYUSERPWD => $username_password, CURLOPT_FAILONERROR => TRUE,
				  CURLOPT_USERAGENT => $this->_user_agent, CURLOPT_FOLLOWLOCATION => TRUE);

		$request = Request::factory($url);

		$request->client()->options($options);

		return $request;
	}
}