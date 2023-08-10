<?php
namespace GetPhoto\KnpMenuExtension\Factory;

use Knp\Menu\Factory\ExtensionInterface;
use Knp\Menu\ItemInterface;

/**
 * Class FlatMenuExtension
 *
 * @package GetPhoto\KnpMenuExtension\Factory
 */
class FlatMenuExtension implements ExtensionInterface {

	protected $_urlCache = [];

	/**
	 * @param array $options
	 * @return array
	 */
	public function buildOptions(array $options): array {
		// TODO: Implement buildOptions() method.

		return $options;
	}

	/**
	 * @param ItemInterface $item
	 * @param array $options
	 */
	public function buildItem(ItemInterface $item, array $options): void {
		// Set extra parameters

		$params = ['icon', 'rate_rights', 'headline', 'hide_navbar', 'badge', 'rate_alt_uri', 'user_rights'];

		foreach($params as $param) {
			if(isset($options[$param])) {
				$item->setExtra('ext:'.$param, $options[$param]);
			}
		}

		if(!empty($options['subpages_uri'])) {
			foreach($options['subpages_uri'] as $uri) {
				$item->addChild($uri, ['uri' => $uri])->setDisplay(false)->setExtra('navbar_backlink', true);
			}
		}
	}

}
