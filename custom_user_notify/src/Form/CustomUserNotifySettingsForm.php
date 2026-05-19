<?php

namespace Drupal\custom_user_notify\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure settings for Custom User Notify.
 */
class CustomUserNotifySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['custom_user_notify.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_user_notify_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('custom_user_notify.settings');

    $form['notify_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Notification email'),
      '#description' => $this->t('Email address where password update notifications will be sent.'),
      '#default_value' => $config->get('notify_email') ?: 'admin@example.com',
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('custom_user_notify.settings')
      ->set('notify_email', $form_state->getValue('notify_email'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}

