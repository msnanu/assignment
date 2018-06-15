<?php

/**
 * @file
 * Contains \Drupal\qm_redirect\ImportForm.
 */

namespace Drupal\qm_redirect;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\File\File;
use Drupal\file\FileInterface;
use Drupal\Core\Language;
use Drupal\Core\Url;

class ImportForm extends FormBase {

  protected $id;

  function getFormId() {
    return 'qm_redirect_import';
  }

  /**
   * Build form for import csv file.
   */
  function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attributes'] = array('enctype' => "multipart/form-data");

    $form['source_options'] = array(
      '#type' => 'value',
      '#value' => array(),
      '#tree' => TRUE,
    );
    $form['questionmarkredirect_options'] = array(
      '#type' => 'value',
      '#value' => array(),
      '#tree' => TRUE,
    );
    $form['csvfile'] = array(
      '#type' => 'managed_file',
      '#title' => t("Upload a CSV file"),
      '#upload_validators' => array(
        'file_validate_extensions' => array('csv'),
      ),
      '#upload_location' => 'public://csvuploads',
    );


    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Save',
    );


    return $form;
  }

  /**
   * Submit form for import csv file .
   */
  function submitForm(array &$form, FormStateInterface $form_state) {

    $language = \Drupal::languageManager()->getCurrentLanguage();

    $fid = $form_state->getValue('csvfile');
    $file = QmRedirectStorage::getFid($fid[0]);
    $realpath = drupal_realpath($file->uri);

    $fh = fopen($realpath, 'r');
    $table_header = NULL;
    $table = array();

    while (($row = fgetcsv($fh, NULL, ';')) !== FALSE) {


      for ($i = 0; $i < count($row); $i++) {
        $data = explode(",", $row[$i]);
        $type = "questionmarkredirect";
        $lang = $language->getId();
        $source = $data[0];
        $redirect = $data[1];
        $chkdiable = $data[2];
        $diable_url = $data[3];
        $rid = "";


        $element_source['#value'] = $source;
        $element_source['#name'] = 'source';
        $flag = false;

        AddForm::_questionmarkredirect_extract_url_options($element_source, $form_state, $flag);

        $element_questionmarkredirect['#value'] = $redirect;
        $element_questionmarkredirect['#name'] = 'questionmarkredirect';

        $flag = false;

        AddForm::_questionmarkredirect_extract_url_options($element_questionmarkredirect, $form_state, $flag);

        $source_option = $form_state->getValue('source_options');
        $questionmarkredirect_options = $form_state->getValue('questionmarkredirect_options');

        $questionmarkredirect = array(
          'source' => $source,
          'source_options' => $source_option,
          'type' => 'questionmarkredirect',
          'questionmarkredirect' => $redirect,
          'questionmarkredirect_options' => $questionmarkredirect_options,
          'language' => $lang,
          'chkdiable' => $chkdiable,
          'diable_url' => $diable_url,
        );
        $questionmarkredirect = (object) $questionmarkredirect;
        AddForm::questionmarkredirect_hash($questionmarkredirect);
        $rid = QmRedirectStorage::checkEntry($source);
        $questionmarkredirect->rid = $rid;
        if ($rid) {
          QmRedirectStorage::edit($questionmarkredirect);
        }
        else {

          QmRedirectStorage::add($questionmarkredirect);
        }
      }
    }

    drupal_set_message(t('Your File has been Imported'));

    $form_state->setRedirect('qm_redirect_listing');
    return;
  }

}
