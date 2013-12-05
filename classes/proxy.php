<?php

class Proxy {

	public function connect($url)
	{
		do
		{
			$response = NULL;
			$success = FALSE;
			$proxy = new Model_Proxy;
			$proxy->find_oldest_used();

			try
			{
				$response = $this->do_request($proxy->get_host(), $url);
				$success = TRUE;
			}
			catch(Kohana_Request_Exception $e)
			{
				$proxy->mark_used(False, Date::HOUR, $e->getMessage());
			}
		}
		while($proxy->loaded() === FALSE OR $success === TRUE);

		if($success === FALSE)
		{
			throw new Proxy_Exception('Could not get a response. Proxy list could be exhausted or a problem with target url');
		}

		return $response;
	}

	protected function do_request($proxy_url, $url)
	{
		$request = Request::factory($url);
		$request->client()->options(CURLOPT_PROXY, $proxy_url);
		return $request->execute()->body();
	}
}