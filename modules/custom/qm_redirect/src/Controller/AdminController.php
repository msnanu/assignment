<?php

/**
  @file
  Contains \Drupal\qm_redirect\Controller\AdminController.
 */

namespace Drupal\qm_redirect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\qm_redirect\QmRedirectStorage;
use Drupal\Core\Form\FormStateInterface;

class AdminController extends ControllerBase {

  function content() {

    $form['searchtext'] = array(
      '#title' => t("FILTER REDIRECTS"),
      '#type' => 'textfield',
      '#default_value' => '',
    );
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Filter'),
    );
    $url = Url::fromRoute('qm_redirect_add');
    $url_import = Url::fromRoute('qm_redirect_import');

    $add_import_link = '<p>' . \Drupal::l(t('Add Redirect'), $url) . "  |  " . \Drupal::l(t('Import Redirect'), $url_import) . '</p>';

    $text = array(
      '#type' => 'markup',
      '#markup' => $add_import_link,
    );

    // Table header.
    $header = array(
      'id' => t('Id'),
      'name' => t('From'),
      'message' => t('To'),
      'message1' => t('Disable'),
      'operations' => t('Operations'), array('Operations' => 'Operations', 'rowspan' => 2),
    );
    $rows = array();
    foreach (QmRedirectStorage::getAll() as $id => $content) {
      // Row with attributes on the row and some of its cells.
      $editUrl = Url::fromRoute('qm_redirect_edit', array('id' => $id));
      $deleteUrl = Url::fromRoute('qm_redirect_delete', array('id' => $id));

      $rows[] = array(
        'data' => array(
          $id,
          $content->source, $content->questionmarkredirect, $content->chkdiable,
          \Drupal::l('Edit', $editUrl), \Drupal::l('Delete', $deleteUrl),
        ),
      );
    }
    $table = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => array(
        'id' => 'qm-redirect-table',
      ),
    );

    return array($form,
      $text,
      $table,
    );
  }

}
