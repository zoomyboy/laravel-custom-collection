<?php

namespace Zoomyboy\LaravelCustomCollection;

class CustomCollection extends \Illuminate\Database\Eloquent\Collection {
	/**
	 * Like the normal pluck function, but this method works recursively and with relations as well
	 *
	 * Plucker should be a string that contains names separated with dots. A dot selects the next layer of children
	 * Children can be a relation, a property or even an array key of a property
	 *
	 * @param string|DSV $plucker
	 *
	 * @return self
	 */
	public function pluckRec($plucker, $return = false) {
		if($return === false) {$return = collect([]);}
		$keys = explode('.', $plucker);
		$key = array_shift($keys);

		foreach($this->items as $item) {
			if ($key == '_') {
				$return = $return->merge($item->pluckRec(implode('.', $keys)));
			} elseif(is_string($item) || is_int($item) || is_numeric($item)) {
				$return = $return->push($item);
			} elseif(method_exists($item, $key)) {
				$collection = $item->{$key}()->get();
				$return = $return->merge($collection->pluckRec(implode('.', $keys)));
			} elseif(is_array($item)) {
				if (array_key_exists($key, $item)) {
					$collection = new self([0 => $item[$key]]);
					$return = $return->merge($collection->pluckRec(implode('.', $keys)));
				}
			} elseif($item->{$key} != null) {
				$collection = new self([0 => $item->{$key}]);
				$return = $return->merge($collection->pluckRec(implode('.', $keys)));
			}
		}
		return new self($return->values());
	}

	/**
	 * Creates an enumeration of all items in the collection (like "a, b and c")
	 *
	 * @return string
	 */
	public function enum() {
		return enum($this->items);
	}

	/**
	 * Checks a property of type date against a given date
	 * The property itself should be a carbon instance
	 *
	 * @param string $prop
	 * @param int $param The Day (1-31, without leading 0)
	 *
	 * @return self
	 */
	public function whereDay($prop, $param) {
		return $this->filter(function($item) use ($prop, $param) {
			return $item->{$prop}->day == $param;
		});
	}

	/**
	 * Checks a property of type date against a given month
	 * The property itself should be a carbon instance
	 *
	 * @param string $prop
	 * @param int $param The month (1-12, without leading 0)
	 *
	 * @return self
	 */
	public function whereMonth($prop, $param) {
		return $this->filter(function($item) use ($prop, $param) {
			return $item->{$prop}->month == $param;
		});
	}

	/**
	 * Checks a property of type date against a given year
	 * The property itself should be a carbon instance
	 *
	 * @param string $prop
	 * @param int $param The year (4 digits, e.g. 2012)
	 *
	 * @return self
	 */
	public function whereYear($prop, $param) {
		return $this->filter(function($item) use ($prop, $param) {
			return $item->{$prop}->year == $param;
		});
	}
}
