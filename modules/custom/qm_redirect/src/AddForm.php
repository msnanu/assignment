<?php

/**
 * @file
 * Contains \Drupal\qm_redirect\AddForm.
 */

namespace Drupal\qm_redirect;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;
use \Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Language;

class AddForm extends FormBase {

  protected $id;

  function getFormId() {
    return 'qm_redirect_add';
  }

  /**
   * Add and edit form for questionmark redirect
   */
  function buildForm(array $form, FormStateInterface $form_state) {
    $language = \Drupal::languageManager()->getCurrentLanguage();

    $this->id = \Drupal::request()->get('id');
    $qm_redirect = QmRedirectStorage::get($this->id);
    $qm_redirect->type = 'questionmarkredirect';

    $form['rid'] = array(
      '#type' => 'value',
      '#value' => $qm_redirect->rid,
    );
    $form['type'] = array(
      '#type' => 'value',
      '#value' => $qm_redirect->type,
    );
    $form['hash'] = array(
      '#type' => 'value',
      '#value' => $qm_redirect->hash,
    );
    $form['source'] = array(
      '#type' => 'textfield',
      '#title' => t('From'),
      '#description' => t("Enter an internal Drupal path or path alias to redirect (e.g. %example1 or %example2). Fragment anchors (e.g. %anchor) are <strong>not</strong> allowed.", array('%example1' => 'node/123', '%example2' => 'taxonomy/term/123', '%anchor' => '#anchor')),
      '#maxlength' => 560,
      '#default_value' => $qm_redirect->rid || $qm_redirect->source ? $qm_redirect->source : '',
      '#required' => TRUE,
      '#field_prefix' => $GLOBALS['base_url'] . '/',
    );
    $form['source_options'] = array(
      '#type' => 'value',
      '#value' => isset($qm_redirect->source_options) ? unserialize($qm_redirect->source_options) : array(),
      '#tree' => TRUE,
    );
    $form['questionmarkredirect'] = array(
      '#type' => 'textfield',
      '#title' => t('To'),
      '#maxlength' => 560,
      '#default_value' => $qm_redirect->rid || $qm_redirect->questionmarkredirect ? $qm_redirect->questionmarkredirect : '',
      '#required' => TRUE,
      '#description' => t('Enter an internal Drupal path, path alias, or complete external URL (like http://example.com/) to redirect to. Use %front to redirect to the front page.', array('%front' => '<front>')),
      '#element_validate' => array(''),
    );
    $form['questionmarkredirect_options'] = array(
      '#type' => 'value',
      '#value' => isset($qm_redirect->questionmarkredirect_options) ? unserialize($qm_redirect->questionmarkredirect_options) : array(),
      '#tree' => TRUE,
    );
    $form['language'] = array(
      '#type' => 'value',
      '#value' => isset($qm_redirect->language) ? $qm_redirect->language : $language->getId(),
    );
    $form['chkdiable'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable'),
      '#description' => t('If checked then redirect will not applicable'),
      '#default_value' => $qm_redirect->rid || $qm_redirect->chkdiable ? $qm_redirect->chkdiable : '',
    );

    $form['diable_url'] = array(
      '#title' => t("Disable URL"),
      '#type' => 'textfield',
      '#default_value' => $qm_redirect->rid || $qm_redirect->diable_url ? $qm_redirect->diable_url : '',
      '#description' => t('If disable then please enter external or internal URL to redirect page'),
      '#element_validate' => array(''),
    );
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );
    return $form;
  }

  /**
   * Validate a questionmark redirect.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {


    $source = $form_state->getValue('source');
    $type = $form_state->getValue('type');
    $questionmarkredirect = $form_state->getValue('questionmarkredirect');
    $language = $form_state->getValue('language');
    $chkdiable = $form_state->getValue('chkdiable');
    $diable_url = $form_state->getValue('diable_url');

    // check that there there are no questionmark redirect loops
    if ($source == $questionmarkredirect) {

      $form_state->setErrorByName('qm_redirect', $this->t('You are attempting to redirect the page to itself. This will result in an infinite loop.'));
    }
    if (($chkdiable) && ('' == $diable_url)) {

      $form_state->setErrorByName($chkdiable, $this->t('Disable URL should not be blank.'));
    }

    if (($chkdiable) && ('' != $diable_url) && $diable_url == $source) {

      $form_state->setErrorByName($chkdiable, $this->t('Disable URL should not be same as source URL.'));
    }
    AddForm::questionmarkredirect_element_validate_source($form['source'], $form_state);
    AddForm::questionmarkredirect_element_validate_questionmarkredirect($form['questionmarkredirect'], $form_state);
    AddForm::questionmarkredirect_element_validate_questionmarkredirect($form['diable_url'], $form_state);
  }

  /**
   * Element validate handler; validate the source of an URL questionmark redirect.
   */
  function questionmarkredirect_element_validate_source($element, &$form_state) {

    $value = $element['#value'];

    // Check that the source contains no URL fragment.
    if (strpos($value, '#') !== FALSE) {
      $form_state->setErrorByName($value, $this->t('The source path cannot contain an URL fragment anchor.'));
    }

    AddForm::_questionmarkredirect_extract_url_options($element, $form_state, false);

    // Disallow questionmarkredirections from the frontpage.
    if ($value === '<front>') {
      $form_state->setErrorByName($value, $this->t('The source path cannot be the front page.'));
    }

    return $element;
  }

  /**
   * Element validate handler; validate the questionmark redirect of an URL questionmark redirect.
   */
  function questionmarkredirect_element_validate_questionmarkredirect($element, &$form_state) {
    $value = &$element['#value'];

    AddForm::_questionmarkredirect_extract_url_options($element, $form_state);

    $name = &$element['#name'];

    // Normalize the path.
    $value = \Drupal::service('path.alias_manager')->getPathByAlias($value, $form_state->getValue('language'));

    if ((!filter_var($uri, FILTER_VALIDATE_URL) === false) && $value != '<front>' && $value != '') {
      $form_state->setErrorByName($value, $this->t('The ' . $name . ' path %value is not valid.'));
    }

    return $element;
  }

  /**
   * Extract the query and fragment parts out of an URL field.
   */
  function _questionmarkredirect_extract_url_options(&$element, &$form_state, $flag = false) {

    $value = $element['#value'];

    $type = $element['#name'];

    $options = $form_state->getValue($type . "_options");


    $parsed = AddForm::questionmarkredirect_parse_url($value);

    if (isset($parsed['fragment'])) {
      $options['fragment'] = $parsed['fragment'];
    }
    else {
      unset($options['fragment']);
    }

    if (isset($parsed['query'])) {
      $options['query'] = $parsed['query'];
    }
    else {
      unset($options['query']);
    }

    if (isset($parsed['scheme']) && $parsed['scheme'] == 'https') {
      $options['https'] = TRUE;
    }
    else {
      unset($options['https']);
    }


    //Need to check external URl here
    if ($parsed['url']) {
      $parsed['url'] = \Drupal::service('path.alias_manager')->getPathByAlias($parsed['url'], $form_state->getValue('language'));
    }

    if (!$flag) {
      $element['#value'] = $parsed['url'];
      $form_state->setValue($type . "_options", $options);
    }
    return $parsed;
  }

  /**
   * Parse URL.
   */
  function questionmarkredirect_parse_url($url) {

    $original_url = $url;
    $url = trim($url, " \t\n\r\0\x0B\/");
    $parsed = parse_url($url);

    if (isset($parsed['fragment'])) {
      $url = substr($url, 0, -strlen($parsed['fragment']));
      $url = trim($url, '#');
    }
    if (isset($parsed['query'])) {
      $url = substr($url, 0, -strlen($parsed['query']));
      $url = trim($url, '?&');
      $parsed['query'] = drupal_get_query_array($parsed['query']);
    }

    // Convert absolute to relative.
    if (isset($parsed['scheme']) && isset($parsed['host'])) {
      $base_secure_url = rtrim($GLOBALS['base_secure_url'] . base_path(), '/');
      $base_insecure_url = rtrim($GLOBALS['base_insecure_url'] . base_path(), '/');
      if (strpos($url, $base_secure_url) === 0) {
        $url = str_replace($base_secure_url, '', $url);
        $parsed['https'] = TRUE;
      }
      elseif (strpos($url, $base_insecure_url) === 0) {
        $url = str_replace($base_insecure_url, '', $url);
      }
    }

    $url = trim($url, '/');

    // Convert to frontpage paths.
    if ($url == '<front>') {
      $url = '';
    }

    $parsed['url'] = $url;

    // Allow modules to alter the parsed URL.
    \Drupal::moduleHandler()->alter('questionmarkredirect_parse_url', $parsed, $original_url);

    return $parsed;
  }

  /**
   * Sort an array recusively.
   *
   * @param $array
   *   The array to sort, by reference.
   * @param $callback
   *   The sorting callback to use (e.g. 'sort', 'ksort', 'asort').
   *
   * @return
   *   TRUE on success or FALSE on failure.
   */
  function questionmarkredirect_sort_recursive(&$array, $callback = 'sort') {
    $result = $callback($array);
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $result &= questionmarkredirect_sort_recursive($array[$key], $callback);
      }
    }
    return $result;
  }

  function questionmarkredirect_hash($questionmarkredirect) {
    $hash = array(
      'source' => $questionmarkredirect->source,
      'language' => $questionmarkredirect->language,
    );
    if (!empty($questionmarkredirect->source_options['query'])) {
      $hash['source_query'] = $questionmarkredirect->source_options['query'];
    }
    \Drupal::moduleHandler()->alter('questionmarkredirect_hash', $hash, $questionmarkredirect);

    AddForm::questionmarkredirect_sort_recursive($hash, 'ksort');

    $hash = base64_encode(hash('sha256', serialize($hash), TRUE));
    $questionmarkredirect->hash = strtr($hash, array('+' => '-', '/' => '_', '=' => ''));



    return $questionmarkredirect->hash;
  }

  /**
   * Build the URL of a questionmark redirect for display purposes only.
   */
  function questionmarkredirect_url($path, array $options = array(), $clean_url = NULL) {


    if (!isset($clean_url)) {

      $clean_url = \Drupal::state()->get('clean_url');
    }

    if (!isset($options['alter']) || !empty($options['alter'])) {
      \Drupal::moduleHandler()->alter('questionmarkredirect_url', $path, $options);
    }

    // The base_url might be rewritten from the language rewrite in domain mode.
    if (!isset($options['base_url'])) {

      if (isset($options['https']) && \Drupal::config('system.site')->get('https') != "") {

        if ($options['https'] === TRUE) {
          $options['base_url'] = $GLOBALS['base_secure_url'];
          $options['absolute'] = TRUE;
        }
        elseif ($options['https'] === FALSE) {
          $options['base_url'] = $GLOBALS['base_insecure_url'];
          $options['absolute'] = TRUE;
        }
      }
      else {
        $options['base_url'] = $GLOBALS['base_url'];
      }
    }

    if (empty($options['absolute']) || !filter_var($path, FILTER_VALIDATE_URL) === false) {
      $url = $path;
    }
    else {
      $url = $options['base_url'] . base_path() . $path;
    }

    if (isset($options['query'])) {
      $url .= $clean_url ? '?' : '&';
      $url .= drupal_http_build_query($options['query']);
    }
    if (isset($options['fragment'])) {
      $url .= '#' . $options['fragment'];
    }

    return $url;
  }

  function submitForm(array &$form, FormStateInterface $form_state) {

    global $user;
    $message = "";
    $rid = $form_state->getValue('rid');
    $source = $form_state->getValue('source');
    $source = $form_state->getValue('source');
    $type = $form_state->getValue('type');
    $qredirect = $form_state->getValue('questionmarkredirect');
    $language = $form_state->getValue('language');
    $chkdiable = $form_state->getValue('chkdiable');
    $diable_url = $form_state->getValue('diable_url');

    $qm_redirect = (object) $form_state->getValues();

    AddForm::questionmarkredirect_url($source, $qm_redirect->source_options);
    AddForm::questionmarkredirect_url($qredirect, $qm_redirect->questionmarkredirect_options);

    if ($rid == '') { // if new url added
      $message .=t('URL Redirect added successfully by @user_name for : @source_name');
    }
    else {
      if (strcmp($form['source']['#default_value'], $form['source']['#value']) != 0) {
        $message .= t('@user_name has updated the source from : @source_prv to @source_value.') . '<br>';
      }
      if (strcmp($form['questionmarkredirect']['#default_value'], $form['questionmarkredirect']['#value']) != 0) {
        $message .= t('@user_name has updated the redirect url from @dest_prv to @dest_value .') . '<br>';
      }
    }
    if ($message != '') {
      \Drupal::logger('qm_redirect')->notice($message, array(
        '@user_name' => $user->name,
        '@source_name' => $source,
        '@source_prv' => $form['source']['#default_value'],
        '@source_value' => $form['source']['#value'],
        '@dest_prv' => $form['questionmarkredirect']['#default_value'],
        '@dest_value' => $form['questionmarkredirect']['#value']
      ));
    }
    AddForm::questionmarkredirect_save($qm_redirect);


    drupal_set_message(t('The redirect has been saved.'));
    $form_state->setRedirect('qm_redirect_listing');
    return;
  }

  function questionmarkredirect_save($questionmarkredirect) {
    $transaction = db_transaction();

    try {
      if (!empty($questionmarkredirect->rid) && !isset($questionmarkredirect->original)) {
        $questionmarkredirect->original = $questionmarkredirect->rid;
      }

      // Determine if we will be inserting a new node.
      if (!isset($questionmarkredirect->is_new)) {
        $questionmarkredirect->is_new = empty($questionmarkredirect->rid);
      }

      AddForm::questionmarkredirect_hash($questionmarkredirect);
      if ($questionmarkredirect->is_new || $questionmarkredirect->hash != $questionmarkredirect->original->hash) {
        // Only new or changed redirects reset the last used value.
        $questionmarkredirect->count = 0;
        $questionmarkredirect->access = 0;
      }
      // Save the questionmarkredirect to the database and invoke the post-save hooks.

      $questionmarkredirect->status_code = 0;

      if ($questionmarkredirect->is_new) {

        QmRedirectStorage::add($questionmarkredirect);
      }
      else {

        QmRedirectStorage::edit($questionmarkredirect);
      }

      // Clear internal properties.
      unset($questionmarkredirect->is_new);
      unset($questionmarkredirect->original);
    } catch (Exception $e) {
      $transaction->rollback();
      watchdog_exception('questionmarkredirect', $e);
      throw $e;
    }
  }

}
