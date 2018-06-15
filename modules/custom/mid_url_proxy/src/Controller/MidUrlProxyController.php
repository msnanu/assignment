<?php

namespace Drupal\mid_url_proxy\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mid_url_proxy\MidUrlProxyLib;

/**
 * Description of MidUrlProxyController.
 *
 * @author pritam.tiwari
 */
class MidUrlProxyController extends ControllerBase {

  /**
   * {@inheritdoc}
   *
   * @param string $proxyUrlKey.
   *    This is the unique key used in the url for fetching the data and cache
   *    id.
   *
   * @return array
   *   Returns the markup text.
   */
  public function displayData($proxyUrlKey) {

    $urlProxyLibObject = new MidUrlProxyLib();
    $urlProxyLibObject->midUrlProxyServer( $proxyUrlKey );
    return array(
      '#type' => 'markup',
      '#markup' => "this is the display data",
    );
  }

}
