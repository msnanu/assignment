<?php

namespace Drupal\id_social_media_feeds\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Description of SocialMediaFeedsLinkedinConfigForm
 *
 * @author pritam.tiwari
 */
class SocialMediaFeedsLinkedinConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'socialmediafeedlinkedinconfig_admin_settings';
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
      '#default_value' => !empty($config->get('channel_type')) ? $config->get('channel_type') : 'linkedin'
    );
    $form['channel_type'] = array(
      '#type' => 'hidden',
      '#title' => $this->t('Channel Type'),
      '#default_value' => !empty($config->get('channel_type')) ? $config->get('channel_type') : 'linkedin'
    );

    $form['linkedin_companyid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('LinkedIn Company Id'),
      '#description' => $this->t(''),
      '#default_value' => !empty($config->get('linkedin_companyid')) ? $config->get('linkedin_companyid') : ''
    );
    $form['linkedin_clientid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('LinkedIn Client Id'),
      '#description' => $this->t(''),
      '#default_value' => !empty($config->get('linkedin_clientid')) ? $config->get('linkedin_clientid') : ''
    );
    $form['linkedin_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('LinkedIn Secret'),
      '#description' => $this->t(''),
      '#default_value' => !empty($config->get('linkedin_secret')) ? $config->get('linkedin_secret') : ''
    );
    $form['linkedin_redirecturl'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('LinkedIn Redirect URL'),
      '#description' => $this->t(''),
      '#default_value' => !empty($config->get('linkedin_redirecturl')) ? $config->get('linkedin_redirecturl') : ''
    );
    $form['linkedin_access_token'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('LinkedIn Access Token'),
      '#description' => $this->t('Generate access token and paste it here.'),
      '#default_value' => !empty($config->get('linkedin_access_token')) ? $config->get('linkedin_access_token') : ''
    );
    $form['linkedin_expiry_mail'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Failure Email IDs'),
      '#description' => $this->t('Add multiple email address with comma seprated values. All the emails listed in this field will get an alert once the token will be expired'),
      '#default_value' => !empty($config->get('linkedin_expiry_mail')) ? $config->get('linkedin_expiry_mail') : ''
    );
    $form['linkedin_faile_mail_time'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('LinkedIn Feed Failure Email Scheduler'),
      '#description' => $this->t('Add time in minutes.<br/> E.g. If you wish to get email alerts for fail LinkedIn feed response after every hour. Set the value to 60.<br/>Default Value is 1440 minutes.'),
      '#default_value' => !empty($config->get('linkedin_faile_mail_time')) ? $config->get('linkedin_faile_mail_time') : '1440'
    );
    $form['linkedin_last_mail_date'] = array(
      '#type' => 'hidden',
      '#default_value' => !empty($config->get('linkedin_last_mail_date')) ? $config->get('linkedin_last_mail_date') : ''
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


//    $prev_config = $this->config('socialmediafeedconfig.settings');
//    if($form_state->getValue('linkedin_access_token') != $prev_config->get('linkedin_access_token')) {
//        $this->config('socialmediafeedconfig.settings')
//          // Set the date value hidden field if there is change in the token
//          ->set('linkedin_last_mail_date', time())
//          ->save();
//    }

    $this->config('socialmediafeedconfig.settings')
      // Set the submitted configuration setting
      ->set('channel_type', $form_state->getValue('channel_type'))
      ->set('linkedin_companyid', $form_state->getValue('linkedin_companyid'))
      ->set('linkedin_clientid', $form_state->getValue('linkedin_clientid'))
      ->set('linkedin_secret', $form_state->getValue('linkedin_secret'))
      ->set('linkedin_expiry_mail', $form_state->getValue('linkedin_expiry_mail'))
      ->set('linkedin_access_token', $form_state->getValue('linkedin_access_token'))
      ->set('linkedin_redirecturl', $form_state->getValue('linkedin_redirecturl'))
      ->set('linkedin_faile_mail_time', $form_state->getValue('linkedin_faile_mail_time'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
