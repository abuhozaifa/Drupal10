<?php

namespace Drupal\google_sso\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\google_sso\Service\GoogleClientService;
use Drupal\user\Entity\User;

class GoogleSSOController extends ControllerBase {

  /**
   * Google service
   *
   * @var \Drupal\google_sso\Service\GoogleClientService
   */
  protected $googleClient;

  /**
   * Constructor
   */
  public function __construct(
    GoogleClientService $googleClient
  ) {
    $this->googleClient = $googleClient;
  }

  /**
   * Dependency Injection
   */
  public static function create(
    ContainerInterface $container
  ) {
    return new static(
      $container->get('google_sso.client')
    );
  }

  /**
   * Redirect to Google
   */
  public function login() {

    $client = $this->googleClient->getClient();

    $url = $client->createAuthUrl();

    // External URL ke liye
    return new TrustedRedirectResponse($url);

  }

  /**
   * Google callback
   */
  public function callback() {

    if (empty($_GET['code'])) {

      $this->messenger()->addError(
        $this->t('Google login failed')
      );

      return new RedirectResponse('/user/login');
    }

    try {

      $client = $this->googleClient->getClient();

      $token = $client
        ->fetchAccessTokenWithAuthCode(
          $_GET['code']
        );

      if (isset($token['error'])) {

        $this->messenger()->addError(
          $token['error']
        );

        return new RedirectResponse(
          '/user/login'
        );

      }

      $client->setAccessToken($token);

      $service =
      new \Google_Service_Oauth2(
        $client
      );

      $googleUser =
      $service
      ->userinfo
      ->get();

      $email =
      $googleUser->email;

      if (!$email) {

        return new RedirectResponse(
          '/user/login'
        );

      }

      $user =
      user_load_by_mail(
        $email
      );

      if (!$user) {

        $username =
        explode('@',$email)[0];

        $user = User::create([
          'name' => $username,
          'mail' => $email,
          'status' => 1,
        ]);

        $user->save();

      }

      user_login_finalize($user);

      return new RedirectResponse(
        '/user'
      );

    }
    catch (\Exception $e) {

      \Drupal::logger(
        'google_sso'
      )->error(
        $e->getMessage()
      );

      $this->messenger()
      ->addError(
        $e->getMessage()
      );

      return new RedirectResponse(
        '/user/login'
      );

    }

  }

}