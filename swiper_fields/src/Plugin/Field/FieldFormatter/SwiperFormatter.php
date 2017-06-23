<?php

namespace Drupal\swiper\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;

/**
 * Plugin implementation of the 'Swiper' formatter.
 *
 * @FieldFormatter(
 *   id = "swiper",
 *   label = @Translation("Swiper"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class SwiperFormatter extends ImageFormatter {
	use SwiperFormatterTrait;

	/**
	 * {@inheritdoc}
	 */
	public function settingsForm(array $form, FormStateInterface $form_state) {
		$element = [];

		// Add the image settings.
		$element = array_merge($element, parent::settingsForm($form, $form_state));
		// We don't need the link setting.
		$element['image_link']['#access'] = FALSE;

		// Add the caption setting.
		if (!empty($this->getSettings())) {
			$element += $this->captionSettings($this, $this->fieldDefinition);
		}

		return $element;
	}

	/**
	 * {@inheritdoc}
	 */
	public function viewElements(FieldItemListInterface $items, $langcode) {
		$images = parent::viewElements($items, $langcode);

		return $this->viewImages($images, $this->getSettings());
	}
}
