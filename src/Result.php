<?php

namespace vaino78\Paralleli;

use \Exception;

class Result
{
	/**
	 * @private array
	 */
	private $item;

	/**
	 * @private array
	 */
	private $positions = array();

	/**
	 * @private int
	 */
	private $max_length;

	/**
	 * @private int
	 */
	private $similar = 0;

	/**
	 * @param int $length
	 * @param array|null $item
	 */
	function __construct($length, $item = null)
	{
		$this->max_length = $length;
		if($item)
			$this->item = $item;
	}

	public function position($position, $value)
	{
		if(array_key_exists($position, $this->positions))
		{
			throw new Exception(sprintf('Position %u is already set', $position));
		}

		if($position >= $this->max_length)
		{
			throw new Exception(sprintf('Incorrect position of %u', $position));
		}

		$this->positions[$position] = $value;
	}

	public function getPositions()
	{
		return array_keys($this->positions);
	}

	public function getPosition($position)
	{
		return isset($this->positions[$position]) ? $this->positions[$position] : null;
	}

	public function setItem($item)
	{
		if(!empty($this->item))
		{
			throw Exception('Item key is already set');
		}

		$this->item = $item;
	}

	public function cost()
	{
		return count($this->positions);
	}

	public function getLength()
	{
		return $this->max_length;
	}

	public function getItem()
	{
		return $this->item;
	}

	/**
	 * @param int $position
	 * @return boolean
	 */
	public function isSimilar($position)
	{
		return !!($this->similar & $this->posToMask($position));
	}

	/**
	 * @param int $position
	 * @param int $similar
	 */
	public function setSimilar($position, $similar)
	{
		$this->similar = ($similar)
			? ($this->similar | $this->posToMask($position))
			: ($this->similar & ~$this->posToMask($position));
	}

	/**
	 * @private
	 * @param int $position
	 * @return int
	 */
	private function posToMask($position)
	{
		return pow(2, $position);
	}
}