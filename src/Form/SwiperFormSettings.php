<?php

namespace Drupal\swiper\Form;

use Drupal\Core\Asset\LibraryDiscovery;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SwiperFormSettings.
 *
 * @package Drupal\swiper\Form
 */
class SwiperFormSettings extends ConfigFormBase {

  /**
   * The library discovery service.
   *
   * @var \Drupal\Core\Asset\LibraryDiscovery
   */
  private $libraryDiscovery;

  /**
   * The current user account service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $currentUser;

  /**
   * Constructs a new SwiperFormSettings.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Drupal\Core\Asset\LibraryDiscovery $libraryDiscovery
   *   The library discovery service.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The user account service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, LibraryDiscovery $libraryDiscovery, AccountProxyInterface $currentUser) {
    parent::__construct($configFactory);
    $this->libraryDiscovery = $libraryDiscovery;
    $this->currentUser = $currentUser;
  }

  /**
   * Use Symfony's ContainerInterface to declare dependency for constructor.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('library.discovery'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'swiper_advanced_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('swiper.settings');
    $config->set('swiper_debug', $form_state->getValue('swiper_debug'))
      ->set('swiper_css', $form_state->getValue('swiper_css'))
      ->set('swiper_module_css', $form_state->getValue('swiper_module_css'))
      ->save();

    // Invalidate the library discovery cache to update new assets.
    $this->libraryDiscovery->clearCachedDefinitions();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['swiper.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $form['library'] = [
      '#type' => 'details',
      '#title' => 'Library',
      '#open' => TRUE,
    ];

    // Debug mode toggle.
    $form['library']['swiper_debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable debug mode'),
      '#description' => $this->t('Load the human-readable version of the Swiper library.'),
      '#default_value' => $this->config('swiper.settings')->get('swiper_debug'),
      '#access' => $this->currentUser->hasPermission('administer swiper'),
    ];

    // Style toggles.
    $form['styles'] = [
      '#type' => 'details',
      '#title' => $this->t('Syles'),
      '#open' => TRUE,
    ];

    $form['styles']['swiper_css'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('swiper.css'),
      '#description' => $this->t('Load the Swiper base css.'),
      '#default_value' => $this->config('swiper.settings')->get('swiper_css'),
    ];

    $form['styles']['swiper_module_css'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('swiper_img.css'),
      '#description' => $this->t('Load the module css fixes.'),
      '#default_value' => $this->config('swiper.settings')->get('swiper_module_css'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
