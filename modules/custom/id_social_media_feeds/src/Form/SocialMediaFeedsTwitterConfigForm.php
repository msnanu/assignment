<?php

namespace Drupal\id_social_media_feeds\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Description of SocialMediaFeedsTwitterConfigForm
 *
 * @author pritam.tiwari
 */
class SocialMediaFeedsTwitterConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'socialmediafeedtwitterconfig_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'socialmediafeedconfig.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = "") {
    $config = $this->config('socialmediafeedconfig.settings');
    $accountConfigurationArray = [];
    if (isset($id)) {
      $accountConfiguration = $config->get('twitter||' . $id);
      if (isset($accountConfiguration)) {
        $accountConfigurationArray = unserialize($accountConfiguration);
      }
      else {
        if($id != NULL){
          drupal_set_message("No configuration found for Id: $id", 'error');
        }
      }
    }


    /**
     * @todo need to create seprate configuration for this array item.
     */
    $tweetsFeedTypeOptions = [
      'https://api.twitter.com/1.1/statuses/user_timeline.json' => 'User Timeline',
      'https://api.twitter.com/1.1/statuses/home_timeline.json' => 'Home Timeline',
      'https://api.twitter.com/1.1/statuses/mentions_timeline.json' => 'Mentions Timeline',
    ];
    $form['channelType'] = array(
      '#type' => 'hidden',
      '#title' => $this->t('Channel Type'),
      '#default_value' => !empty($accountConfigurationArray['channelType']) ? $accountConfigurationArray['channelType'] : 'twitter'
    );
    $form['tweetsFeedType'] = array(
      '#type' => 'select',
      '#options' => $tweetsFeedTypeOptions,
      '#title' => $this->t('Feed Type to fetch'),
      '#description' => $this->t('Please select the feed type you need to fetch'),
      '#default_value' => !empty($accountConfigurationArray['tweetsFeedType']) ? $accountConfigurationArray['tweetsFeedType'] : ''
    );
    
    // Get all enabled languages in site.
    $activeLanguages = \Drupal::languageManager()->getLanguages();

