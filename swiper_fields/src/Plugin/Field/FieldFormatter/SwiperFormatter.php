<?php

namespace Drupal\swiper_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
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
	public static function defaultSettings() {
		return self::getDefaultSettings() + parent::defaultSettings();
	}

	/**
	 * {@inheritdoc}
	 */
	public function settingsSummary() {
		$summary = $this->buildSettingsSummary($this);

		// Add the image settings summary and return.
		return array_merge($summary, parent::settingsSummary());

	}

	/**
	 * {@inheritdoc}
	 */
	public function settingsForm(array $form, FormStateInterface $form_state) {
		// Add the options setting.
		$element = $this->buildSettingsForm($this);

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

	/**
	 * {@inheritdoc}
	 */
	public static function isApplicable(FieldDefinitionInterface $field_definition) {
		// This formatter only applies to multi-image fields.
		return parent::isApplicable($field_definition) && $field_definition->getFieldStorageDefinition()->isMultiple();
	}

	/**
	 * {@inheritdoc}
	 */
	public function calculateDependencies() {
		$dependencies = parent::calculateDependencies();
		return $dependencies + $this->getOptionsDependencies($this);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onDependencyRemoval(array $dependencies) {
		$changed = parent::onDependencyRemoval($dependencies);

		if ($this->optionsDependenciesDeleted($this, $dependencies)) {
			$changed = TRUE;
		}
		return $changed;
	}

}
