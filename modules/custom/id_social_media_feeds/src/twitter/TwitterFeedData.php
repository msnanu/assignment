<?php

namespace Drupal\id_social_media_feeds\twitter;

use Drupal\id_social_media_feeds\smfeeds\SMFeedsApi;
use Drupal\id_social_media_feeds\SocialMediaConfigData;
use Drupal\id_social_media_feeds\SocialMediaAPIConfigData;

/**
 * Description of TwitterFeedData
 *
 * @author pritam.tiwari
 */
class TwitterFeedData {

  public $soapClient;
  public $apiConfig;

  public function __construct() {

    $configObj = new SocialMediaAPIConfigData();
    $this->apiConfig = $configObj->getSMFeedAPIConfig();

    $soapClientUrl = isset($this->apiConfig['soapClientUrl']) ? $this->apiConfig['soapClientUrl'] : 'http://smfeeds-ire.production.investis.com/SocialMediaService.svc?wsdl';

    // Soap client for smfeed wsdl call.
    $this->soapClient = new \SoapClient($soapClientUrl);
  }

  /**
   * Methode to fetch the data using SMFeeds API.
   * 
   * @param type $twitterConfigArray
   * @return type
   */
  private function getData($twitterConfigArray) {

    $feedsArray = [];
    $smFeedApiObj = new SMFeedsApi($this->soapClient);
    foreach ($twitterConfigArray as $accountConfigData) {
      // Call to SMFeeds API here.
      $feedsData = $smFeedApiObj->getFeedData($accountConfigData, 'twitter', $this->apiConfig);
      if ($feedsData != NULL) {
        $feedsData = $feedsData['post'];

        $feedLocalArray = [];
        // Mapping of the response of API with local.
        foreach ($feedsData as $key => $feedData) {

          // Keep the key mapping consistence with the 
          if (isset($feedData['postid'])) {
            $feedLocalArray[$key]['postid'] = $feedData['postid'];
          }

          if (isset($feedData['title'])) {
            /* Code for remove last url from tweet post */
            $arrcommnet = (explode("https://", $feedData['title']));
            $arrcommnetLastUrl = array_pop($arrcommnet);
            $feedData['title'] = str_replace("https://" . $arrcommnetLastUrl, '', $feedData['title']);
            /*             * End* */

            $feedLocalArray[$key]['title'] = $feedData['title'];
          }
          if (isset($feedData['description'])) {
            $feedLocalArray[$key]['description'] = $feedData['description'];
          }
          if (isset($feedData['tweetsFeedLanguage'])) {
            $feedLocalArray[$key]['tweetsFeedLanguage'] = $feedData['tweetsFeedLanguage'];
          }
          if (isset($feedData['thumbimageurl'])) {
            $feedLocalArray[$key]['thumbimageurl'] = $feedData['thumbimageurl'];
          }
          if (isset($feedData['mediumimageurl'])) {
            $feedLocalArray[$key]['mediumimageurl'] = $feedData['mediumimageurl'];
          }
          if (isset($feedData['largeimageurl'])) {
            $feedLocalArray[$key]['largeimageurl'] = $feedData['largeimageurl'];
          }
          if (isset($feedData['posturl'])) {
            $feedLocalArray[$key]['posturl'] = $feedData['posturl'];
          }
          if (isset($feedData['rawposturl'])) {
            $feedLocalArray[$key]['rawposturl'] = $feedData['rawposturl'];
          }
          if (isset($feedData['embedurl'])) {
            $feedLocalArray[$key]['embedurl'] = $feedData['embedurl'];
          }
          if (isset($feedData['posteddate'])) {
            $feedLocalArray[$key]['posteddate'] = $this->formatPostDate($feedData['posteddate']);
          }
          if (isset($feedData['likecount'])) {
            $feedLocalArray[$key]['likecount'] = $feedData['likecount'];
          }
          if (isset($feedData['commentcount'])) {
            $feedLocalArray[$key]['commentcount'] = $feedData['commentcount'];
          }
          if (isset($feedData['noofviews'])) {
            $feedLocalArray[$key]['noofviews'] = $feedData['noofviews'];
          }
          if (isset($feedData['tags'])) {
            $feedLocalArray[$key]['tags'] = $feedData['tags'];
          }
          if (isset($feedData['@attributes']['channel'])) {
            $feedLocalArray[$key]['attributesData']['channel'] = $feedData['@attributes']['channel'];
          }
          if (isset($feedData['@attributes']['settingname'])) {
            $feedLocalArray[$key]['attributesData']['settingname'] = $feedData['@attributes']['settingname'];
          }


          $postUrlArray = explode("/", $feedData['posturl']);
          // Home timeline post author
          if (isset($postUrlArray[1]) && ($feedData['@attributes']['settingname'] != $postUrlArray[1])) {
            $feedLocalArray[$key]['postauthor'] = $postUrlArray[1];
          }
        }

        $feedsArray = array_merge($feedsArray, $feedLocalArray);
      } else {
        \Drupal::logger('id_social_media_feeds')->error(t('No data found'));
      }
    }
    return $feedsArray;
  }

  /**
   * Method to get the twitter feeds in array format for multiple accounts configured.
   * 
   * @return type
   */
  public function getFeedData() {

    // Store channelwize account configurations.
    $twitterConfigArray = [];

    $configObj = new SocialMediaConfigData();

    $twitterConfigArray = $configObj->getTwitterChannelConfig();
//    $twitterConfigArray = $configObj->getChannelConfig('twitter');

    return $this->getData($twitterConfigArray);
  }

  
  /**
   * Method to give different date formats as per the post needed.
   *
   * @param type $dateString
   * @return string
   */
  public function formatPostDate($dateString) {
    $postDate = date('Y-m-d H:i:s', strtotime($dateString));
    $currentDate = date('Y-m-d H:i:s', time());
    
    $currentParsedDate = date_parse($currentDate);
    $parsedDate = date_parse($postDate);

    if ($parsedDate['year'] < $currentParsedDate['year']) {
      $dateFormat = 'd M Y';
    } elseif (($parsedDate['month'] == $currentParsedDate['month']) && ($parsedDate['day'] == $currentParsedDate['day']) && ($parsedDate['hour'] != $currentParsedDate['hour'])) {
      return $currentParsedDate['hour'] - $parsedDate['hour'] . "h";
    } elseif (($parsedDate['month'] == $currentParsedDate['month']) && ($parsedDate['day'] == $currentParsedDate['day']) && ($parsedDate['hour'] == $currentParsedDate['hour'])) {
      if (($currentParsedDate['minute'] - $parsedDate['minute']) > 0) {
        return $currentParsedDate['minute'] - $parsedDate['minute'] . "m";
      } else {
        return "1m";
      }
    } else {
      $dateFormat = 'M d';
    }

    return date($dateFormat, strtotime($postDate));
  }

}
