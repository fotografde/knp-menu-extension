<?php

namespace GetPhoto\KnpMenuExtension\Renderer;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Renderer\Renderer;
use Knp\Menu\Renderer\RendererInterface;

/**
 * Renders MenuItem tree as unordered list
 */
class FlatMenuRenderer extends Renderer implements RendererInterface {
	protected $matcher;
	protected $defaultOptions;

	/**
	 * @param MatcherInterface $matcher
	 * @param array $defaultOptions
	 * @param string $charset
	 */
	public function __construct(MatcherInterface $matcher, array $defaultOptions = array(), $charset = null) {
		$this->matcher = $matcher;
		$this->defaultOptions = array_merge(
			array(
				'depth' => null,
				'matchingDepth' => null,
				'currentAsLink' => true,
				'currentClass' => 'current',
				'ancestorClass' => 'current_ancestor',
				'firstClass' => 'first',
				'lastClass' => 'last',
				'compressed' => false,
				'allow_safe_labels' => false,
				'clear_matcher' => true,
				'leaf_class' => null,
				'branch_class' => null,
				'icon_base_path' => null,
				'icon_ext' => null,
			),
			$defaultOptions
		);

		parent::__construct($charset);
	}

	public function render(ItemInterface $item, array $options = array()): string {
		$options = array_merge($this->defaultOptions, $options);

		$html = $this->renderList($item, $item->getChildrenAttributes(), $options);

		if ($options['clear_matcher']) {
			$this->matcher->clear();
		}

		return $html;
	}

	public function renderBacklink(ItemInterface $item, $options = array()): string {
		$options = array_merge($this->defaultOptions, $options);

		if (empty($options['headline'])) {
			return '';
		}

		if (!empty($options['label'])) {
			$item->getParent()->setLabel($options['label']);
		}

		if (!empty($options['icon'])) {
			$item->getParent()->setExtra('ext:icon', $options['icon']);
		}

		if (!empty($options['headline'])) {
			$item->setExtra('ext:headline', $options['headline']);
		}

		$item->setLabelAttribute('class', 'cf-headline');

		$item->getParent()->setLinkAttribute('class', 'cf-back-link btn btn-default');

		$html = $this->renderLink($item->getParent(), $options);

		$html .= $this->renderHeadline($item, $options);

		if ($options['clear_matcher']) {
			$this->matcher->clear();
		}

		return $html;
	}

	protected function getExtendedOptions(ItemInterface $item) {
		$options = [];
		$extras = $item->getExtras();
		foreach ($extras as $key => $value) {
			if (substr($key, 0, 4) === 'ext:') {
				$options[substr($key, 4)] = $value;
			}
		}
		return $options;
	}

	protected function renderList(ItemInterface $item, array $attributes, array $options) {
		/**
		 * Return an empty string if any of the following are true:
		 *   a) The menu has no children eligible to be displayed
		 *   b) The depth is 0
		 *   c) This menu item has been explicitly set to hide its children
		 */
		if (!$item->hasChildren() || 0 === $options['depth'] || !$item->getDisplayChildren()) {
			return '';
		}

		$html = $this->format(
			'<ul' . $this->renderHtmlAttributes($attributes) . '>',
			'ul',
			$item->getLevel(),
			$options
		);
		$html .= $this->renderChildren($item, $options);
		$html .= $this->format('</ul>', 'ul', $item->getLevel(), $options);

		return $html;
	}

	/**
	 * Renders all of the children of this menu.
	 *
	 * This calls ->renderItem() on each menu item, which instructs each
	 * menu item to render themselves as an <li> tag (with nested ul if it
	 * has children).
	 * This method updates the depth for the children.
	 *
	 * @param ItemInterface $item
	 * @param array $options The options to render the item.
	 *
	 * @return string
	 */
	protected function renderChildren(ItemInterface $item, array $options) {
		// render children with a depth - 1
		if (null !== $options['depth']) {
			$options['depth'] = $options['depth'] - 1;
		}

		if (null !== $options['matchingDepth'] && $options['matchingDepth'] > 0) {
			$options['matchingDepth'] = $options['matchingDepth'] - 1;
		}

		$html = '';
		foreach ($item->getChildren() as $child) {
			$html .= $this->renderItem($child, $options);
		}

		return $html;
	}

