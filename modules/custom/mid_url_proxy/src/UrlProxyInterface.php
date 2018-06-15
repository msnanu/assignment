<?php

namespace Drupal\mid_url_proxy;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Mid Url Proxy entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup mid_url_proxy
 */
interface UrlProxyInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
