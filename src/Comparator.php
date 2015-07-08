<?php

namespace vaino78\Paralleli;

use \Exception;

class Comparator
{
	private $items = array();

	public function add(Index $index)
	{
		$this->items[] = $index;
	}

	public function result()
	{
		$count_map = $this->countMap();
		$count_max = max(array_keys($count_map));

		if($count_max <= 1)
		{
			throw new Exception('No parallel items in given indexes');
		}

		$items_cost_map = array();
		foreach($count_map as $cost => $keys)
		{
			if($cost <= 1)
				break;

			$items_cost_map[$cost] = array();
			foreach($keys as $key)
			{
				$items_cost_map[$cost] = $this->getResultItems($key);
			}
		}

		$result = array();
		for($i = max(array_keys($items_cost_map)); $i > 1; $i--)
		{
			if(empty($items_cost_map[$i]))
				continue;

			foreach($items_cost_map[$i] as $item)
			{
				try
				{
					$i = $this->getPositionToInsert($result, $item);
					array_splice($result, $i, 0, array($item));
				}
				catch(IntegrityException $e)
				{
					$splited_items = $e->getSplitedItems();
					foreach($splited_items as $splited)
					{
						$split_cost = $splited->cost();
						if($split_cost <= 1)
							continue;

						if(!isset($items_cost_map[$split_cost]))
							$items_cost_map[$split_cost] = array();
						$items_cost_map[$split_cost][] = $splited;
					}
				}
			}
		}

		return $result;
	}

	private function countMap()
	{
		$keys = array();
		foreach($this->items as $item)
		{
			$keys = array_merge($keys, $item->getItems());
		}

		$count_values = array_count_values($keys);
		$count_map = array();
		foreach($count_values as $value => $count)
		{
			if(!isset($count_map[$count]))
			{
				$count_map[$count] = array();
			}

			$count_map[$count][] = $value;
		}

		krsort($count_map);
		return $count_map;
	}

	private function getResultItem($item)
	{
		$result = new Result(count($this->items), $item);
		foreach($this->items as $i => $index)
		{
			$pos = $index->getPosition($item);
			if(!is_null($pos))
			{
				$result->position($i, $pos);
			}
		}

		return $result;
	}

	private function getPositionToInsert(&$arr, $resultItem)
	{
		if(empty($arr))
			return 0;

		$left = 0;
		$right = count($arr) - 1;

		do
		{
			$half = $this->getHalfPosition($left, $right);
			$cmp = $this->itemCmp($arr[$half], $resultItem);
			if($cmp > 0)
			{
				$right = $half;
			}
			elseif($cmp < 0)
			{
				$left = $half + 1;
			}
		}
		while($half > $left);

		return $left;
	}

	private function itemCmp($a, $b)
	{
		$intersect = ($a->cost() > $b->cost()) 
			? array_intersect($a->getPositions(), $b->getPositions())
			: array_intersect($b->getPositions(), $a->getPositions());

		if(empty($intersect))
			return 0;

		$result = array();
		foreach($intersect as $position)
		{
			$result[$position] = ($a->getPosition($position) > $b->getPosition($position))
				? 1
				: -1;
		}

		$min = min($result);
		if($min == max($result))
			return $min;

		throw new IntegrityException($b, $result);
	}

	private function getHalfPosition($left, $right)
	{
		if($left == $right)
			return $left;
		return floor(($right - $left) / 2) + $left;
	}
}