<?php

namespace Drupal\module_audit\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class ModuleAuditFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'module_audit_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $request = \Drupal::request();

    $form['filters'] = [
      '#type' => 'container',
      '#attributes' => [
        'style' => '
          display:flex;
          gap:15px;
          align-items:flex-end;
          flex-wrap:wrap;
          padding:15px;
          margin-bottom:20px;
          border:1px solid #ddd;
          border-radius:6px;
          background:#f8f9fa;
        ',
      ],
    ];

    $form['filters']['from'] = [
      '#type' => 'date',
      '#title' => $this->t('From'),
      '#default_value' => $request->query->get('from'),
    ];

    $form['filters']['to'] = [
      '#type' => 'date',
      '#title' => $this->t('To'),
      '#default_value' => $request->query->get('to'),
    ];

    $form['filters']['module'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Module'),
      '#size' => 15,
      '#placeholder' => 'webform',
      '#default_value' => $request->query->get('module'),
    ];

    $form['filters']['action'] = [
      '#type' => 'select',
      '#title' => $this->t('Action'),
      '#options' => [
        '' => '- Any -',
        'Installed' => 'Installed',
        'Uninstalled' => 'Uninstalled',
      ],
      '#default_value' => $request->query->get('action'),
    ];

    $form['filters']['user'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User'),
      '#size' => 15,
      '#placeholder' => 'admin',
      '#default_value' => $request->query->get('user'),
    ];

    $form['filters']['source'] = [
      '#type' => 'select',
      '#title' => $this->t('Source'),
      '#options' => [
        '' => '- Any -',
        'UI' => 'UI',
        'Drush' => 'Drush',
      ],
      '#default_value' => $request->query->get('source'),
    ];

    $form['filters']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
    ];

    $form['filters']['reset'] = [
      '#type' => 'link',
      '#title' => $this->t('Reset'),
      '#url' => Url::fromRoute('module_audit.report'),
      '#attributes' => [
        'class' => ['button'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $query = [
      'from' => $form_state->getValue('from'),
      'to' => $form_state->getValue('to'),
      'module' => $form_state->getValue('module'),
      'action' => $form_state->getValue('action'),
      'user' => $form_state->getValue('user'),
      'source' => $form_state->getValue('source'),
    ];

    $query = array_filter($query);

    $form_state->setRedirect(
      'module_audit.report',
      [],
      ['query' => $query]
    );

  }

}