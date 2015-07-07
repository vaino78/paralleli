<?php

namespace vaino78\Paralleli;

use \Exception;

class Index {

	private $data = array();

	function __construct($index = '', $separator = '')
	{
		if(!empty($index))
		{
			$index = (empty($separator)) 
				? str_split($index) 
				: explode($separator, $index);
			foreach($index as $pos => $item)
			{
				$this->add($item, $pos);
			}
		}
	}

	public function add($item, $position)
	{
		if($this->itemExists($item))
		{
			throw new Exception('Item is already in the index');
		}

		if(in_array($position, $this->data))
		{
			throw new Exception('Position is alerady set');
		}

		$this->data[$item] = $position;
	}

	public function itemExists($item) 
	{
		return array_key_exists($item, $this->data);
	}

	public function getPosition($item)
	{
		return $this->itemExists($item) ? $this->data[$item] : null;
	}

	public function getItems()
	{
		return array_keys($this->data);
	}
}