    $languageOptions = ['all'=>t('All')];
    foreach($activeLanguages as $langCode => $langObj){
      $languageOptions[$langCode] = t($langObj->getName());
    }
    $form['tweetsFeedLanguage'] = array(
      '#type' => 'select',
      '#options' => $languageOptions,
      '#title' => $this->t('Account Language'),
      '#description' => $this->t('Please add language here for the twitter account.'),
      '#default_value' => !empty($accountConfigurationArray['tweetsFeedLanguage']) ? $accountConfigurationArray['tweetsFeedLanguage'] : ''
    );
    $form['tweetsUsernameOld'] = array(
      '#type' => 'hidden',
      '#default_value' => !empty($accountConfigurationArray['tweetsUsername']) ? $accountConfigurationArray['tweetsUsername'] : ''
    );
    $form['tweetsUsername'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Twitter Hashtag(@)'),
      '#description' => $this->t(''),
      '#required' => TRUE,
      '#default_value' => !empty($accountConfigurationArray['tweetsUsername']) ? $accountConfigurationArray['tweetsUsername'] : ''
    );
    $form['tweetsLimit'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#description' => $this->t(''),
      '#required' => TRUE,
      '#default_value' => !empty($accountConfigurationArray['tweetsLimit']) ? $accountConfigurationArray['tweetsLimit'] : 3
    );
    $form['accessToken'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Twitter Access token'),
      '#description' => $this->t(''),
      '#required' => TRUE,
      '#default_value' => !empty($accountConfigurationArray['accessToken']) ? $accountConfigurationArray['accessToken'] : ''
    );
    $form['tokenSecret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Twitter Token secret'),
      '#description' => $this->t(''),
      '#required' => TRUE,
      '#default_value' => !empty($accountConfigurationArray['tokenSecret']) ? $accountConfigurationArray['tokenSecret'] : ''
    );
    $form['consumerKey'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Twitter Consumer key'),
      '#description' => $this->t(''),
      '#required' => TRUE,
      '#default_value' => !empty($accountConfigurationArray['consumerKey']) ? $accountConfigurationArray['consumerKey'] : ''
    );
    $form['consumerSecret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Twitter Consumer secret'),
      '#description' => $this->t(''),
      '#required' => TRUE,
      '#default_value' => !empty($accountConfigurationArray['consumerSecret']) ? $accountConfigurationArray['consumerSecret'] : ''
    );
    /*
      $form['twitterExpiryMail'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Twitter Failure Email IDs'),
      '#description' => $this->t('Add multiple email address with comma seprated values. All the emails listed in this field will get an alert once the token will be expired'),
      '#default_value' => !empty($accountConfigurationArray['twitterExpiryMail']) ?$accountConfigurationArray['twitterExpiryMail'] : ''
      );
     
    $form['twitterFailedMailTime'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Twitter feeds Failure Email Scheduler'),
      '#description' => $this->t('Add time in minutes.<br/> E.g. If you wish to get email alerts for fail Twitter feeds response after every hour. Set the value to 60.<br/>Default Value is 1440 minutes.'),
      '#default_value' => !empty($accountConfigurationArray['twitterFailedMailTime']) ? $accountConfigurationArray['twitterFailedMailTime'] : '1440'
    );
    $form['twitter_last_mail_date'] = array(
      '#type' => 'hidden',
      '#default_value' => !empty($accountConfigurationArray['twitter_last_mail_date']) ? $accountConfigurationArray['twitter_last_mail_date'] : ''
    );
    */
    $form['#attached']['library'][] = 'id_social_media_feeds/id_social_media_feeds.id_social_media_feeds_admin';
    
    $currentPath = \Drupal::service('path.current')->getPath();
    $actionText = substr($currentPath, -3);
    if($actionText != 'add'){
      $form['delete'] = [
        '#type' => 'submit',
        '#value' => t('Delete'),
        '#attributes' => ['class' => ['delete-operation-button']]
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = \Drupal::getContainer()->get('config.factory')->getEditable('socialmediafeedconfig.settings');

    // Get button clicked
    $buttonClicked = $form_state->getTriggeringElement();
    $userName = $form_state->getValue('tweetsUsername');

    if (isset($buttonClicked['#attributes']) && isset($buttonClicked['#attributes']['class']) && isset($buttonClicked['#attributes']['class']) && (in_array('delete-operation-button', $buttonClicked['#attributes']['class']))) {
      $config->clear('twitter||'.$userName); 
      $config->save();
//      drupal_set_message(t('Configuration deleted successfuly.'));
      parent::submitForm($form, $form_state);
      return;
    }
    
    // Code to handle the username change happen
    if($form_state->getValue('tweetsUsername') != $form_state->getValue('tweetsUsernameOld')){
      // Delete the config with old username.
      $config->clear('twitter||'.$form_state->getValue('tweetsUsernameOld')); 
    }
    
    
    // Save the twitter account configuration.
    
    /**
     * structure
     * Configuration["twitter".$userName] = AccountConfiguration(Serialized string of configuration array)
     */
    
    $accountConfiguration = [
      'channelType' => $form_state->getValue('channelType'),
      'tweetsFeedType' => $form_state->getValue('tweetsFeedType'),
      'tweetsFeedLanguage' => $form_state->getValue('tweetsFeedLanguage'),
      'tweetsUsername' => $form_state->getValue('tweetsUsername'),
      'tweetsLimit' => $form_state->getValue('tweetsLimit'),
      'accessToken' => $form_state->getValue('accessToken'),
      'tokenSecret' => $form_state->getValue('tokenSecret'),
      'consumerKey' => $form_state->getValue('consumerKey'),
      'consumerSecret' => $form_state->getValue('consumerSecret'),
      //'twitterExpiryMail'=>  $form_state->getValue('twitterExpiryMail'),
//      'twitterFailedMailTime' => $form_state->getValue('twitterFailedMailTime'),
    ];

    $config->set('twitter||' . $userName, serialize($accountConfiguration));
    $config->save();

    parent::submitForm($form, $form_state);

  }

}
