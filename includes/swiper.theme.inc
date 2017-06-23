<?php

/**
 * @file
 * Theming functions for the swiper module.
 *
 * Preprocessor functions fill variables for templates and helper
 * functions to make theming easier.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;

/**
 * Prepares variables for swiper template.
 *
 * Default template: templates/swiper.html.twig.
 */
function template_preprocess_swiper(&$variables) {
	// Reference configuration variables.
	$settings = &$variables['swiper']['settings'];
	$items = &$variables['swiper']['items'];

	// Set the default container type.
	if (empty($settings['type'])) {
		$settings['type'] = 'ul';
	}

	// Configure attributes for containing elements.
	$attributes = $variables['attributes'];
	// Merge with defined attributes.
	if (isset($settings['attributes']) && is_array($settings['attributes'])) {
		$attributes = NestedArray::mergeDeep($attributes, $settings['attributes']);
	}

	// Set the ID for each swiper instance if none is provided.
	if (empty($attributes['id'])) {
		$swiper_id = &drupal_static('swiper_id', 0);
		$attributes['id'] = 'swiper-' . ++$swiper_id;
	}

	// Add the namespace to any classes.
	// @todo figure out what this is supposed to do
	if (!empty($attributes['class']) && !empty($options['namespace'])) {
		foreach ($attributes['class'] as $key => $value) {
			$attributes['class'][$key] = $options['namespace'] . $value;
		}
	}

	// Add the swiper class to be namespaced.
	$attributes['class'][] = 'swiper';

	// Add the image style name as a class to the container.
	if (!empty($settings['image_style'])) {
		$attributes['class'][] = 'imagestyle-' . Html::getClass($settings['image_style']);
	}

	// Pass attributes to twig.
	$variables['attributes'] = $attributes;

	// Add the list render array.
	$variables['content']['list'] = [
		'#theme' => 'swiper_list',
		'#items' => $items,
		'#settings' => $settings,
	];

	// Finally, add the configuration to the page.
	$attached = [];
	// $attached = flexslider_add($variables['attributes']['id'], $variables['flexslider']['settings']['optionset']);
	$variables['#attached'] = $attached;
}

/**
 * Prepares variables for swiper list template.
 *
 * Default template: templates/swiper-list.html.twig.
 */
function template_preprocess_swiper_list(&$variables) {
	// Reference configuration variables.
	$items = &$variables['items'];
	$attributes = &$variables['attributes'];

	$attributes['class'][] = 'slides';

	// Build the list.
	$variables['content']['list'] = [];
	if (!empty($items)) {
		foreach ($items as $i => $item) {

			$caption = '';
			if (!empty($item['caption'])) {
				$caption = $item['caption'];
			}

			// Add the list item render array.
			$variables['content']['list'][$i] = [
				'#theme' => 'swiper_list_item',
				'#item' => $item['slide'],
				'#caption' => $caption,
			];
		}
	}

}

/**
 * Prepares variables for swiper list item template.
 *
 * Default template: templates/swiper-list-item.html.twig.
 */
function template_preprocess_swiper_list_item(&$variables) {

}
