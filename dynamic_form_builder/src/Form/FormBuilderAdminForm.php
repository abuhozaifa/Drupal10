<?php

namespace Drupal\dynamic_form_builder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class FormBuilderAdminForm extends FormBase {

  public function getFormId() {
    return 'dfb_admin_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['form_id'] = [
      '#type' => 'textfield',
      '#title' => 'Form ID (use unique like contact_form)',
      '#required' => TRUE,
      '#description' => 'Example: contact_form, feedback_form',
    ];

    $form['fields'] = [
      '#type' => 'textarea',
      '#title' => 'Fields JSON',
      '#description' => 'Example: [{"name":"email","type":"email"}]',
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save Form',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    // 🔥 Clean form ID
    $form_id = strtolower(trim($form_state->getValue('form_id')));
    $fields_raw = $form_state->getValue('fields');

    $key = 'dfb_form_' . $form_id;

    // 🔴 CHECK: already exists?
    if (\Drupal::state()->get($key)) {
      $this->messenger()->addError('Form already exists: ' . $form_id . '. Use a different Form ID.');
      return;
    }

    // 🔥 Validate JSON
    $decoded = json_decode($fields_raw, TRUE);

    if (json_last_error() !== JSON_ERROR_NONE) {
      $this->messenger()->addError('Invalid JSON format.');
      return;
    }

    if (!is_array($decoded)) {
      $this->messenger()->addError('Fields must be JSON array.');
      return;
    }

    // 🔥 Save clean JSON
    $clean_json = json_encode($decoded);

    \Drupal::state()->set($key, $clean_json);

    $this->messenger()->addMessage('Form created successfully: ' . $form_id);
  }
}