<?php

namespace Drupal\mid_url_proxy\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the mid_url_proxy entity.
 *
 * @ingroup mid_url_proxy
 *
 * @ContentEntityType(
 *   id = "mid_url_proxy",
 *   label = @Translation("Mid Url Proxy"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mid_url_proxy\Entity\Controller\MidUrlProxyListBuilder",
 *     "form" = {
 *       "add" = "Drupal\mid_url_proxy\Form\MidUrlProxyForm",
 *       "edit" = "Drupal\mid_url_proxy\Form\MidUrlProxyForm",
 *       "delete" = "Drupal\mid_url_proxy\Form\MidUrlProxyDeleteForm",
 *     },
 *     "access" = "Drupal\mid_url_proxy\MidUrlProxyAccessControlHandler",
 *   },
 *   base_table = "mid_url_proxy_config",
 *   admin_permission = "administer content_entity_example entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "source_url",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/mid-modules/mid_url_proxy/{mid_url_proxy}",
 *     "edit-form" = "/admin/mid-modules/mid_url_proxy/{mid_url_proxy}/edit",
 *     "delete-form" = "/admin/mid-modules/contact/{mid_url_proxy}/delete",
 *     "collection" = "/admin/mid-modules/mid_url_proxy/list"
 *   },
 *   field_ui_base_route = "mid_url_proxy.url_proxy_settings",
 * )
 */
class MidUrlProxy extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritdoc}
   *
   * @return type
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   *
   * @param string $key
   *
   * @return type
   */
  public function getEntityFieldValue($key) {
    return $this->getEntityKey($key);
  }

  /**
   * {@inheritdoc}
   *
   * @return type
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   *
   * @return type
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   *
   * @return type
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   *
   * @param int $uid
   *
   * @return \Drupal\mid_url_proxy\Entity\MidUrlProxy
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\mid_url_proxy\Entity\UserInterface $account
   *
   * @return \Drupal\mid_url_proxy\Entity\MidUrlProxy
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @param EntityTypeInterface $entityType
   *
   * @return type
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entityType) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Mid Url Proxy entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Mid Url Proxy entity.'))
      ->setReadOnly(TRUE);

    // Name field for the contact.
    // We set display options for the view as well as the form.
    // Users with correct privileges can change the view and edit configuration.
    $fields['source_url'] = BaseFieldDefinition::create('uri')
      ->setLabel(t('Source URL'))
      ->setDescription(t('Source URL from where data to be copied. Add full url with http or https 
eg. <b>"http://www.investis.com"</b>. You can append the parameters in request parameters section.
Do not add last "/" in URL.'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
        'not null' => TRUE,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'url',
        'weight' => 1,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'url',
        'weight' => 1,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['proxy_url_key'] = BaseFieldDefinition::create('string')
      ->setLabel(t('URL Proxy Key'))
      ->setDescription(t('Please add string to request this data using <b>Proxy URL</b>. 
Dont add any special characters. This attribute will be used for client side request.
You can ues "-".'))
      ->setRequired(TRUE)
      ->addConstraint('UniqueField')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'max_length' => 255,
        'weight' => 2,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 2,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['request_method'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Request Method'))
      ->setDescription(t('Request method to fetch the data from remote server.'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'allowed_values' => array(
          'GET' => 'GET',
          'POST' => 'POST',
        ),
        'default_value' => 'GET',
        'max_length' => 4,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 3,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['is_pretty_url'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is Pretty URL'))
      ->setDescription(t('Check only if request URL is pretty URL. Or need to replace the parameters'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 1,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'settings' => array(
          'display_label' => TRUE,
        ),
        'weight' => 5,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['request_parameters'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Request Parameters'))
      ->setDescription(t('Request parameters to be added as <b>key~~value</b> pair. Use <b> "~~" </b> as seprator between key and value. One pair need to be added each line.'))
      ->setSettings(array(
        'default_value' => '',
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 6,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => 6,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['request_header_parameters'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Request Header Parameters'))
      ->setDescription(t('Request header item. Add new header in new line. Headers added here will be sent to remote server while sending request.'))
      ->setSettings(array(
        'default_value' => '',
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 7,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => 7,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['xpath_string'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Xpath String'))
      ->setDescription(t('Xpath string for selection selection of data from row data.'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 500,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 8,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 8,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['inner_html_only'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Inner HTML Only'))
      ->setDescription(t('Inner HTML only.'))
      ->setSettings(
        array(
          'default_value' => '',
          'max_length' => 1,
          'text_processing' => 0,
        )
      )
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'boolean',
        'weight' => 9,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'settings' => array(
          'display_label' => TRUE,
        ),
        'weight' => 9,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['xpath_exclude'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Xpath Exclude'))
      ->setDescription(t('Xpath exclude string for excluding the some content from outcome of xpath string.'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 500,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 10,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['xslt_string'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('XSLT String'))
      ->setDescription(t('XSLT string for transforming the XML string.'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 2500,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'text',
        'weight' => 11,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => 11,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['request_time_milisecond'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Request Timeout Milliseconds'))
      ->setDescription(t('Request timeout in milliseconds'))
      ->setSettings(array(
        'max_length' => 1,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 12,
      ))
      ->setDefaultValue(10000)
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 12,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['error_message'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Error Message'))
      ->setDescription(t('Error message to be display if no data received from remote server.'))
      ->setSettings(array(
        'default_value' => '',
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 13,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => 13,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['cache_timeout_minutes'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Cache timeout minutes'))
      ->setDescription(t('Cache timeout in minutes. Set -1 for no time limit.'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 1,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 15,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 15,
      ))
      ->setDefaultValue(0)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of ContentEntityExample entity.'));
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
