<?php

namespace hypeJunction\Ajax;

use Elgg\IntegrationTestCase;

/**
 * Characterization suite for hypeajax on Elgg 4.x.
 *
 * Small plugin — 5 classes, no entities, no actions, one route, two hook
 * handlers — so the test surface is plugin lifecycle, class autoloading,
 * Bootstrap::init hook wiring, and the migration-enforced absence of
 * start.php. The 3.x-to-4.x migration replaced start.php with a
 * DefaultPluginBootstrap subclass referenced from elgg-plugin.php; pin
 * that shape so a regression surfaces immediately.
 */
class BootstrapTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypeajax';
	}

	public function up() {}
	public function down() {}

	// --- plugin lifecycle ---

	public function testPluginIsRegistered() {
		$this->assertInstanceOf(\ElggPlugin::class, elgg_get_plugin_from_id('hypeajax'));
	}

	public function testPluginIsEnabled() {
		$this->assertTrue(elgg_get_plugin_from_id('hypeajax')->isEnabled());
	}

	public function testPluginIsActive() {
		$this->assertTrue(elgg_get_plugin_from_id('hypeajax')->isActive());
	}

	// --- migration invariants (no start.php, declarative bootstrap) ---

	public function testNoStartPhpPresent() {
		// Elgg 4.x fatals on plugin activation if start.php is present.
		// The 3.x migration removed it — pin its absence so the file
		// doesn't sneak back in via cherry-pick or revert.
		$pluginPath = elgg_get_plugin_from_id('hypeajax')->getPath();
		$this->assertFileDoesNotExist($pluginPath . 'start.php');
	}

	public function testBootstrapRegisteredInPluginManifest() {
		$plugin = elgg_get_plugin_from_id('hypeajax');
		$data = include $plugin->getPath() . 'elgg-plugin.php';
		$this->assertArrayHasKey('bootstrap', $data);
		$this->assertSame(Bootstrap::class, $data['bootstrap']);
	}

	// --- class autoloading ---

	public function testBootstrapClassLoads() {
		$this->assertTrue(class_exists(Bootstrap::class));
	}

	public function testBootstrapExtendsDefaultPluginBootstrap() {
		$r = new \ReflectionClass(Bootstrap::class);
		$this->assertTrue($r->isSubclassOf(\Elgg\DefaultPluginBootstrap::class));
	}

	public function testCapturePageContextClassLoads() {
		$this->assertTrue(class_exists(CapturePageContext::class));
	}

	public function testDeferViewRenderingClassLoads() {
		$this->assertTrue(class_exists(DeferViewRendering::class));
	}

	public function testDeferredViewControllerClassLoads() {
		$this->assertTrue(class_exists(DeferredViewController::class));
	}

	public function testContextClassLoads() {
		$this->assertTrue(class_exists(Context::class));
	}

	public function testPayloadItemClassLoads() {
		$this->assertTrue(class_exists(PayloadItem::class));
	}

	// --- Bootstrap::init hook wiring ---

	public function testElggDataPageHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('elgg.data', $handlers);
		$this->assertArrayHasKey('page', $handlers['elgg.data']);
	}

	public function testViewVarsAllHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('view_vars', $handlers);
		$this->assertArrayHasKey('all', $handlers['view_vars']);
	}
}
