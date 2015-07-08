<?php

namespace vaino78\Paralleli;

use \Exception;

class IntegrityException extends Exception
{
	private $item;

	private $positions;

	function __construct($result, $positions)
	{
		$this->item = $result;
		$this->positions = $positions;

		parent::__construct('Data disintegrity');
	}

	public function getSplitedItems()
	{
		$sum = array();
		foreach($this->positions as $position => $cmp)
		{
			if(!isset($sum[$cmp]))
				$sum[$cmp] = array();
			$sum[$cmp][] = $position;
		}

		$result = [];
		foreach($sum as $positions)
		{
			$item = new Result($this->item->getLength(), $this->item->getItem());
			foreach($positions as $position)
			{
				$pos = $this->item->getPosition($position);
				if(!is_null($pos))
					$item->position($position, $pos);
			}
			$result[] = $item;
		}

		return $result;
	}
}