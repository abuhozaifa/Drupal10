<?php

namespace Drupal\dynamic_form_builder\Controller;

use Drupal\dynamic_form_builder\Form\DynamicForm;

class FormPageController {

  public function view($form_id) {
    return \Drupal::formBuilder()->getForm(DynamicForm::class, $form_id);
  }
}