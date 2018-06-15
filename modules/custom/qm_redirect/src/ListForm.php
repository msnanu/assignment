<?php

/**
 * @file
 * Contains \Drupal\qm_redirect\ImportForm.
 */

namespace Drupal\qm_redirect;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;
use Drupal\Core\Form\FormBuilderInterface;

class ListForm extends FormBase {

  protected $id;

  function getFormId() {
    return 'qm_redirect_listing';
  }

  /**
   * Build form for list and filter records.
   */
  function buildForm(array $form, FormStateInterface $form_state) {

    $url = Url::fromRoute('qm_redirect_add');
    $url_import = Url::fromRoute('qm_redirect_import');

    $add_import_link = '<p>' . \Drupal::l(t('Add Redirect'), $url) . "  |  " . \Drupal::l(t('Import File'), $url_import) . '</p>';
    $tempstore = \Drupal::service('user.private_tempstore')->get('qm_redirect');
    $searchtext = $tempstore->get('searchtext');


    $form['id'] = 'filter';
    $form['link'] = array(
      '#type' => 'markup',
      '#markup' => $add_import_link,
    );


    $form['searchtext'] = array(
      '#title' => t("FILTER REDIRECTS"),
      '#type' => 'textfield',
      '#size' => '40',
      '#default_value' => $searchtext,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Filter',
      '#attributes' => array('id' => 'filter'),
    );

    $form['clear'] = array(
      '#name' => 'clear',
      '#type' => 'submit',
      '#value' => 'Reset',
    );

    $build['operations'] = array(
      '#type' => 'fieldset',
      '#title' => t('Update options'),
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
      '#attributes' => array(
        'class' => array('questionmarkredirect-list-operations'),
      ),
    );
    $operations = array('Delete');
    foreach ($build['#operations'] as $key => $operation) {
      $operations[$key] = $operation['action'];
    }
    $build['operations']['operation'] = array(
      '#type' => 'select',
      '#options' => $operations,
      '#default_value' => 'delete',
    );
    $build['operations']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Update'),
      '#validate' => array('questionmarkredirect_list_form_operations_validate'),
    );


    $content = ListForm::redirect_list($searchtext);


    return array($form, $build, $content);
  }

  /**
   * submit form for list and filter records.
   */
  function submitForm(array &$form, FormStateInterface $form_state) {

    if ($form_state->getValue('op') == "") {
      $form_state->setValue('searchtext', '');
    }
    elseif ($form_state->getValue('op') == "Filter") {
      $searchtext = $form_state->getValue('searchtext');
    }
    elseif ($form_state->getValue('op') == "Update") {
      $rids = $form_state->getValue('table');


      $rid = array();
      foreach ($rids as $id => $rd) {
        if ($rd != 0)
          $rid[] = $rd;
      }
      if (count($rid) == 0) {
        //   $form_state->setErrorByName('qm_redirect', $this->t('The source path cannot contain an URL fragment anchor.'));
        drupal_set_message(t('Please select atleast one checkbox'), 'error');
      }
      QmRedirectStorage::DeleteAll($rids);
      drupal_set_message(t('Selected data has been deleted'));
    }
    $tempstore = \Drupal::service('user.private_tempstore')->get('qm_redirect');
    $tempstore->set('searchtext', $searchtext);
    $content = ListForm::redirect_list($searchtext, $form);
    return $content;
  }

  function redirect_list($arg = Null, $form) {

    $header = array(
      'source' => array('data' => t('From'), 'field' => 'source', 'sort' => 'asc'),
      'questionmarkredirect' => array('data' => t('To'), 'field' => 'questionmarkredirect'),
      'chkdiable' => array('data' => t('Disable'), 'field' => 'chkdiable'),
      'operations' => array('data' => t('Operations'), 'colspan' => 2), array('Operations' => t('Operations'), 'style' => "Display:none")
    );

    $sort_header = array(
      'From' => 'source',
      'To' => 'questionmarkredirect',
      'Disable' => 'chkdiable',
    );
    $rows = array();
    foreach (QmRedirectStorage::getByFilter($arg, $sort_header) as $id => $content) {
      // Row with attributes on the row and some of its cells.
      $editUrl = Url::fromRoute('qm_redirect_edit', array('id' => $id));
      $deleteUrl = Url::fromRoute('qm_redirect_delete', array('id' => $id));

      if ($content->chkdiable == 0)
        $chkdiable = "No";
      else
        $chkdiable = "Yes";
      $rows[] = array(
        'data' => array(
          $id,
          $content->source, $content->questionmarkredirect, $chkdiable,
          \Drupal::l('Edit', $editUrl), \Drupal::l('Delete', $deleteUrl),
        ),
      );
      $surl = listForm::get_valid_url_from_uri($content->source);
      $source_url = Url::fromUri($surl);
      $qurl = listForm::get_valid_url_from_uri($content->questionmarkredirect);
      $questionmarkredirect_url = Url::fromUri($qurl);
      $options[$id] = array(
        'source' => \Drupal::l($content->source, $source_url),
        'questionmarkredirect' => \Drupal::l($content->questionmarkredirect, $questionmarkredirect_url),
        'chkdiable' => $chkdiable,
        'operations' => \Drupal::l('Edit', $editUrl), \Drupal::l('Delete', $deleteUrl),
      );
    }
    $build['table'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#attributes' => array(
        'id' => 'qm-redirect-table',
      ),
      '#empty' => t('No URL questionmarkredirects available.'),
      '#attached' => array(
        'library' => array('qm_redirect/qm_redirect'),
      ),
    );

    $build['pager'] = array(
      '#type' => 'pager'
    );
    $markup = \Drupal::service('renderer')->render($table);

    return $build;
  }

  /**
   * get valid URL for uri
   */
  public function get_valid_url_from_uri($uri) {
    if (!filter_var($uri, FILTER_VALIDATE_URL) === false) {
      $url = $uri;
    }
    else {


      if ($uri == '<front>') {
        $site_frontpage = \Drupal::config('system.site')->get('site_frontpage');
        $url = $GLOBALS['base_url'];
      }
      else {
        $url = $GLOBALS['base_url'] . '/' . $uri;
      }
    }

    return $url;
  }

}
