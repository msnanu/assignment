<?php

namespace Drupal\id_social_media_feeds;

/**
 * Description of SocialMediaAPIConfigData
 *
 * @author pritam.tiwari
 */
class SocialMediaAPIConfigData {

  public $config;
  public $smFeedAPIConfig = [];

  /**
   * Construct method of class.
   */
  public function __construct() {
    // Read social media api configuration.
    $this->config = \Drupal::config('socialmediafeedsapiconfig.settings');

    $this->configArray = $this->config->getRawData();
  }

  /**
   * Methode to returns all SMFeed configuration array.
   *
   * @return array
   */
  public function getSMFeedAPIConfig() {
    return $this->smFeedAPIConfig;
  }


}
