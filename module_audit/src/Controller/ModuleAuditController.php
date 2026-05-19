<?php

namespace Drupal\module_audit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\module_audit\Form\ModuleAuditFilterForm;

class ModuleAuditController extends ControllerBase {

  public function report() {

    $build = [];

    // Filter form show.
    $build['filter_form'] =
      \Drupal::formBuilder()
      ->getForm(ModuleAuditFilterForm::class);

    $header = [
      'Module',
      'Action',
      'User',
      'Source',
      'Enabled Count',
      'Date',
    ];

    $rows = [];

    $query = \Drupal::database()
      ->select('module_audit_log', 'm');

    $query->fields('m');

    $request = \Drupal::request();

    // Module filter.
    if ($module = $request->query->get('module')) {
      $query->condition(
        'module_name',
        '%' . $module . '%',
        'LIKE'
      );
    }

    // Action filter.
    if ($action = $request->query->get('action')) {
      $query->condition(
        'action',
        $action
      );
    }

    // User filter.
    if ($user = $request->query->get('user')) {
      $query->condition(
        'username',
        '%' . $user . '%',
        'LIKE'
      );
    }

    // Source filter.
    if ($source = $request->query->get('source')) {
      $query->condition(
        'source',
        $source
      );
    }

    // From date.
    if ($from = $request->query->get('from')) {

      $query->condition(
        'created',
        strtotime($from),
        '>='
      );
    }

    // To date.
    if ($to = $request->query->get('to')) {

      $query->condition(
        'created',
        strtotime($to . ' 23:59:59'),
        '<='
      );
    }

    $query->orderBy(
      'created',
      'DESC'
    );

    $results = $query
      ->execute();

    foreach ($results as $row) {

      $rows[] = [

        $row->module_name,

        $row->action,

        $row->username,

        $row->source,

        $row->enabled_modules_count,

        date(
          'd M Y h:i A',
          $row->created
        ),

      ];

    }

    $build['table'] = [

      '#type' => 'table',

      '#header' => $header,

      '#rows' => $rows,

      '#empty' => $this->t(
        'No logs found'
      ),

    ];

    return $build;

  }

}