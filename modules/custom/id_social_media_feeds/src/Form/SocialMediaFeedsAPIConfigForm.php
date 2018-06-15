<?php

namespace Drupal\id_social_media_feeds\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Description of SocialMediaFeedsAPIConfigForm
 *
 * @author pritam.tiwari
 */
class SocialMediaFeedsAPIConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'socialmediafeedsapiconfig_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'socialmediafeedsapiconfig.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('socialmediafeedsapiconfig.settings');
    $accountConfigurationArray = [];

    $form['soapClientUrl'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('SOAP Client URL'),
      '#required' => TRUE,
      '#default_value' => !empty($config->soapClientUrl) ? $$config->soapClientUrl : 'http://smfeeds-ire.production.investis.com/SocialMediaService.svc?wsdl'
    );
    
    $form['domain'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Domain Name'),
      '#description' => $this->t('Domain name "http://www.abc.com"'),
      '#required' => TRUE,
      '#default_value' => !empty($config->get('domain')) ? $config->get('domain') : ''
    );

    $form['errorMailRecipient'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Error Recipent Email'),
      '#description' => $this->t('Email id where error mails will be send by API'),
      '#required' => TRUE,
      '#default_value' => !empty($config->get('errorMailRecipient')) ? $config->get('errorMailRecipient') : ''
    );
 
    
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = \Drupal::getContainer()->get('config.factory')->getEditable('socialmediafeedsapiconfig.settings');

    // Save the smfeeds api configuration.
    $config->set('soapClientUrl', $form_state->getValue('soapClientUrl'));
    $config->set('domain', $form_state->getValue('domain'));
    $config->set('errorMailRecipient', $form_state->getValue('errorMailRecipient'));
    $config->save();

    parent::submitForm($form, $form_state);

  }

}
