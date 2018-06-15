<?php

namespace Drupal\mid_url_proxy\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mid_url_proxy\MidUrlProxyLib;

/**
 * Provides a 'URL Proxy block: URL Proxy block' block.
 *
 * @Block(
 *   id = "mid_url_proxy",
 *   admin_label = @Translation("URL Proxy Block"),
 *   deriver = "Drupal\mid_url_proxy\Plugin\Derivative\MidUrlProxyBlock"
 * )
 */
class UrlProxyBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var EntityViewBuilderInterface.
   */
  private $viewBuilder;

  /**
   * @var UrlProxyContactInterface.
   */
  private $urlProxyInterface;

  /**
   * Creates a NodeBlock instance.
   *
   * @param array $configuration
   * @param string $pluginId
   * @param array $pluginDefinition
   * @param EntityManagerInterface $entityManager
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityManagerInterface $entityManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->viewBuilder = $entityManager->getViewBuilder('mid_url_proxy');
    $this->urlProxyStorage = $entityManager->getStorage('mid_url_proxy');
    $this->urlProxyInterface = $entityManager->getStorage('mid_url_proxy')->load($this->getDerivativeId());
  }

  /**
   * {@inheritdoc}
   *
   * @param ContainerInterface $container
   * @param array $configuration
   * @param type $pluginId
   * @param type $pluginDefinition
   *
   * @return \static
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration, $pluginId, $pluginDefinition, $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param AccountInterface $account
   * @param bool $returnAsObject
   *
   * @return type
   */
  public function blockAccess(AccountInterface $account, $returnAsObject = FALSE) {
    return $this->urlProxyInterface->access('view', NULL, TRUE);
  }

	private function objToArray($obj, &$arr){

   	 if(!is_object($obj) && !is_array($obj)){
       		 $arr = $obj;
        	return $arr;
    	}

    	foreach ($obj as $key => $value)
    	{
        	if (!empty($value))
        	{
            		$arr[$key] = array();
            		$this->objToArray($value, $arr[$key]);
        	}
       		 else
       		 {
           		 $arr[$key] = $value;
       		 }
   	 }
   		 return $arr;
	}
  /**
   * {@inheritdoc}
   *
   * @return type
   */
  public function build() {
  //  \Drupal::service('page_cache_kill_switch')->trigger();

    $blockMetadata = new \stdClass();

    foreach ($this->urlProxyInterface->toArray() as $key => $value) {

      if (!empty($value[0]) && !empty($value[0]['value'])) {
        $blockMetadata->$key = $value[0]['value'];
      }
      else {
        $blockMetadata->$key = "";
      }
    }

    $UrlProxyObj = new MidUrlProxyLib();

    // Get the configuration parameters.
    $configurationParametersArray = $UrlProxyObj->_getConfUrlParam($blockMetadata->request_parameters);

    $updatedUserRequestParameters = $UrlProxyObj->getUpdatedUserRequestParameters($configurationParametersArray, $UrlProxyObj);

    $updatedParameters = $updatedUserRequestParameters;

    // Generate cache id.
    $UrlProxyObj->generateCacheId($blockMetadata->proxy_url_key, $updatedParameters);
   // $blockContent;

    try {
      if (!empty($blockMetadata)) {
        $updatedParameters = $updatedUserRequestParameters;

         $responceData = $UrlProxyObj->getMidUrlProxyOutputData((array) $blockMetadata, $updatedParameters, 1);
	//echo "<pre>"; print_r($responceData); die;
         $responceBody = "";
         if(isset($responceData['data'])){
           $responceBody = $responceData['data'];
         }
         $responceHeader = "";
         if(isset($responceData['header']) && isset($responceData['header']['Content-Type']) && isset($responceData['header']['Content-Type'][0])){
           $responceHeader = strtolower($responceData['header']['Content-Type'][0]);
         }

         $responceBody = $responceData['data'];
	 $responceHeader = strtolower($responceData['header']['Content-Type'][0]);

        if (!empty($responceBody)) {
          $blockContent = preg_replace('/<+\?xml\/*\s*([A-Z][A-Z0-9]*)\b[^>]*\/*\s*>+/i', "", $responceBody);
          if($responceHeader == 'application/json; charset=utf-8'|| $responceHeader == 'application/json'){
		  $jsonData = json_decode($blockContent);
		  
                  $data = $this->objToArray($jsonData,$arr);
                  
	   } else if($responceHeader == "text/xml") {
		  $xml = simplexml_load_string($responceBody, "SimpleXMLElement", LIBXML_NOCDATA);
		  $json = json_encode($xml);
		  $data = json_decode($json,TRUE);
		  $data = $data['item'];
				
	   } else {
		   $data = $blockContent;
	   }
        }
        else {
          $data = (!empty($blockMetadata->error_message)) ? $blockMetadata->error_message : "";
        }
      }
      else {
        \Drupal::logger('mid_url_proxy')->error("No record found.");
      }
    }
    catch (Exception $e) {
      if (is_object($e)) {
        // Display error message.
        \Drupal::logger('mid_url_proxy')->error('mid_url_proxy error', "URL: " . $blockMetadata->source_url . $UrlProxyObj->_getExceptionString($e));
      }
    }
    return array(
      '#type' => 'markup',
      '#markup' => $data,
      '#cache' => array(
        'max-age' => 0,
      ),
    );
  }



  
  public function getCacheMaxAge() {
    return 0;
  }
}