	/**
	 * Called by the parent menu item to render this menu.
	 *
	 * This renders the li tag to fit into the parent ul as well as its
	 * own nested ul tag if this menu item has children
	 *
	 * @param ItemInterface $item
	 * @param array $options The options to render the item
	 *
	 * @return string
	 */
	protected function renderItem(ItemInterface $item, array $options) {
		//apply user rights policy to item
		$this->applyUserRights($item, $options);

		// if we don't have access or this item is marked to not be shown
		if (!$item->isDisplayed()) {
			return '';
		}

		$extendedOtions = $this->getExtendedOptions($item);

		// create an array than can be imploded as a class list
		$class = (array)$item->getAttribute('class');

		if ($this->matcher->isCurrent($item)) {
			$class[] = $options['currentClass'];
		} elseif ($this->matcher->isAncestor($item, $options['matchingDepth'])) {
			$class[] = $options['ancestorClass'];
		}

		if ($item->actsLikeFirst()) {
			$class[] = $options['firstClass'];
		}
		if ($item->actsLikeLast()) {
			$class[] = $options['lastClass'];
		}

		if ($item->hasChildren() && $options['depth'] !== 0) {
			if (null !== $options['branch_class'] && $item->getDisplayChildren()) {
				$class[] = $options['branch_class'];
			}
		} elseif (null !== $options['leaf_class']) {
			$class[] = $options['leaf_class'];
		}

		// retrieve the attributes and put the final class string back on it
		$attributes = $item->getAttributes();
		if (!empty($class)) {
			$attributes['class'] = implode(' ', $class);
		}

		// opening li tag
		$html = $this->format(
			'<li' . $this->renderHtmlAttributes($attributes) . '>',
			'li',
			$item->getLevel(),
			$options
		);

		// render the text/link inside the li tag
		//$html .= $this->format($item->getUri() ? $item->renderLink() : $item->renderLabel(), 'link', $item->getLevel());
		$html .= $this->renderLink($item, $options);


		// renders the embedded ul
		$childrenClass = (array)$item->getChildrenAttribute('class');
		$childrenClass[] = 'menu_level_' . $item->getLevel();

		$childrenAttributes = $item->getChildrenAttributes();
		$childrenAttributes['class'] = implode(' ', $childrenClass);

		$html .= $this->renderList($item, $childrenAttributes, $options);


		// closing li tag
		$html .= $this->format('</li>', 'li', $item->getLevel(), $options);

		return $html;
	}

	/**
	 * Apply user rights to an item
	 * so we can hide and display items differently based on user rights & photographer rates
	 * Used in renderItem
	 *
	 * @author navihtot
	 * @param ItemInterface $item
	 * @param array $options The options to render the item
	 */
	protected function applyUserRights(ItemInterface &$item, array $options) {
		$user_rights=$item->getExtra('ext:user_rights');
		if (!empty($user_rights)) {
			if (empty(array_intersect($user_rights, $options['user_rights']))) {
				$item->setDisplay(false);
			}
		}

		$rate_rights=$item->getExtra('ext:rate_rights');
		$display_normal = false;
		if (!empty($rate_rights)) {
			//if one of item rate rights is in user rate right, then display item normaly
			foreach ($rate_rights as $rate_right) {
				if (isset($options['user_rate'][$rate_right]) && $options['user_rate'][$rate_right] == 1) {
					$display_normal = true;
					break;
				}
			}

			//if user rate is in required rates for item
			if (!$display_normal) {
				$alt_uri = $item->getExtra('ext:rate_alt_uri');
				if (!empty($alt_uri)) {
					$item->setUri($alt_uri);
				}
				else if (!empty($this->defaultOptions['default_rate_alt_uri'])) {
					$item->setUri($this->defaultOptions['default_rate_alt_uri']);
				}
				//set item class for css style if its unavailable for rate
				$item_class = $item->getAttribute('class');
				if ($item_class != '') $item_class .= ' ';
				$item_class .= 'rate-unavailable';
				$item->setAttribute('class',$item_class);
				$item->setExtra('ext:rate-unavailable', true);
			}
		}
	}

