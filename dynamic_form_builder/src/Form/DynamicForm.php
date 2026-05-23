<?php

namespace Drupal\dynamic_form_builder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Dynamic Form Builder Renderer Form.
 */
class DynamicForm extends FormBase {

  /**
   * Current dynamic form ID.
   */
  protected $formId;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dfb_dynamic_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $form_id = NULL) {

    // 🔥 Safe fallback
    $form_id = $form_id ?? 'default';
    $this->formId = $form_id;

    $key = 'dfb_form_' . $form_id;

    // Get stored value
    $fields_json = \Drupal::state()->get($key);

    // ❌ No data found
    if (empty($fields_json)) {
      $form['message'] = [
        '#markup' => '<div class="messages messages--error">No form found for ID: ' . $form_id . '</div>',
      ];
      return $form;
    }

    // 🔥 Fix: ensure string (avoid BLOB / array issue)
    if (!is_string($fields_json)) {
      $fields_json = json_encode($fields_json);
    }

    // Decode JSON safely
    $fields = json_decode($fields_json, TRUE);

    // ❌ Invalid JSON
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($fields)) {
      $form['message'] = [
        '#markup' => '<div class="messages messages--error">Invalid form configuration (JSON error).</div>',
      ];
      return $form;
    }

    // ❌ Empty fields
    if (empty($fields)) {
      $form['message'] = [
        '#markup' => '<div class="messages messages--warning">No fields defined in form.</div>',
      ];
      return $form;
    }

    // Build dynamic fields
    foreach ($fields as $field) {

      if (empty($field['name']) || empty($field['type'])) {
        continue;
      }

      $name = $field['name'];
      $type = $field['type'];

      $form[$name] = [
        '#type' => $type,
        '#title' => ucfirst(str_replace('_', ' ', $name)),
        '#required' => !empty($field['required']),
      ];

      // Optional default value
      if (!empty($field['default'])) {
        $form[$name]['#default_value'] = $field['default'];
      }

      // Optional options (select support future)
      if ($type === 'select' && !empty($field['options'])) {
        $form[$name]['#options'] = $field['options'];
      }
    }

    // Submit actions
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    // Remove system values
    unset(
      $values['submit'],
      $values['form_build_id'],
      $values['form_token'],
      $values['form_id'],
      $values['op']
    );

    // Save submission safely
    \Drupal::database()->insert('dfb_submission')
      ->fields([
        'form_id' => $this->formId,
        'data' => json_encode($values, JSON_UNESCAPED_UNICODE),
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();

    $this->messenger()->addMessage($this->t('Form submitted successfully.'));
  }

}