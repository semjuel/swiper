<?php

namespace Drupal\swiper\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;
use Drupal\Component\Utility\Xss;
use Drupal\flexslider\Entity\Flexslider;

/**
 * A common Trait for swiper formatters.
 *
 * Currently, only image based formatters exist for swiper but this trait
 * could apply to any type formatter.
 *
 * @see \Drupal\Core\Field\FormatterBase
 */
trait SwiperFormatterTrait {

	/**
	 * Returns the form element for caption settings.
	 *
	 * @param \Drupal\Core\Field\FormatterBase $formatter
	 *   The formatter having this trait.
	 * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
	 *   The image field definition.
	 *
	 * @return array
	 *   The caption settings render array.
	 */
	protected function captionSettings(FormatterBase $formatter, FieldDefinitionInterface $field_definition) {
		$field_settings = $field_definition->getSettings();

		// Set the caption options.
		$caption_options = [
			0 => $formatter->t('None'),
			1 => $formatter->t('Image title'),
			'alt' => $formatter->t('Image ALT attribute'),
		];

		// Remove the options that are not available.
		$action_fields = [];
		if ($field_settings['title_field'] == FALSE) {
			unset($caption_options[1]);
			// User action required on the image title.
			$action_fields[] = 'title';
		}
		if ($field_settings['alt_field'] == FALSE) {
			unset($caption_options['alt']);
			// User action required on the image alt.
			$action_fields[] = 'alt';
		}

		// Create the caption element.
		$element['caption'] = [
			'#title' => $formatter->t('Choose a caption source'),
			'#type' => 'select',
			'#options' => $caption_options,
		];

		// If the image field doesn't have all of the suitable caption sources,
		// tell the user.
		if ($action_fields) {
			$action_text = $formatter->t('enable the @action_field field', ['@action_field' => implode(' and/or ', $action_fields)]);
			/* This may be a base field definition (e.g. in Views UI) which means it
			 * is not associated with a bundle and will not have the toUrl() method.
			 * So we need to check for the existence of the method before we can
			 * build a link to the image field edit form.
			 */
			if (method_exists($field_definition, 'toUrl')) {
				// Build the link to the image field edit form for this bundle.
				$rel = "{$field_definition->getTargetEntityTypeId()}-field-edit-form";
				$action = $field_definition->toLink($action_text, $rel,
					[
						'fragment' => 'edit-settings-alt-field',
						'query' => \Drupal::destination()->getAsArray(),
					]
				)->toRenderable();
			}
			else {
				// Just use plain text if we can't build the field edit link.
				$action = ['#markup' => $action_text];
			}
			$element['caption']['#description']
				= $formatter->t('You need to @action for this image field to be able to use it as a caption.',
				['@action' => render($action)]);

			// If there are no suitable caption sources, disable the caption element.
			if (count($action_fields) >= 2) {
				$element['caption']['#disabled'] = TRUE;
			}
		}
		else {
			$element['caption']['#default_value'] = $formatter->getSetting('caption');
		}

		return $element;
	}

	/**
	 * The swiper formatted view for images.
	 *
	 * @param array $images
	 *   Images render array from the Image Formatter.
	 * @param array $formatter_settings
	 *   Render array of settings.
	 *
	 * @return array
	 *   Render of swiper formatted images.
	 */
	protected function viewImages(array $images, array $formatter_settings) {

		// Bail out if no images to render.
		if (empty($images)) {
			return [];
		}

		$items = [];

		foreach ($images as $delta => &$image) {
			// Prepare the slide item render array.
			$item = [];
			$item['slide'] = render($image);

			// Check caption settings.
			if ($formatter_settings['caption'] == 1) {
				$item['caption'] = ['#markup' => Xss::filterAdmin($image['#item']->title)];
			}
			elseif ($formatter_settings['caption'] == 'alt') {
				$item['caption'] = ['#markup' => Xss::filterAdmin($image['#item']->alt)];
			}

			$items[$delta] = $item;
		}

		$images['#theme'] = 'swiper';
		$images['#swiper'] = [
			'settings' => $formatter_settings,
			'items' => $items,
		];

		return $images;
	}
}
