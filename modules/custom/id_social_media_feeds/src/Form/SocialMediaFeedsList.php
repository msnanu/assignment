<?php

namespace Drupal\id_social_media_feeds\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Description of SocialMediaFeedsTwitterConfigForm
 *
 * @author pritam.tiwari
 */
class SocialMediaFeedsList extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'socialmediafeedlist_admin_settings';
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
    $configArrayKeys = array_keys($config->getRawData());

    // Make fieldset expanded if one of the filters is entered.
    $form['filters']['#collapsed'] = TRUE;

    // Set header for table of results.
    $header = [
      [
        'data' => $this->t('ID'),
      ],
      [
        'data' => $this->t('Channel'),
      ],
      [
        'data' => $this->t('UserID'),
      ],
      [
        'data' => $this->t('Operation'),
      ]
    ];

    $counter = 1;
    $rows = [];
    foreach ($configArrayKeys as $key => $value) {
      $keyArray = explode("||", $value);

      if (isset($keyArray[0]) && isset($keyArray[1])) {
        $url = Url::fromUri("internal:/admin/config/id-social-feeds-config/$keyArray[0]/edit/$keyArray[1]");
        $link = \Drupal::l(t('Edit'), $url);
        $rows[] = [
          'data' => [
            'data' =>
            $counter++,
            $keyArray[0],
            $keyArray[1],
            $link,
          ],
        ];
      }
    }


    // SEt reuslt in table theme with pager.
    $build['auditlog_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => ['id' => 'admin-smfeeds-list', 'class' => ['admin-smfeeds']],
      '#empty' => $this->t('No configuration available.'),
    ];


    $form['#attached']['library'][] = 'id_social_media_feeds/id_social_media_feeds.id_social_media_feeds_admin';
    $form['results'] = $build;

    // No token checking and/or caching.
    $form_state->disableCache();

    return parent::buildForm($form, $form_state);
  }

}
