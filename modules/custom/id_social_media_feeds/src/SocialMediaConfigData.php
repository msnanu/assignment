<?php

namespace Drupal\id_social_media_feeds;

/**
 * Description of SocialMediaConfigData
 *
 * @author pritam.tiwari
 */
class SocialMediaConfigData {

  public $config;
  public $configArray = [];
  
  // Store channelwize account configurations.
  public $twitterConfigArray = [];
  public $linkedinConfigArray = [];
  public $facebookConfigArray = [];
  public $instagramConfigArray = [];

  /**
   * Construct method of class.
   */
  public function __construct() {
    // Read social media configuration.
    $this->config = \Drupal::config('socialmediafeedconfig.settings');

    $this->configArray = $this->config->getRawData();
    
    // Function to read the configration and store in atrribs.
    $this->readConfig();
  }

  /**
   * Method to read and filter the configuration as per channels.
   */
  private function readConfig() {
    foreach ($this->configArray as $key => $config) {
      if (strpos($key, 'twitter') == 0) {
        $this->twitterConfigArray[] = unserialize($config);
      }
      elseif (strpos($key, 'linkedin') == 0) {
        $this->linkedinConfigArray[] = unserialize($config);
      }
      elseif (strpos($key, 'facebook') == 0) {
        $this->facebookConfigArray[] = unserialize($config);
      }
      elseif (strpos($key, 'instagram') == 0) {
        $this->instagramConfigArray[] = unserialize($config);
      }
    }
  }

  /**
   * Methode to returns all Twitter configuration array.
   *
   * @return array
   */
  public function getTwitterChannelConfig() {
    return $this->twitterConfigArray;
  }

  /**
   * Methode to return all Facebook configuration array.
   *
   * @return array
   */
  public function getFacebookChannelConfig() {
    return $this->facebookConfigArray;
  }

  /**
   * Methode to return all Linkedin configuration array.
   * 
   * @return array
   */
  public function getLinkedinChannelConfig() {
    return $this->linkedinConfigArray;
  }

  /**
   * Methode to return all the Instagram configuration array.
   *
   * @return array
   */
  public function getInstagramChannelConfig() {
//    if (empty($this->instagramConfigArray)) {
//      $this->readConfig();
//    }
    return $this->instagramConfigArray;
  }

  /**
   * Methode to return all the configuration for social media.
   *
   * @return array
   */
  public function getAllChannelConfig() {

    $this->readConfig();
    return [
      'twitter' => $this->twitterConfigArray,
      'facebook' => $this->facebookConfigArray,
      'linkedin' => $this->linkedinConfigArray,
      'instagram' => $this->instagramConfigArray,
    ];
  }

  // Common funciton for all the channels.
//  public function getChannelConfig($channel) {
//    $channelProperty = $channel . "ConfigArray";
//    if (property_exists($this, $channelProperty)) {
//      return $this->$channelProperty;
//    }
//  }

}
