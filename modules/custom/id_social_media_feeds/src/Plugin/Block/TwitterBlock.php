<?php

/**
 * @file
 * Contains \Drupal\id_social_media_feeds\Plugin\Block\TwitterBlock.
 */

namespace Drupal\id_social_media_feeds\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\id_social_media_feeds\twitter\TwitterFeedData;


/**
 * Provides a 'TwitterBlock' block.
 *
 * @Block(
 *  id = "twitter_block",
 *  admin_label = @Translation("Twitter block"),
 * )
 */
class TwitterBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    
    $feed = [];
    $feedsArray = [];

    $twitterFeedDataObject = new TwitterFeedData();

    $feedsArray = $twitterFeedDataObject->getFeedData();
    if(isset($feedsArray)){
      $feed['twitter'] = $feedsArray;
    }

    $build = array(
      '#theme' => 'tweets',
      '#doubles' => array('key' => $feed),
        '#cache' => array(
          'max-age' => 0, // seconds
        ),
    );
    return $build;
  }

}