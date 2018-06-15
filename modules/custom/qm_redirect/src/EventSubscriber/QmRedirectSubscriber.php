<?php

namespace Drupal\qm_redirect\EventSubscriber;

use Drupal;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;

class QmRedirectSubscriber implements EventSubscriberInterface {

  /**
   * Redirection function.
   */
  public function checkForRedirection(GetResponseEvent $event) {


    $can_redirect = &drupal_static(__FUNCTION__);

    if (!isset($can_redirect)) {
      $path = \Drupal::service('path.current')->getPath();
      $can_redirect = TRUE;
      $maintenance_mode = \Drupal::state()->get('maintenance_mode');

      if ($_SERVER['SCRIPT_NAME'] != $GLOBALS['base_path'] . 'index.php') {
        // Do not questionmark redirect if the root script is not /index.php.
        $can_redirect = FALSE;
      }
      elseif (!empty($_POST)) {
        // Do not questionmark redirect if this is a post request with data.
        $can_redirect = FALSE;
      }
      elseif ($maintenance_mode) {
        // Do not questionmark redirect in offline or maintenance mode.
        $can_redirect = FALSE;
      }
      elseif (\Drupal::service('router.admin_context')->isAdminRoute()) {
        // Do not questionmark redirect on admin paths.
        $can_redirect = FALSE;
      }
    }

    if (!$can_redirect) {
      return;
    }


    $current_path = \Drupal::service('path.current')->getPath();

    if (strpos($current_path, '/') == 0) {
      $current_path = substr($current_path, 1);
    }

    $result = db_query('SELECT * FROM {qm_redirect} WHERE source like :src', array(':src' => $current_path))->fetchAssoc();

    if ($result) {
      if ($result['chkdiable'] == 1) {
        $url = QmRedirectSubscriber::get_valid_url($result['diable_url']);
      }
      else {
        $url = QmRedirectSubscriber::get_valid_url($result['questionmarkredirect']);
      }

      $event->setResponse(new TrustedRedirectResponse($url, 301));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {

    //print request_path();  
    $events[KernelEvents::REQUEST][] = array('checkForRedirection');

    return $events;
  }

  /**
   * Get valid URL.
   */
  public function get_valid_url($uri) {
    if (!filter_var($uri, FILTER_VALIDATE_URL) === false) {
      $url = $uri;
    }
    else {


      if ($uri == '<front>') {
        $site_frontpage = \Drupal::config('system.site')->get('site_frontpage');
        $url = $GLOBALS['base_url'];
      }
      else {
        $url = $GLOBALS['base_url'] . '/' . $uri;
      }
    }

    return $url;
  }

}
