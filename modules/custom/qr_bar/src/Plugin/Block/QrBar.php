<?php

/**
 * @file
 * Contains \Drupal\qr_bar\Plugin\Block\qr_bar.
 */

namespace Drupal\qr_bar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
//use Endroid\QrCode\Factory\QrCodeFactoryInterface;
//use Endroid\QrCode\QrCode;
//use Endroid\QrCode\Response\QrCodeResponse;

/**
 * Provides my custom block.
 *
 * @Block(
 *   id = "qr_bar",
 *   admin_label = @Translation("QR Bar"),
 *   category = @Translation("Custom")
 * )
 */
class QrBar extends BlockBase implements BlockPluginInterface {
  /**
     * {@inheritdoc}
     */
    function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $form['qr_bar_alt'] = array(
            '#type' => 'textfield',
            '#title' => t('Alt Text'),
            '#default_value' => isset($config['qr_bar_alt']) ? $config['qr_bar_alt'] : 'QR code for this page URL'
        );
        $form['qr_bar_width'] = array(
            '#type' => 'textfield',
            '#title' => t('QR Code Width & Height'),
            '#description' => t('Width & Height will be same. i.e. 150'),
            '#default_value' => isset($config['qr_bar_width']) ? $config['qr_bar_width'] : '150'
        );
       
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        
        $this->configuration['qr_bar_alt'] = $form_state->getValue('qr_bar_alt');
        $this->configuration['qr_bar_width'] = $form_state->getValue('qr_bar_width');
        
    }

     /**
     * {@inheritdoc}
     */
    public function build() {

        global $base_url;

        $path = \Drupal::request()->getRequestUri();
       
       // $path = \Drupal::service('path.alias_manager')->getAliasByPath($path);
        
        $config = $this->getConfiguration();
        $width = isset($config['qr_bar_width']) ? $config['qr_bar_width'] : '150';
       // $width = $this->configuration['qr_bar_width'];
        

        $page_url = urlencode($path);
        
      /*  $qrCode = new QrCode('Life is too short to be generating QR codes');
        $qrCode->setSize(300);
        header('Content-Type: '.$qrCode->getContentType());
        //echo $qrCode->writeString(); */


       // header('Content-Type: image/png');
        //$qrCode = new QrCode();
      //  $qrCode->setText('http://www.google.com');
       // $qrCode->setSize(200);
       // echo $qrCode->writeString();
        //$qrCode->render();

       
        $url = "http://qr.liantu.com/api.php?bg=ffffff&w={$width}&text={$page_url}";
        //$url = "qrcode.php?text={$page_url}&size={$width}";
       
        
        return array(
            '#theme' => 'qr_bar_block',
            '#url' => $url,
            '#alt' => $this->configuration['qr_bar_alt'],
            '#width' => $width,
            '#height' => $this->configuration['qr_bar_width'],
          
        );
    }

}