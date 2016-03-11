<?php
namespace GetPhoto\KnpMenuExtension;

use Knp\Menu\MenuItem;

/**
 * Class FlatMenuItem
 *
 * @package GetPhoto\KnpMenuExtension
 */
class FlatMenuItem extends MenuItem {

	/**
	 * @var array
	 */
	protected $_urlcache = [];


	public function addChild($child, array $options = array()) {
		$child = &parent::addChild($child, $options);

		if($child) {
			$this->_urlcache[$child->getUri()] = &$child;
		}

		return $child;
	}


}