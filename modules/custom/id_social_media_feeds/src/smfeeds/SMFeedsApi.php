<?php

namespace Drupal\id_social_media_feeds\smfeeds;

/**
 * Description of SMFeedsApi
 *
 * @author pritam.tiwari
 */
class SMFeedsApi {

  /**
   * Soap client class object reference.
   *
   * @var type Object reference.
   */
  private $soapClientObj;

  /**
   * Construct function of class.
   *
   * @param type $soapClient
   *   Soap client object reference.
   */
  public function __construct(&$soapClient) {
    $this->soapClientObj = $soapClient;
  }

  /**
   * Methode to call SMFeed API for different channels available.
   *
   * @param array $accountConfigData
   *   Stores the array of account configuration from the site.
   *
   * @param string $channel
   *   Channel name string.
   *
   * @return array
   *   Feed data available from SMFeed API.
   * 
   */
  public function getFeedData($accountConfigData, $channel, $apiConfig) {
    switch ($channel) {

      // Twitter feed data.
      case 'twitter' :

        $currentLanguageCode = \Drupal::languageManager()->getCurrentLanguage()->getId();



        $channelType = $accountConfigData['channelType'];
        $tweetsFeedType = $accountConfigData['tweetsFeedType'];
        $tweetsUsername = $accountConfigData['tweetsUsername'];
        $tweetsFeedLanguage = $accountConfigData['tweetsFeedLanguage'];
        $tweetsLimit = $accountConfigData['tweetsLimit'];
        $accessToken = $accountConfigData['accessToken'];
        $tokenSecret = $accountConfigData['tokenSecret'];
        $consumerKey = $accountConfigData['consumerKey'];
        $consumerSecret = $accountConfigData['consumerSecret'];
//        $twitterFailedMailTime = $accountConfigData['twitterFailedMailTime'];

        if (in_array($tweetsFeedLanguage, ['all', $currentLanguageCode])) {

          // Code to call Twitter from smfeeds wsdl API
          // Twitter data
          $params = array(
            'SettingName' => $tweetsUsername,
            'Channel' => $channelType,
            'apiurl' => $tweetsFeedType . "@@accesstoken=$accessToken&accesstokensecret=$tokenSecret&consumerkey=$consumerKey&consumerkeysecret=$consumerSecret@@screen_name=$tweetsUsername&count=$tweetsLimit&tweet_mode=extended&debug=1&exclude_replies=true",
            'domain' => isset($apiConfig['domain'])?$apiConfig['domain']:"",
            'errorMailRecipient' => isset($apiConfig['errorMailRecipient'])?$apiConfig['errorMailRecipient']:"",
            'isRawFeed' => false,
          );

          $response = $this->soapClientObj->GetSingleChannelFeeds($params);
          
          $feedsData = "";
          if (isset($response->GetSingleChannelFeedsResult->string[1]) && ($response->GetSingleChannelFeedsResult->string[1] == "text/xml")) {
            $xmlData = simplexml_load_string($response->GetSingleChannelFeedsResult->string[0]);

            $jsonData = json_encode($xmlData);
            $feedsData = json_decode($jsonData, TRUE);
            return $feedsData;
          } else {
            // Log error 
            $errorMessageObj = json_decode($response->GetSingleChannelFeedsResult->string[0]);
            $errorCode = $errorMessageObj->errors[0]->code;
            $errorMessage = $errorMessageObj->errors[0]->message;
            $message = "Twitter: Username: $tweetsUsername. Error Code: $errorCode. Error Message: $errorMessage";
            \Drupal::logger('id_social_media_feeds')->error($message);
            
            // Sample of error message generated in logger
            // 	Twitter: Username: Pritam92597204asdf. Error Code: 34. Error Message: Sorry, that page does not exist.
            // 	Twitter: Username: Pritam92597204. Error Code: 215. Error Message: Bad Authentication data.
            // 	Twitter: Username: Pritam92597204. Error Code: 32. Error Message: Could not authenticate you.
            // 	Twitter: Username: Pritam92597204. Error Code: 88. Error Message: Rate limit exceeded.
            
          }
        }
        break;

      // Fecebook feed data.
      case 'facebook':

        break;

      // Linkedin feed data.
      case 'linkedin':

        break;

      // Instagram feed data.
      case 'instagram':

        break;

      default:
        drupal_set_message($channel . ':Channel not found', 'notice');
        break;
    }
  }

}
