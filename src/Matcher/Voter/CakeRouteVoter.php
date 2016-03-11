<?php

namespace GetPhoto\KnpMenuExtension\Matcher\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;

class CakeRouteVoter implements VoterInterface
{
	/**
	 * @var string
	 */
	private $route;

	/**
	 * @param string $regexp
	 */
	public function __construct($route)
	{
		$this->route = $route;
	}

	public function matchItem(ItemInterface $item)
	{
		if (null === $this->route || null === $item->getUri()) {
			return null;
		}

		$route = array_filter(explode('/', $this->route));
		$uri = array_filter(explode('/', $item->getUri()));

		foreach($uri as $key => $part) {
			if(empty($route[$key])) {
				return null;
			}
			if($part === '*') {
				continue;
			}

			if($part !== $route[$key]) {
				return null;
			}
		}

		return true;
	}
}
