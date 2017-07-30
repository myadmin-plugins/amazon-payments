<?php

namespace Detain\MyAdminAmazon;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Plugin
 *
 * @package Detain\MyAdminAmazon
 */
class Plugin {

	public static $name = 'Amazon Plugin';
	public static $description = 'Allows handling of Amazon based Payments through their Payment Processor/Payment System.';
	public static $help = '';
	public static $type = 'plugin';

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
	}

	/**
	 * @return array
	 */
	public static function getHooks() {
		return [
			'system.settings' => [__CLASS__, 'getSettings'],
			//'ui.menu' => [__CLASS__, 'getMenu'],
		];
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getMenu(GenericEvent $event) {
		$menu = $event->getSubject();
		if ($GLOBALS['tf']->ima == 'admin') {
			function_requirements('has_acl');
					if (has_acl('client_billing'))
							$menu->add_link('admin', 'choice=none.abuse_admin', '//my.interserver.net/bower_components/webhostinghub-glyphs-icons/icons/development-16/Black/icon-spam.png', 'Amazon');
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getRequirements(GenericEvent $event) {
		$loader = $event->getSubject();
		$loader->add_requirement('class.Amazon', '/../vendor/detain/myadmin-amazon-payments/src/Amazon.php');
		$loader->add_requirement('deactivate_kcare', '/../vendor/detain/myadmin-amazon-payments/src/abuse.inc.php');
		$loader->add_requirement('deactivate_abuse', '/../vendor/detain/myadmin-amazon-payments/src/abuse.inc.php');
		$loader->add_requirement('get_abuse_licenses', '/../vendor/detain/myadmin-amazon-payments/src/abuse.inc.php');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getSettings(GenericEvent $event) {
		$settings = $event->getSubject();
		$settings->add_radio_setting('Billing', 'Amazon', 'amazon_checkout_enabled', 'Enable Amazon Checkout', 'Enable Amazon Checkout', AMAZON_CHECKOUT_ENABLED, [true, false], ['Enabled', 'Disabled']);
		$settings->add_dropdown_setting('Billing', 'Amazon', 'amazon_sandbox', 'Use Sandbox/Test Environment', 'Use Sandbox/Test Environment', AMAZON_SANDBOX, [false, true], ['Live Environment', 'Sandbox Test Environment']);
		$settings->add_text_setting('Billing', 'Amazon', 'amazon_client_id', 'Client ID', 'Client ID', (defined('AMAZON_CLIENT_ID') ? AMAZON_CLIENT_ID : ''));
		$settings->add_text_setting('Billing', 'Amazon', 'amazon_seller_id', 'Seller ID', 'Seller ID', (defined('AMAZON_SELLER_ID') ? AMAZON_SELLER_ID : ''));
	}

}
