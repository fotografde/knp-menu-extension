<?php
namespace GetPhoto\KnpMenuExtension\Factory;

use Knp\Menu\Factory\ExtensionInterface;
use Knp\Menu\ItemInterface;

/**
 * Class UrlCacheExtension
 *
 * @package GetPhoto\KnpMenuExtension\Factory
 */
class UrlCacheExtension implements ExtensionInterface {

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
		// TODO: Implement buildItem() method.

		$urlCache = $item->getRoot()->getExtra('UrlCache');
		if(!is_array($urlCache)) {
			$urlCache = [];
		}
		$urlCache[$item->getUri()] = &$item;

		$item->getRoot()->setExtra('UrlCache', $urlCache);


	}

}