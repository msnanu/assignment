<?php

namespace Drupal\mid_url_proxy\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UrlProxySettingsForm.
 *
 * @package Drupal\mid_url_proxy\Form
 *
 * @ingroup mid_url_proxy
 */
class UrlProxySettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'mid_url_proxy_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['url_proxy_settings']['#markup'] = 'Settings form for Mid Url Proxy. Manage field settings here.';
    return $form;
  }

}
