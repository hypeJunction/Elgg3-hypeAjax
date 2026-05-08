<?php

namespace hypeJunction\Ajax;

use Elgg\Event;
use Elgg\IntegrationTestCase;

/**
 * CapturePageContext is the elgg.data:page hook handler that pushes a
 * snapshot of the current request context onto the client-side data
 * blob. The snapshot is later echoed back when /data endpoints are
 * called so the server can rebuild context and verify request integrity.
 */
class CapturePageContextTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypeajax';
	}

	public function up() {}
	public function down() {}

	public function testHandlerInvokeReturnsHookValueArray() {
		$handler = new CapturePageContext();
		$hook = new Event(elgg(), 'elgg.data', 'page', [], []);
		$result = $handler($hook);
		$this->assertIsArray($result);
	}

	public function testHandlerSetsContextKey() {
		$handler = new CapturePageContext();
		$hook = new Event(elgg(), 'elgg.data', 'page', [], []);
		$result = $handler($hook);
		$this->assertArrayHasKey('context', $result);
		$this->assertIsArray($result['context']);
	}

	public function testHandlerPreservesExistingHookValueKeys() {
		$handler = new CapturePageContext();
		$hook = new Event(elgg(), 'elgg.data', 'page', ['existing' => 'kept'], []);
		$result = $handler($hook);
		$this->assertSame('kept', $result['existing']);
	}

	public function testCapturedContextHasAllExpectedKeys() {
		$handler = new CapturePageContext();
		$hook = new Event(elgg(), 'elgg.data', 'page', [], []);
		$result = $handler($hook);
		$context = $result['context'];
		$expected = ['user', 'page_owner', 'context_stack', 'input', 'viewtype', 'ts', 'mac'];
		foreach ($expected as $key) {
			$this->assertArrayHasKey($key, $context, "context array missing key: {$key}");
		}
	}

	public function testCapturedContextSignedWithValidMac() {
		// The mac field signs the snapshot so /data endpoints can verify
		// the client echoed back an unmodified context.
		$handler = new CapturePageContext();
		$hook = new Event(elgg(), 'elgg.data', 'page', [], []);
		$context = $handler($hook)['context'];

		$payload = serialize([
			$context['user'],
			$context['page_owner'],
			$context['context_stack'],
			$context['input'],
			$context['viewtype'],
			$context['ts'],
		]);
		$expected = elgg_build_hmac($payload)->getToken();
		$this->assertSame($expected, $context['mac']);
	}
}