	/**
	 * Renders the link in a a tag with link attributes or
	 * the label in a span tag with label attributes
	 *
	 * Tests if item has a an uri and if not tests if it's
	 * the current item and if the text has to be rendered
	 * as a link or not.
	 *
	 * @param ItemInterface $item The item to render the link or label for
	 * @param array $options The options to render the item
	 *
	 * @return string
	 */
	protected function renderLink(ItemInterface $item, array $options = array()) {
		if ($item->getUri() && (!$item->isCurrent() || $options['currentAsLink'])) {
			$text = $this->renderLinkElement($item, $options);
		} else {
			$text = $this->renderSpanElement($item, $options);
		}
		return $this->format($text, 'link', $item->getLevel(), $options);
	}

	protected function renderLinkElement(ItemInterface $item, array $options) {
		return sprintf(
			'<a href="%s"%s>%s</a>',
			$this->escape($item->getUri()),
			$this->renderHtmlAttributes($item->getLinkAttributes()),
			$this->renderLabel($item, $options)
		);
	}

	protected function renderHeadline(ItemInterface $item, array $options) {
		$label = $item->getLabel();

		if($item->getExtra('ext:headline')) {
			$label = $item->getExtra('ext:headline');
		}

		return sprintf(
			'<h1%s>%s</h1>',
			$this->renderHtmlAttributes($item->getLabelAttributes()),
			$label
		);
	}

	protected function renderSpanElement(ItemInterface $item, array $options) {
		return sprintf(
			'<span%s>%s</span>',
			$this->renderHtmlAttributes($item->getLabelAttributes()),
			$this->renderLabel($item, $options)
		);
	}

	protected function renderLabel(ItemInterface $item, array $options) {
		$label = '';
		$extendedOptions = $this->getExtendedOptions($item);
		// icon
		if (!empty($extendedOptions['icon'])) {
			$label .= $this->format(
				'<img src="' . $options['icon_base_path'] . $extendedOptions['icon'] . $options['icon_ext'] . '" />',
				'img',
				$item->getLevel(),
				$options
			);
		}

		if (!empty($extendedOptions['badge'])) {
			$label .= sprintf(' <span style="margin-left:10px" class="label label-important pull-right">%s</span>',
				$this->escape($extendedOptions['badge'])
			);
		}

		if ($options['allow_safe_labels'] && $item->getExtra('safe_label', false)) {
			$label .= $item->getLabel();
		} else {
			$label .= $this->escape($item->getLabel());
		}

		if (!empty($extendedOptions['rate-unavailable'])) {
			$label .= '<i class="'.$this->defaultOptions['rate_unavailable_icon'].' rate-unavailable-icon"></i>';
		}

		return $label;
	}

	/**
	 * If $this->renderCompressed is on, this will apply the necessary
	 * spacing and line-breaking so that the particular thing being rendered
	 * makes up its part in a fully-rendered and spaced menu.
	 *
	 * @param string $html The html to render in an (un)formatted way
	 * @param string $type The type [ul,link,li] of thing being rendered
	 * @param integer $level
	 * @param array $options
	 *
	 * @return string
	 */
	protected function format($html, $type, $level, array $options) {
		if ($options['compressed']) {
			return $html;
		}

		switch ($type) {
			case 'ul':
			case 'link':
			case 'img':
				$spacing = $level * 4;
				break;

			case 'li':
				$spacing = $level * 4 - 2;
				break;
		}

		return str_repeat(' ', $spacing) . $html . "\n";
	}
}
