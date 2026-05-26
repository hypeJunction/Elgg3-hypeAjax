<?php

namespace hypeJunction\Ajax;

use Elgg\Exceptions\Http\BadRequestException;
use Elgg\Http\Request as HttpRequest;
use Elgg\IntegrationTestCase;
use Elgg\Request;

/**
 * Context::capture() snapshots the active page context (user, owner,
 * context stack, viewtype, query input) and signs it with an HMAC.
 * Context::restore() re-applies a snapshot — but only if the HMAC
 * matches; otherwise it throws BadRequestException.
 *
 * These tests exercise the round-trip and the security boundary
 * (signature mismatch).
 */
class ContextTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypeajax';
	}

	public function up() {}
	public function down() {}

	public function testCaptureReturnsArrayWithExpectedShape() {
		$captured = Context::capture();
		$this->assertIsArray($captured);
		foreach (['user', 'page_owner', 'context_stack', 'input', 'viewtype', 'ts', 'mac'] as $key) {
			$this->assertArrayHasKey($key, $captured);
		}
	}

	public function testCaptureRecordsActivePageOwnerGuid() {
		$captured = Context::capture();
		$this->assertSame(\elgg_get_page_owner_guid(), $captured['page_owner']);
	}

	public function testCaptureRecordsActiveContextStack() {
		\elgg_push_context('hypeajax_test_ctx');
		try {
			$captured = Context::capture();
			$this->assertContains('hypeajax_test_ctx', $captured['context_stack']);
		} finally {
			\elgg_pop_context();
		}
	}

	public function testCaptureRecordsViewtype() {
		$captured = Context::capture();
		$this->assertSame(\elgg_get_viewtype(), $captured['viewtype']);
	}

	public function testCaptureMacValidatesAgainstHmac() {
		$captured = Context::capture();
		$payload = serialize([
			$captured['user'],
			$captured['page_owner'],
			$captured['context_stack'],
			$captured['input'],
			$captured['viewtype'],
			$captured['ts'],
		]);
		$this->assertTrue(\elgg_build_hmac($payload)->matchesToken($captured['mac']));
	}

	public function testRestoreRoundTripAcceptsValidSignature() {
		$captured = Context::capture();
		$request = $this->buildRequestWithContextParam('__context', $captured);
		$this->assertTrue(Context::restore($request));
	}

	public function testRestoreRoundTripUsesCustomParamName() {
		$captured = Context::capture();
		$request = $this->buildRequestWithContextParam('ct', $captured);
		$this->assertTrue(Context::restore($request, 'ct'));
	}

	public function testRestoreThrowsBadRequestOnSignatureMismatch() {
		$captured = Context::capture();
		$captured['mac'] = 'tampered-mac-value';
		$request = $this->buildRequestWithContextParam('__context', $captured);

		$this->expectException(BadRequestException::class);
		$this->expectExceptionMessage('Request signature is invalid');
		Context::restore($request);
	}

	public function testRestoreThrowsWhenContextStackTampered() {
		// Mutating the captured payload after signing must fail HMAC.
		$captured = Context::capture();
		$captured['context_stack'] = ['injected_context'];
		$request = $this->buildRequestWithContextParam('__context', $captured);

		$this->expectException(BadRequestException::class);
		Context::restore($request);
	}

	public function testRestoreReplacesContextStackOnSuccess() {
		// Capture a known stack, then change context, then restore — the
		// stack should snap back to what was captured.
		\elgg_push_context('hypeajax_restore_target');
		$captured = Context::capture();
		\elgg_pop_context();
		\elgg_push_context('different_context');

		$request = $this->buildRequestWithContextParam('__context', $captured);
		try {
			Context::restore($request);
			$this->assertContains('hypeajax_restore_target', \elgg_get_context_stack());
			$this->assertNotContains('different_context', \elgg_get_context_stack());
		} finally {
			// reset stack to a clean state regardless of test outcome
			\elgg_set_context_stack([]);
		}
	}

	private function buildRequestWithContextParam(string $name, array $context): Request {
		$httpRequest = HttpRequest::create('/_deferred/test', 'GET', [$name => $context]);

		return new Request(elgg(), $httpRequest);
	}
}
