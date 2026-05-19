<?php

namespace Drupal\custom_user_notify\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityDeleteEvent;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Subscribe to user delete events.
 */
class UserDeleteSubscriber implements EventSubscriberInterface {

  protected $mailManager;
  protected $currentUser;
  protected $settings;

  public function __construct(MailManagerInterface $mail_manager, AccountProxyInterface $current_user, Settings $settings) {
    $this->mailManager = $mail_manager;
    $this->currentUser = $current_user;
    $this->settings = $settings;
  }

  public static function getSubscribedEvents() {
    return [
      'entity.delete' => 'onEntityDelete',
    ];
  }

  public function onEntityDelete(EntityDeleteEvent $event) {
    $entity = $event->getEntity();

    if ($entity instanceof UserInterface) {
      $environment = $this->settings->get('environment') ?? 'unknown';

      $params['subject'] = t('@env User account deleted', [
        '@env' => ucfirst($environment),
      ]);

      $params['message'] = t("Dear Admin,\n\nA user account has been deleted.\n\nUsername: @username\nEmail: @mail\nDeleted by: @actor (uid=@uid)\nEnvironment: @env\n\nThanks,\nCrown Estate Scotland", [
        '@username' => $entity->getAccountName(),
        '@mail' => $entity->getEmail() ?: '(none)',
        '@actor' => $this->currentUser->getDisplayName(),
        '@uid' => $this->currentUser->id(),
        '@env' => ucfirst($environment),
      ]);

      $to = \Drupal::config('custom_user_notify.settings')->get('notify_email');
      $this->mailManager->mail('custom_user_notify', 'user_account_deleted', $to, 'en', $params);

      \Drupal::logger('custom_user_notify')->notice('User %user deleted by %actor', [
        '%user' => $entity->getAccountName(),
        '%actor' => $this->currentUser->getDisplayName(),
      ]);
    }
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('current_user'),
      $container->get('settings')
    );
  }
}

