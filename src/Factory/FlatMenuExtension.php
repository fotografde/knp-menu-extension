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
	public function buildOptions(array $options) {
		// TODO: Implement buildOptions() method.

		return $options;
	}

	/**
	 * @param ItemInterface $item
	 * @param array $options
	 */
	public function buildItem(ItemInterface $item, array $options) {
		// Set extra parameters

		$params = ['icon', 'rate_rights', 'headline'];

		foreach($params as $param) {
			if(isset($options[$param])) {
				$item->setExtra('ext:'.$param, $options[$param]);
			}
		}
	}

}