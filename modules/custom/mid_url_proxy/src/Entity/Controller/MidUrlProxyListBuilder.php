<?php

namespace Drupal\mid_url_proxy\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Provides a list controller for mid_url_proxy entity.
 *
 * @ingroup mid_url_proxy
 */
class MidUrlProxyListBuilder extends EntityListBuilder {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entityType) {
    return new static(
      $entityType, $container->get('entity.manager')->getStorage($entityType->id()), $container->get('url_generator')
    );
  }

  /**
   * Constructs a new ContactListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entityType, EntityStorageInterface $storage, UrlGeneratorInterface $urlGenerator) {
    parent::__construct($entityType, $storage);
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  public function render() {
    $build['description'] = array(
      '#markup' => $this->t(
        'Mid URL Proxy implements a url proxy for sites. You can manage the fields on the <a href="@adminlink">URL proxy admin page</a>.',
        array(
          '@adminlink' => $this->urlGenerator->generateFromRoute('mid_url_proxy.url_proxy_settings'),
        )
      ),
    );
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the contact list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['proxy_url_key'] = $this->t('Url Proxy Key');
    $header['request_method'] = $this->t('Request Method');
    $header['operations'] = $this->t('OPERATIONS');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   *
   * @global type $baseUrl
   * @param EntityInterface $entity
   *
   * @return type
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\mid_url_proxy\Entity\MidUrlProxy */
    global $base_url;

    $row['source_url'] = $entity->source_url->value;

    if (!empty($entity->proxy_url_key->value)) {
      // Create the button link for the news page.
      $url = Url::fromUri($base_url . "/tools/mid-proxy-url/" . $entity->proxy_url_key->value);

      if (is_object($url)) {
        $linkOptions = array(
          'attributes' => array(
            'target' => array(
              '_blank',
            ),
          ),
        );
        $url->setOptions($linkOptions);

        $row['proxy_url_key'] = \Drupal::l(t($entity->proxy_url_key->value), $url);
      }
      else {
        $row['proxy_url_key'] = "";
      }
    }
    else {
      $row['proxy_url_key'] = "";
    }

    $row['request_method'] = $entity->request_method->value;

    return $row + parent::buildRow($entity);
  }

}
