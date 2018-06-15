<?php

namespace Drupal\qm_redirect;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class DeleteForm extends ConfirmFormBase {

  protected $id;

  function getFormId() {
    return 'qm_redirect_delete';
  }

  /**
   * Get question on delete data.
   */
  function getQuestion() {
    return t('Are you sure you want to delete submission %id?', array('%id' => $this->id));
  }

  /**
   * Confirmation text.
   */
  function getConfirmText() {
    return t('Delete');
  }

  /**
   * Cancel URL.
   */
  function getCancelUrl() {
    return new Url('qm_redirect_list');
  }

  /**
   * BuildForm for delete records.
   */
  function buildForm(array $form, FormStateInterface $form_state) {
    $this->id = \Drupal::request()->get('id');
    return parent::buildForm($form, $form_state);
  }

  /**
   * Submit form for delete records.
   */
  function submitForm(array &$form, FormStateInterface $form_state) {
    QmRedirectStorage::delete($this->id);

    \Drupal::logger('qm_redirect')->notice('@type: deleted %title.', array(
      '@type' => $this->id,
      '%title' => $this->id,
    ));
    drupal_set_message(t('qm_redirect submission %id has been deleted.', array('%id' => $this->id)));
    $form_state->setRedirect('qm_redirect_listing');
  }

}
