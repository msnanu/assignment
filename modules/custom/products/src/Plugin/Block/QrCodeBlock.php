<?php

/**
 * @file
 * Contains \Drupal\products\Plugin\Block\qr_code_block.
 */
namespace Drupal\products\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Hello' Block
 *
 * @Block(
 *   id = "hello_block",
 *   admin_label = @Translation("Hello block"),
 * )
 */
class QrCodeBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        return array(
            '#markup' => $this->t('Hello, World!'), 
        );
    }
}
?>