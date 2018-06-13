<?php

/**
 * @file
 * Contains \Drupal\qr_code\Plugin\Block\qr_code_block.
 */

namespace Drupal\qr_code\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use Endroid\QrCode\QrCode;

/**
 * Provides my custom block.
 *
 * @Block(
 *   id = "qr_code",
 *   admin_label = @Translation("QR Code For Product"),
 *   category = @Translation("Blocks")
 * )
 */
class QrCodeBlock extends BlockBase implements BlockPluginInterface {
  /**
     * {@inheritdoc}
     */
    function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $form['qr_code_alt'] = array(
            '#type' => 'textfield',
            '#title' => t('Alt Text'),
            '#default_value' => isset($config['qr_code_alt']) ? $config['qr_code_alt'] : 'QR code for this page URL'
        );
        $form['qr_code_width'] = array(
            '#type' => 'textfield',
            '#title' => t('QR Code Width & Height'),
            '#description' => t('Width & Height will be same. i.e. 150'),
            '#default_value' => isset($config['qr_code_width']) ? $config['qr_code_width'] : '150'
        );
       
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        
        $this->configuration['qr_code_alt'] = $form_state->getValue('qr_code_alt');
        $this->configuration['qr_code_width'] = $form_state->getValue('qr_code_width');
        
    }

     /**
     * {@inheritdoc}
     */
    public function build() {

        global $base_url;

        $path = \Drupal::request()->getRequestUri();
       
       // $path = \Drupal::service('path.alias_manager')->getAliasByPath($path);
        
        $width = $this->configuration['qr_code_width'];
        

        $page_url = urlencode($path);
        
        
        header('Content-Type:'.$qrCode->getContentType());
        $qrCode = new QrCode();
        $qrCode->setText('http://www.google.com');
        $qrCode->setSize(200);
        //$qrCode->render();

       
        $url = "http://qr.liantu.com/api.php?bg=ffffff&w={$width}&text={$page_url}";
       
        
        return array(
            '#theme' => 'qr_code_block',
            '#url' => $url,
            '#alt' => $this->configuration['qr_code_alt'],
            '#width' => $width,
            '#height' => $this->configuration['qr_code_width'],
          
        );
    }

}