<?php

namespace hypeJunction\Ajax;

use Elgg\HooksRegistrationService\Hook;
use Elgg\IntegrationTestCase;

/**
 * DeferViewRendering replaces a deferred view's output with a placeholder
 * that the client resolves asynchronously. The hook fires for every
 * view_vars:all and is a no-op unless the view explicitly opted in via
 * the 'deferred' var.
 */
class DeferViewRenderingTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypeajax';
	}

	public function up() {}
	public function down() {}

	public function testHandlerIsNoOpWhenDeferredFlagAbsent() {
		$handler = new DeferViewRendering();
		$hook = new Hook(elgg(), 'view_vars', 'navigation/menu', ['foo' => 'bar'], []);
		$result = $handler($hook);
		// Returning null/void leaves the view vars untouched.
		$this->assertNull($result);
	}

	public function testHandlerIsNoOpWhenDeferredFlagFalsy() {
		$handler = new DeferViewRendering();
		$hook = new Hook(elgg(), 'view_vars', 'navigation/menu', ['deferred' => false], []);
		$this->assertNull($handler($hook));
	}

	public function testHandlerStripsDeferredAndPlaceholderKeys() {
		$handler = new DeferViewRendering();
		$hook = new Hook(elgg(), 'view_vars', 'output/url', [
			'deferred' => true,
			'placeholder' => 'loading...',
			'item_id' => 42,
		], []);
		$result = $handler($hook);

		$this->assertIsArray($result);
		$this->assertArrayNotHasKey('deferred', $result);
		$this->assertArrayNotHasKey('placeholder', $result);
		$this->assertSame(42, $result['item_id']);
	}

	public function testHandlerWritesPlaceholderToViewOutput() {
		$handler = new DeferViewRendering();
		$hook = new Hook(elgg(), 'view_vars', 'output/url', [
			'deferred' => true,
			'placeholder' => 'loading...',
			'item_id' => 42,
		], []);
		$result = $handler($hook);
		$this->assertArrayHasKey('__view_output', $result);
		$this->assertNotEmpty($result['__view_output']);
	}

	public function testPlaceholderViewRendersDeferredViewName() {
		// The placeholder view embeds the deferred view name in the
		// data-src URL so the client knows what to fetch. Render it
		// directly and confirm the wiring rather than scraping markup
		// from the hook.
		$rendered = \elgg_view('ajax/placeholder', [
			'view' => 'output/url',
			'payload' => ['item_id' => 42],
			'placeholder' => 'loading...',
		]);
		$this->assertNotEmpty($rendered);
		$this->assertStringContainsString('_deferred/output/url', $rendered);
	}

	public function testPlaceholderViewRendersEmptyForUnknownView() {
		// placeholder.php returns early when the deferred view doesn't
		// exist — protects against typos producing broken client-side
		// fetches.
		$rendered = \elgg_view('ajax/placeholder', [
			'view' => 'this/view/does/not/exist',
			'payload' => [],
		]);
		$this->assertEmpty(trim($rendered));
	}
}
