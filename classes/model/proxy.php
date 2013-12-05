<?php

class Model_Proxy extends ORM {

	const OK_STATUS = 1;

	const ERROR_STATUS = 9;

	const PERM_DISABLED_STATUS = 0;

	const TEMP_DISABLED_STATUS = 2;

	protected $_table_name = 'proxies';

	protected $_columns = array(
		'id'                     => array('type' => 'int'),
		'host'                   => array('type' => 'string'),
		'port'                   => array('type' => 'int'),
		'last_used'              => array('type' => 'string'),
		'connections_successful' => array('type' => 'int'),
		'connections_errors'     => array('type' => 'int'),
		'enable_time'            => array('type' => 'string'),
		'status'                 => array('type' => 'int')
	);

	public function temporarily_disable($time = NULL, $save = TRUE, $status_msg = NULL)
	{
		$this->status = self::TEMP_DISABLED_STATUS;
		$this->enable_time = strtotime('+' . $time.' seconds');

		if($save === TRUE)
		{
			$this->save();
		}
	}

	public function find_oldest_used()
	{
		return $this->where('status', '=', self::OK_STATUS)
					->order_by('last_used', 'ASC')
					->limit(1)
					->find();
	}

	public function mark_used($success = TRUE, $disable_time = NULL, $status_message = NULL)
	{
		if($success === TRUE)
		{
			$this->connection_successful++;
		}
		else
		{
			$this->connection_errors++;

			if($disable_time !== NULL)
			{
				$this->temporarily_disable($disable_time, FALSE, $status_message);
			}
		}

		$this->last_used = strtotime('Y-m-d h:i:s');

		$this->save();
	}

	public function get_host()
	{
		return $this->host;
	}
}