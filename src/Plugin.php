<?php

namespace Detain\MyAdminAmazon;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Plugin
 *
 * @package Detain\MyAdminAmazon
 */
class Plugin
{
	public static $name = 'Amazon Plugin';
	public static $description = 'Allows handling of Amazon based Payments through their Payment Processor/Payment System.';
	public static $help = '';
	public static $type = 'plugin';

	/**
	 * Plugin constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @return array
	 */
	public static function getHooks()
	{
		return [
			'system.settings' => [__CLASS__, 'getSettings'],
			//'ui.menu' => [__CLASS__, 'getMenu'],
			'function.requirements' => [__CLASS__, 'getRequirements']
		];
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getMenu(GenericEvent $event)
	{
		$menu = $event->getSubject();
		if ($GLOBALS['tf']->ima == 'admin') {
			function_requirements('has_acl');
			if (has_acl('client_billing')) {
				$menu->add_link('admin', 'choice=none.abuse_admin', '/lib/webhostinghub-glyphs-icons/icons/development-16/Black/icon-spam.png', __('Amazon'));
			}
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getRequirements(GenericEvent $event)
	{
        /**
         * @var \MyAdmin\Plugins\Loader $this->loader
         */
        $loader = $event->getSubject();
		$loader->add_requirement('amazon_obtain_profile', '/../vendor/detain/myadmin-amazon-payments/src/amazon.php');
		$loader->add_requirement('amazon_wallet_widget', '/../vendor/detain/myadmin-amazon-payments/src/amazon.php');
		$loader->add_requirement('amazon_addressbook_widget', '/../vendor/detain/myadmin-amazon-payments/src/amazon.php');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
    public static function getSettings(GenericEvent $event)
    {
        /**
         * @var \MyAdmin\Settings $settings
         **/
        $settings = $event->getSubject();
		$settings->add_radio_setting(__('Billing'), __('Amazon'), 'amazon_checkout_enabled', __('Enable Amazon Checkout'), __('Enable Amazon Checkout'), AMAZON_CHECKOUT_ENABLED, [true, false], ['Enabled', 'Disabled']);
		$settings->add_dropdown_setting(__('Billing'), __('Amazon'), 'amazon_sandbox', __('Use Sandbox/Test Environment'), __('Use Sandbox/Test Environment'), AMAZON_SANDBOX, [false, true], ['Live Environment', 'Sandbox Test Environment']);
		$settings->add_text_setting(__('Billing'), __('Amazon'), 'amazon_client_id', __('Client ID'), __('Client ID'), (defined('AMAZON_CLIENT_ID') ? AMAZON_CLIENT_ID : ''));
		$settings->add_text_setting(__('Billing'), __('Amazon'), 'amazon_seller_id', __('Seller ID'), __('Seller ID'), (defined('AMAZON_SELLER_ID') ? AMAZON_SELLER_ID : ''));
	}
}
