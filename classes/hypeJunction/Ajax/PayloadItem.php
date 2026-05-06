<?php

namespace hypeJunction\Ajax;

/**
 * Encode and decode payload items for transport between the server and
 * the JS client. Elgg entities/data objects are reduced to id+type+subtype
 * triples so the receiver can re-hydrate them server-side without
 * round-tripping serialised PHP through the browser.
 */
class PayloadItem {

	/**
	 * @var mixed
	 */
	protected $item;

	/**
	 * Constructor
	 *
	 * @param mixed $item Item to serialize
	 */
	public function __construct($item = null) {
		$this->item = $item;
	}

	/**
	 * Encode an item to a JSON-safe transport string.
	 *
	 * ElggEntity/ElggData are represented by id+type+subtype so that the
	 * full object can be re-hydrated server-side without round-tripping PHP
	 * serialised objects through the browser.
	 *
	 * @param mixed $item Item to encode
	 *
	 * @return string
	 */
	public static function encode($item): string {
		$data = new \stdClass();
		if ($item instanceof \ElggData) {
			if ($item instanceof \ElggEntity) {
				$data->item_id = $item->guid;
			} else {
				$data->item_id = $item->id;
			}

			$data->item_type = $item->getType();
			$data->item_subtype = $item->getSubtype();
		} else {
			$data->item = $item;
		}

		return json_encode($data);
	}

	/**
	 * Decode a transport string produced by encode() and return the item.
	 *
	 * @param string $encoded Transport string produced by encode()
	 *
	 * @return mixed
	 */
	public static function decode(string $encoded) {
		$data = json_decode($encoded);
		if ($data === null) {
			return null;
		}

		if (isset($data->item_id) && isset($data->item_type)) {
			switch ($data->item_type) {
				case 'object':
				case 'user':
				case 'group':
				case 'site':
					return get_entity($data->item_id);
				case 'annotation':
					return elgg_get_annotation_from_id($data->item_id);
				case 'metadata':
					return elgg_get_metadata_from_id($data->item_id);
				case 'relationship':
					return get_relationship($data->item_id);
			}
		}

		return $data->item ?? null;
	}
}
