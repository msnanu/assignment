<?php

namespace Drupal\id_social_media_feeds\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Description of SocialMediaFeedsFacebookConfigForm
 *
 * @author pritam.tiwari
 */
class SocialMediaFeedsFacebookConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'socialmediafeedfacebookconfig_admin_settings';
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('socialmediafeedconfig.settings');

    $form['channel_type'] = array(
      '#type' => 'hidden',
      '#title' => $this->t('Channel Type'),
      '#default_value' => !empty($config->get('channel_type')) ? $config->get('channel_type') : 'facebook'
    );

    $form['facebook_appid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Facebook AppId'),
      '#description' => $this->t(''),
      '#default_value' => !empty($config->get('facebook_appid')) ? $config->get('facebook_appid') : ''
    );
    $form['facebook_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Facebook Secret'),
      '#description' => $this->t(''),
      '#default_value' => !empty($config->get('facebook_secret')) ? $config->get('facebook_secret') : ''
    );
    $form['facebook_pageid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Facebook PageId'),
      '#description' => $this->t(''),
      '#default_value' => !empty($config->get('facebook_pageid')) ? $config->get('facebook_pageid') : ''
    );
    $form['facebook_pagename'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Facebook PageName'),
      '#description' => $this->t(''),
      '#default_value' => !empty($config->get('facebook_pagename')) ? $config->get('facebook_pagename') : ''
    );
    $form['facebook_failed_mail_time'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Facebook feeds Failure Email Scheduler'),
      '#description' => $this->t('Add time in minutes.<br/> E.g. If you wish to get email alerts for fail Twitter feeds response after every hour. Set the value to 60.<br/>Default Value is 1440 minutes.'),
      '#default_value' => !empty($config->get('facebook_failed_mail_time')) ? $config->get('facebook_failed_mail_time') : '1440'
    );
    $form['facebook_last_mail_date'] = array(
      '#type' => 'hidden',
      '#default_value' => !empty($config->get('facebook_last_mail_date')) ? $config->get('facebook_last_mail_date') : ''
    );

    $currentPath = \Drupal::service('path.current')->getPath();
    $actionText = substr($currentPath, -3);
    if($actionText != 'add'){
      $form['delete'] = [
        '#type' => 'submit',
        '#value' => 'Delete',
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('socialmediafeedconfig.settings')
      // Set the submitted configuration setting
      ->set('channel_type', $form_state->getValue('channel_type'))
      ->set('facebook_appid', $form_state->getValue('facebook_appid'))
      ->set('facebook_secret', $form_state->getValue('facebook_secret'))
      ->set('facebook_pageid', $form_state->getValue('facebook_pageid'))
      ->set('facebook_pagename', $form_state->getValue('facebook_pagename'))
      ->set('facebook_last_mail_date', $form_state->getValue('facebook_last_mail_date'))
      ->set('facebook_failed_mail_time', $form_state->getValue('facebook_failed_mail_time'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
