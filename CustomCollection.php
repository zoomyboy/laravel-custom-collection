<?php

namespace Zoomyboy;

class CustomCollection extends \Illuminate\Database\Eloquent\Collection {
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
	public function enum() {
		return enum($this->items);
	}
}
