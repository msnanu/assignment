<?php

namespace Drupal\mid_url_proxy\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of UrlProxyBlock.
 */
class MidUrlProxyBlock extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $midUrlProxyStorage;

  /**
   * Constructs new SystemMenuBlock.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entityStorage
   *   The mid url proxy storage.
   */
  public function __construct(EntityStorageInterface $entityStorage) {
    $this->midUrlProxyStorage = $entityStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity.manager')->getStorage('mid_url_proxy')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $entities = $this->midUrlProxyStorage->loadMultiple();
    foreach ($entities as $urlProxy => $entity) {
      $this->derivatives[$urlProxy] = $basePluginDefinition;
      $this->derivatives[$urlProxy]['admin_label'] = $entity->label();
      $this->derivatives[$urlProxy]['config_dependencies']['config'] = array($entity->getConfigDependencyName());
    }
    return $this->derivatives;
  }

}
