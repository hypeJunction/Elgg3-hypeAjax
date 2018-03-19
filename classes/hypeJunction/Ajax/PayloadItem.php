<?php
/**
 *
 */

namespace hypeJunction\Ajax;

class PayloadItem implements \Serializable {

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
	 * Serializes event object for database storage
	 * @return string
	 */
	public function serialize() {
		$data = new \stdClass();
		if ($this->item instanceof \ElggData) {
			if ($this->item instanceof \ElggEntity) {
				$data->item_id = $this->item->guid;
			} else {
				$data->item_id = $this->item->id;
			}
			$data->item_type = $this->item->getType();
			$data->item_subtype = $this->item->getSubtype();
		} else {
			$data->item = $this->item;
		}

		return serialize($data);
	}

	/**
	 * Unserializes the event object stored in the database
	 *
	 * @param string $serialized Serialized string
	 * @return string
	 */
	public function unserialize($serialized) {
		$data = unserialize($serialized);

		if (isset($data->item_id) && isset($data->item_type)) {
			switch ($data->item_type) {
				case 'object' :
				case 'user' :
				case 'group' :
				case 'site' :
					$this->item = get_entity($data->item_id);
					break;
				case 'annotation' :
					$this->item = elgg_get_annotation_from_id($data->item_id);
					break;
				case 'metadata' :
					$this->item = elgg_get_metadata_from_id($data->item_id);
					break;
				case 'relationship' :
					$this->item = get_relationship($data->item_id);
			}
		} else {
			$this->item = $data->item;
		}
	}
}