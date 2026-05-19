<?php

namespace Drupal\user_action_tracker\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Url;

class UserActionController extends ControllerBase {

  protected $dateFormatter;
  protected $pagerManager;

  public function __construct(DateFormatterInterface $date_formatter, PagerManagerInterface $pager_manager) {
    $this->dateFormatter = $date_formatter;
    $this->pagerManager = $pager_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('pager.manager')
    );
  }

  public function actionLog(Request $request) {
    $limit = 10; // Number of records per page
    $page = \Drupal::service('pager.parameters')->findPage();
    
    // Get search keyword from URL query parameter
    $search = trim($request->query->get('search', ''));

    // Define table headers
    $header = [
      ['data' => $this->t('User ID')],
      ['data' => $this->t('Username')],
      ['data' => $this->t('Entity Type')],
      ['data' => $this->t('Action')],
      ['data' => $this->t('Message')],
      ['data' => $this->t('Domain')],
      ['data' => $this->t('Timestamp')],
    ];

    // Base query for retrieving records
    $query = Database::getConnection()->select('user_action_logs', 'ual')
      ->fields('ual', ['uid', 'username', 'entity_type', 'action', 'message', 'domain', 'timestamp'])
      ->orderBy('timestamp', 'DESC');

    // Apply search filter if a keyword is entered
    if (!empty($search)) {
      $query->condition('username', '%' . Database::getConnection()->escapeLike($search) . '%', 'LIKE');
    }

    // Clone query for counting total records
    $count_query = clone $query;
    $total = $count_query->countQuery()->execute()->fetchField();

    // Apply range for pagination
    $query->range($page * $limit, $limit);
    $results = $query->execute()->fetchAll();

    // Initialize pagination
    $this->pagerManager->createPager($total, $limit);

    // Process rows
    $rows = [];
    foreach ($results as $row) {
      $rows[] = [
        'uid' => $row->uid,
        'username' => $row->username,
        'entity_type' => $row->entity_type,
        'action' => $row->action,
        'message' => $row->message,
        'domain' => $row->domain,
        'timestamp' => $this->dateFormatter->format($row->timestamp, 'custom', 'Y-m-d H:i:s'),
      ];
    }

    return [
    'search_form' => [
  '#type' => 'inline_template',
  '#template' => '
    <div class="search-form-wrapper">
      <form action="{{ url }}" method="GET" class="search-form">
        <div class="form-item">
          <input type="text" name="search" value="{{ search }}" placeholder="{{ "Search by Username"|t }}" class="form-text">
          <button type="submit" class="button button--primary">{{ "Search"|t }}</button>
        </div>
      </form>
    </div>',
  '#context' => [
    'url' => Url::fromRoute('<current>')->toString(),
    'search' => htmlspecialchars($search),
  ],
  '#attached' => [
    'library' => ['core/drupal.ajax'], // Ensures styling loads correctly
  ],
],


      // Table with search results
      'log_table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No activity logs found.'),
      ],

      // Pagination
      'pager' => [
        '#type' => 'pager',
      ],
    ];
  }
}
