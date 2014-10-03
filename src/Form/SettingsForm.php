<?php

/**
 * @file
 * Contains \Drupal\uservoice\Form\SettingsForm.
 */

namespace Drupal\uservoice\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure text display settings for this the uservoice world page.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Constructs a \Drupal\aggregator\SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The Core Language manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManager $language_manager) {
    parent::__construct($config_factory);
    $this->language_manager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uservoice_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('uservoice.settings');

    $trigger_style_default_value = $config->get('trigger_style');

    $form['api_key'] = array(
      '#type' => 'textfield',
      '#title' => t('API Key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    );

    $form['mode'] = array(
      '#type' => 'select',
      '#title' => t('Mode'),
      '#options' => array(
          'contact'      => t('Contact'),
          'satisfaction' => t('Satisfaction Rating'),
          'smartvote'    => t('SmartVote'),
       ),
      '#default_value' => $config->get('mode'),
    );
    $select_options = array();
    $standard_languages = $this->language_manager->getStandardLanguageList();
    foreach ($standard_languages as $langcode => $language_names) {
      $select_options[$langcode] = $language_names[0];
    }
    $form['locale'] = array(
      '#type' => 'select',
      '#title' => t('Locale'),
      '#description' => t('If not supported by UserVoice, the default language is english.'),
      '#options' => $select_options,
      '#default_value' => $config->get('locale'),
    );

    $form['customization'] = array(
      '#type'        => 'fieldset',
      '#title'       => t('Customization'),
      '#collapsible' => FALSE,
      '#collapsed'   => FALSE,
    );

    $form['customization']['accent_color'] = array(
      '#type' => 'textfield',
      '#title' => t('Accent color'),
      '#description' => t('Ex: rgba(68, 141, 214, 0.6), #448dd6, blue'),
      '#default_value' => $config->get('accent_color'),
    );

    $form['customization']['trigger_color'] = array(
      '#type' => 'textfield',
      '#title' => t('Trigger color'),
      '#description' => t('Ex: rgba(255, 255, 255, 1), #ffffff, white'),
      '#default_value' => $config->get('trigger_color'),
    );

    $form['customization']['trigger_background_color'] = array(
      '#type' => 'textfield',
      '#title' => t('Trigger background color'),
      '#description' => t('Ex: rgba(46, 49, 51, 0.6), #2e3133, grey'),
      '#default_value' => $config->get('trigger_background_color'),
    );

    $form['customization']['trigger_style'] = array(
      '#type' => 'select',
      '#title' => t('Trigger style'),
      '#options' => array(
        'icon' => t('icon'),
        'tab'  => t('tab'),
       ),
      '#default_value' => $trigger_style_default_value,
      '#ajax' => array(
        'callback' => array($this, 'uservoiceTriggerStyleCallback'),
        'wrapper' => 'trigger-position-div',
        'method' => 'replace'
      )
    );

    $trigger_style_value = $trigger_style_default_value;

    if ($form_state->hasValue('trigger_style')) {
      $trigger_style_value = $form_state->getValue('trigger_style');
    }

    $trigger_position_options = array(
      'bottom-right' => t('bottom-right'),
      'bottom-left'  => t('bottom-left'),
      'top-left'     => t('top-left'),
      'top-right'    => t('top-right'),
    );

    if ($trigger_style_value == 'tab') {
      $trigger_position_options = array(
        'left'  => t('left'),
        'right' => t('right'),
      );
    }

    $form['customization']['trigger_position'] = array(
      '#type' => 'select',
      '#prefix' => '<div id="trigger-position-div">',
      '#suffix' => '</div>',
      '#title' => t('Trigger position'),
      '#options' => $trigger_position_options,
      '#default_value' => $config->get('trigger_position'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('uservoice.settings')
      ->set('api_key', $values['api_key'])
      ->set('mode', $values['mode'])
      ->set('locale', $values['locale'])
      ->set('accent_color', $values['accent_color'])
      ->set('trigger_color', $values['trigger_color'])
      ->set('trigger_background_color', $values['trigger_background_color'])
      ->set('trigger_style', $values['trigger_style'])
      ->set('trigger_position', $values['trigger_position'])
      ->save();

    parent::submitForm($form, $form_state);
  }

  public function uservoiceTriggerStyleCallback(array $form, array &$form_state) {
    return $form['customization']['trigger_position'];
  }
}
