<?php

namespace hypeJunction\Ajax;

use Elgg\IntegrationTestCase;

/**
 * PayloadItem encodes/decodes values for transport between the deferred-
 * view client and server. Entities are reduced to id+type+subtype and
 * re-hydrated server-side; scalars and arrays round-trip through an
 * `item` envelope.
 */
class PayloadItemTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypeajax';
	}

	public function up() {}
	public function down() {}

	public function testEncodeScalarWrapsInItemEnvelope() {
		$encoded = PayloadItem::encode('hello');
		$decoded = json_decode($encoded);
		$this->assertIsObject($decoded);
		$this->assertSame('hello', $decoded->item);
	}

	public function testEncodeIntegerWrapsInItemEnvelope() {
		$encoded = PayloadItem::encode(42);
		$decoded = json_decode($encoded);
		$this->assertSame(42, $decoded->item);
	}

	public function testEncodeNullWrapsInItemEnvelope() {
		$encoded = PayloadItem::encode(null);
		$decoded = json_decode($encoded, true);
		$this->assertArrayHasKey('item', $decoded);
		$this->assertNull($decoded['item']);
	}

	public function testEncodeArrayWrapsInItemEnvelope() {
		$encoded = PayloadItem::encode([1, 2, 3]);
		$decoded = json_decode($encoded, true);
		$this->assertSame([1, 2, 3], $decoded['item']);
	}

	public function testEncodeEntityProducesIdTypeSubtypeTriple() {
		$obj = new \ElggObject();
		$obj->setSubtype('hypeajax_test_obj');
		$obj->save();
		try {
			$encoded = PayloadItem::encode($obj);
			$decoded = json_decode($encoded, true);
			$this->assertSame($obj->guid, $decoded['item_id']);
			$this->assertSame('object', $decoded['item_type']);
			$this->assertSame('hypeajax_test_obj', $decoded['item_subtype']);
			$this->assertArrayNotHasKey('item', $decoded);
		} finally {
			$obj->delete();
		}
	}

	public function testDecodeScalarRoundTrip() {
		$encoded = PayloadItem::encode('hello');
		$this->assertSame('hello', PayloadItem::decode($encoded));
	}

	public function testDecodeIntegerRoundTrip() {
		$encoded = PayloadItem::encode(42);
		$this->assertSame(42, PayloadItem::decode($encoded));
	}

	public function testDecodeArrayRoundTrip() {
		// Arrays come back as stdClass-decoded scalars under ->item, which
		// json_decode (default object mode) returns as an array literal.
		$encoded = PayloadItem::encode([1, 2, 3]);
		$decoded = PayloadItem::decode($encoded);
		$this->assertSame([1, 2, 3], $decoded);
	}

	public function testDecodeEntityRoundTrip() {
		$obj = new \ElggObject();
		$obj->setSubtype('hypeajax_test_obj');
		$obj->save();
		try {
			$encoded = PayloadItem::encode($obj);
			$decoded = PayloadItem::decode($encoded);
			$this->assertInstanceOf(\ElggObject::class, $decoded);
			$this->assertSame($obj->guid, $decoded->guid);
		} finally {
			$obj->delete();
		}
	}

	public function testDecodeReturnsNullForInvalidJson() {
		$this->assertNull(PayloadItem::decode('not-json{'));
	}

	public function testDecodeReturnsFalsyForMissingEntity() {
		// A non-existent guid encoded as an entity reference: get_entity()
		// returns false → decode propagates that.
		$encoded = json_encode([
			'item_id' => 99999999,
			'item_type' => 'object',
			'item_subtype' => 'nonexistent',
		]);
		$this->assertFalse((bool) PayloadItem::decode($encoded));
	}

	public function testDecodeReturnsNullForUnknownTypeWithoutItemKey() {
		// JSON with no item_id and no item key → ->item is null.
		$encoded = json_encode(['some_other_key' => 'value']);
		$this->assertNull(PayloadItem::decode($encoded));
	}
}
