<?php

namespace GetPhoto\KnpMenuExtension\Matcher\Voter;

use Knp\Menu\ItemInterface;

class CakeRouteVoter implements VoterInterface
{
	/**
	 * @var string
	 */
	private $route;

	/**
	 * @param string $regexp
	 */
	public function __construct($regexp)
	{
		$this->route = $regexp;
	}

	public function matchItem(ItemInterface $item)
	{


		return null;
	}
}
