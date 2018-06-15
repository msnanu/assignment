<?php

namespace Drupal\mid_url_proxy;

use GuzzleHttp\Exception\RequestException;
use DOMDocument;
use DOMXPath;
use XSLTProcessor;
use Symfony\Component\DependencyInjection\SimpleXMLElement;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

define('PRETTY_URL_TOKEN_IDENTIFIRE', '@');

/**
 * Description of MidUrlProxyLib.
 *
 * @author pritam.tiwari.
 */
class MidUrlProxyLib {

  private $cacheTitle;

  /**
   * {@inheritdoc}
   *
   * Function to serve the client requests. Display output in seprate page.
   *
   * @param string $urlProxyKey
   */
  public function midUrlProxyServer($urlProxyKey) {
    if ($urlProxyKey) {
      // Select the configuration from the databsae.
      $result = db_select('mid_url_proxy_config', 'upc')
        ->fields('upc')
        ->condition('proxy_url_key', $urlProxyKey, "=")
        ->execute();
      $configData = $result->fetchAssoc();
      if (empty($configData)) {
        echo "Invalid url.";
        drupal_set_message("Invalid url.");
        exit;
      }

      // Get the configuration parameters.
      $configurationParametersArray = $this->_getConfUrlParam($configData['request_parameters']);

      $updatedUserRequestParameters = $this->getUpdatedUserRequestParameters($configurationParametersArray, $this);

      // Final parameters used in request.
      $updatedParameters = $updatedUserRequestParameters;

      // Create the cache title $cid.
      $cacheTitle = str_replace("-", "_", $configData["proxy_url_key"]);
      if (!empty($updatedParameters)) {
        $cacheTitle .= "_" . implode("_", $updatedParameters);
      }

      $this->getMidUrlProxyOutputData($configData, $updatedParameters, 0);
    }
  }

  /**
   * {@inheritdoc}
   *
   * Function to get the conf parameters array.
   *
   * @param string $configRequestParam
   *
   * @return type
   */
  public function _getConfUrlParam(&$configRequestParam) {
    // Parameters string .
    $parametersArray = explode("\n", $configRequestParam);
    if (!empty($parametersArray)) {
      $confUrlParam = array();
      foreach ($parametersArray as $key => $value) {
        $pair = explode("~~", $value);
        if (!empty($pair[0]) && !empty($pair[1])) {
          $confUrlParam[trim($pair[0])] = trim($pair[1]);
        }
      }
      return $confUrlParam;
    }
  }

  /**
   * {@inheritdoc}
   *
   * This function removed the parameters with [skip] values. Remove empty
   * parameters.
   *
   * @param array $updatedParameters
   */
  public function _skipEmptyParam(&$updatedParameters) {
    if (!empty($updatedParameters) && !is_null($updatedParameters)) {
      foreach ($updatedParameters as $key => $value) {
        if (strpos($value, "[skip]") >= -1 || strpos($value, "[skip]") != FALSE) {
          unset($updatedParameters[$key]);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * Function to filter the XML data with Xpsth.
   *
   * @param string $responceBody
   * @param string $xpathFilter
   *
   * @return type
   *
   * @throws NotFoundHttpException
   */
  private function _filterXmlDataWithXpath(&$responceBody, &$xpathFilter) {
    $xml = new SimpleXMLElement($responceBody);
    $result = $xml->xpath($xpathFilter);
    $output = "";

    if (!empty($result)) {
      foreach ($result as $key => $value) {
        $output .= $value->asXML();
      }

      if (count($result) > 1) {
        return "<root>" . $output . "</root>";
      }
      return $output;
    }
    else {
      throw new NotFoundHttpException('Xpath filtering results empty.');
    }
  }

  /**
   * {@inheritdoc}
   *
   * Function to filter the HTML data with Xpath.
   *
   * @param string $responceBody
   * @param string $xpathFilter
   *
   * @return type
   *
   * @throws NotFoundHttpException
   */
  private function _filterHtmlDataWithXpath(&$responceBody, &$xpathFilter) {
    if (!empty($xpathFilter)) {

      $doc = new DOMDocument();
      libxml_use_internal_errors(TRUE);
      $doc->loadHTML($responceBody);
      libxml_clear_errors();
      libxml_use_internal_errors(FALSE);

      $xpath = new DOMXpath($doc);
      $elements = $xpath->query($xpathFilter);

      if ($elements->length > 0) {
        $htmlOutputString = "";
        foreach ($elements as $element) {
          $newdoc = new DOMDocument();
          $cloned = $element->cloneNode(TRUE);
          $newdoc->appendChild($newdoc->importNode($cloned, TRUE));
          $htmlOutputString .= $newdoc->saveHTML();
        }
        return $htmlOutputString;
      }
      else {
        throw new NotFoundHttpException('Xpath query returns empty result.');
      }
    }
    else {
      return $responceBody;
    }
  }

  /**
   * {@inheritdoc}
   *
   * Function to extract the inner html from DOM.
   *
   * @param string $responceBody
   *
   * @return string
   */
  private function _extractInnerHtml(&$responceBody) {

    // Code block to get a DOMNode's innerHTML.
    $dom = new DOMDocument();

    // Wrapper div added to dietect the parent tag dynamically.
    libxml_use_internal_errors(TRUE);
    $dom->loadHTML('<div>' . $responceBody . '</div>');
    libxml_clear_errors();
    libxml_use_internal_errors(FALSE);

    // Get the inner html from the div we wraped above.
    $node = $dom->getElementsByTagName('div')->item(0)->firstChild;

    if (!empty($node->nodeValue)) {
      $innerHTML = '';
      foreach ($node->childNodes as $childNode) {
        $innerHTML .= $childNode->ownerDocument->saveHTML($childNode);
      }
      return $innerHTML;
    }
    else {
      throw new NotFoundHttpException('Inner HTML is empty.');
    }
  }

  /**
   * {@inheritdoc}
   *
   * Function to exclude the extra content.
   *
   * @param string $responceData
   * @param string $xpathExclude
   *
   * @return type string
   */
  private function _xpathExcludeXml($responceData, $xpathExclude) {
    $xml = new \DOMDocument();
    if (!empty($responceData)) {
      libxml_use_internal_errors(TRUE);
      if ($xml->loadXML($responceData)) {
        libxml_clear_errors();
        libxml_use_internal_errors(FALSE);
        $xpath = new DOMXPath($xml);

        $node = $xpath->query($xpathExclude);
        foreach ($node as $entry) {
          $entry->parentNode->removeChild($entry);
        }

        return $responceData = $xml->saveXML();
      }
      libxml_clear_errors();
      libxml_use_internal_errors(FALSE);
    }
    return $responceData;
  }

  /**
   * {@inheritdoc}
   *
   * Function to exclude the extra content.
   *
   * @param string $responceData
   * @param string $xpathExclude
   *
   * @return type string
   */
  private function _xpathExcludeHtml($responceData, $xpathExclude) {
    $dom = new DomDocument();

    if (!empty($responceData)) {
      libxml_use_internal_errors(TRUE);
      if ($dom->loadHTML($responceData)) {
        libxml_clear_errors();
        libxml_use_internal_errors(FALSE);

        $xpath = new DomXPath($dom);
        $nodes = $xpath->query($xpathExclude);
        foreach ($nodes as $entry) {
          $entry->parentNode->removeChild($entry);
        }
        return $dom->saveHTML();
      }
      libxml_clear_errors();
      libxml_use_internal_errors(FALSE);
    }
    return $responceData;
  }

  /**
   * {@inheritdoc}
   *
   * Demo function to check the flow of the Guzzle library.
   *
   * @param array $configData
   * @param string $updatedParameters
   * @param array $headersData
   * @param int $blockRequest
   *
   * @return type
   *
   * @throws NotFoundHttpException
   */
  public function midUrlProxyGetRemoteData(&$configData, &$updatedParameters, &$headersData, $blockRequest = 0) {
    $client = new \GuzzleHttp\Client();
    $response = "";

    $configRequestMethode1 = "";
    if (!empty($configData['request_method'])) {
      $configRequestMethode1 = $configData['request_method'];
    }

    $configData['source_url'] = $this->checkPrettyUrl($configData['is_pretty_url'], $configData['source_url'], $updatedParameters);
    $headersData = $this->_getHeadersData($configData);
    
    switch ($configRequestMethode1) {
      case "GET":
        // Create client request parameters array.
        try {
          // Call to the remote server data with Guzzle Client.
          $response = $client->request('GET', $configData['source_url'], ['query' => $updatedParameters, 'timeout' => $configData["request_time_milisecond"], 'headers' => $headersData, 'allow_redirects' => ['max' => 1000]]);
          
       }
        catch (RequestException $e) {
          if (is_object($e)) {
            // Display error message.
            \Drupal::logger('mid_url_proxy')->debug("URL: " . $configData['source_url'] . $this->_getExceptionString($e));
            if ($blockRequest == 0) {
              echo $configData['error_message'] . $this->_getExceptionString($e);
              exit;
            }
            else {
              return $configData['error_message'] . $this->_getExceptionString($e);
            }
          }
        }
        break;

      case "POST":
        // Create client request parameters array.
        try {
          // Call to the remote server data with Guzzle Client.
          $response = $client->request('POST', $configData['source_url'], ['form_params' => $updatedParameters, 'timeout' => $configData["request_time_milisecond"], 'headers' => $headersData, 'allow_redirects' => ['max' => 1000]]);
        }
        catch (RequestException $e) {
          if (is_object($e)) {
            // Display error message.
            \Drupal::logger('mid_url_proxy')->debug($this->_getExceptionString($e));
            if ($blockRequest == 0) {
              echo $configData['error_message'];
              echo $this->_getExceptionString($e);
              exit;
            }
            else {
              return $configData['error_message'] . $this->_getExceptionString($e);
            }
          }
        }
        break;
    }

    try {
      if (is_object($response)) {

        $headersData = $response->getHeaders();
        $body = $response->getBody();
        // 200.
        $code = $response->getStatusCode();
        // OK.
        $reason = $response->getReasonPhrase();
        // Responce code validation in array ().
        if (in_array($code, array(200, 201, 202, 203, 204, 205, 206))) {
          $responceBody = "";
          if ($response->hasHeader('Content-Length')) {
            $contentLength = $response->getHeader('Content-Length');
            if (!empty($contentLength[0])) {
              $responceBody = $body->read($contentLength[0]);
            }
          }
          else {
            $responceBody = "";
            while ($outputString = $body->read(1024)) {
              $responceBody .= $outputString;
            }
          }
          // Apply xpath filter.
          // Check if output header is XML/HTML.
          $contentTypeHeader = $response->getHeader("content-type");
          $responceData = "";

          if (!empty($configData["xpath_string"])) {
            if (strpos($contentTypeHeader[0], "/xml")) {
              $responceData = $this->_filterXmlDataWithXpath($responceBody, $configData["xpath_string"]);
            }
            elseif (strpos($contentTypeHeader[0], "html")) {
              $responceData = $this->_filterHtmlDataWithXpath($responceBody, $configData["xpath_string"]);

              // Code to get the inner html if selected.
              if (isset($configData['inner_html_only']) && $configData['inner_html_only'] == 1) {
                $responceData = $this->_extractInnerHtml($responceData);
              }
            }
          }
          else {
            $responceData = $responceBody;
          }
          
          // Code to apply the xpath exclude filter.
          if (!empty($configData['xpath_exclude'])) {

            if (strpos($contentTypeHeader[0], "/xml")) {
              $responceData = $this->_xpathExcludeXml($responceData, $configData["xpath_exclude"]);
            }
            elseif (strpos($contentTypeHeader[0], "html")) {
              $responceData = $this->_xpathExcludeHtml($responceData, $configData["xpath_exclude"]);
            }
          }

          // Apply the XSLT transformation to the XML data.
          if (strpos($contentTypeHeader[0], "/xml")) {
            $xslt = $configData['xslt_string'];
            if (!empty($xslt)) {
              // Load XSLT string from configuration.
              $xsl = new DomDocument();
              
              libxml_use_internal_errors(TRUE);
              if (!$xsl->loadXML($xslt)) {
                libxml_clear_errors();
                libxml_use_internal_errors(FALSE);
                throw new NotFoundHttpException('Unable to create object from XSLT string.');
              }
              libxml_clear_errors();
              libxml_use_internal_errors(FALSE);

              // Load XML responce text.
              $inputdom = new DomDocument();

              libxml_use_internal_errors(TRUE);
              if (!$inputdom->loadXML($responceData)) {
                libxml_clear_errors();
                libxml_use_internal_errors(FALSE);
                throw new NotFoundHttpException('Unable to create inputdom object from response data.');
              }
              libxml_clear_errors();
              libxml_use_internal_errors(FALSE);

              /* Create the processor and import the stylesheet */
              $proc = new XsltProcessor();
              $xsl = $proc->importStylesheet($xsl);
              if ($newdom = $proc->transformToDoc($inputdom)) {
                $responceData = $newdom->saveXML();
              }
              else {
                throw new NotFoundHttpException("Unable to transform the XML.");
              }
            }
          }
          // Comment the closing brackets in CDATA.
          $responceData = str_replace("]]>", "<!-- ]]> -->", $responceData);
            
          return $responceData;
        }
      }
    }
    catch (\Exception $e) {
      if (is_object($e)) {
        // Display error message.
        \Drupal::logger('mid_url_proxy')->error("<br/>Code: " . $e->getCode() . "<br/>Message: " . $e->getMessage() . "<br/>Line: " . $e->getLine() . "<br/>Code: " . $e->getCode() . "<br/>File: " . $e->getFile());
        if ($blockRequest == 0) {
          echo $this->_getExceptionString($e);
          echo $configData["error_message"];
          exit;
        }
        else {
          return $configData["error_message"] . $this->_getExceptionString($e);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   * 
   * Function to prepare the Header data.
   *
   * @param array $configData
   *
   * @return type
   */
  private function _getHeadersData(&$configData) {
    $headersData = array();
    if (isset($configData['request_header_parameters']) && !empty($configData['request_header_parameters'])) {
      $headerConfig = explode("\n", $configData['request_header_parameters']);
      if (is_array($headerConfig)) {
        foreach ($headerConfig as $value) {
          $value = trim($value);
          if (empty($value)) {
            continue;
          }
          $headerPair = explode("~~", $value);
          if (!isset($headerPair[0])) {
            $headerPair[0] = NULL;
          }
          if (!isset($headerPair[1])) {
            $headerPair[1] = NULL;
          }
          if (!empty($headerPair[0]) && !empty($headerPair[1])) {
            $headersData[trim($headerPair[0])] = trim($headerPair[1]);
          }
        }
      }
    }
    return $headersData;
  }

  /**
   * {@inheritdoc}
   *
   * Function to generate the exception string and display to admin role user
   * only.
   *
   * @param obj $e
   *
   * @return string
   */
  private function _getExceptionString(&$e) {
    if (array_search('administrator', \Drupal::currentUser()->getRoles())) {
      return "<br/>Code: " . $e->getCode() . "<br/>Message: " . $e->getMessage() . "<br/>Line: " . $e->getLine() . "<br/>Code: " . $e->getCode() . "<br/>File: " . $e->getFile();
    }
    else {
      return "";
    }
  }

  /**
   * {@inheritdoc}
   *
   * Function to get the serialized parameters in string form.
   *
   * @param string $configDataValue
   *
   * @return type
   */
  private function _getParametersString($configDataValue) {
    if (!empty($configDataValue)) {
      $parametersArray = unserialize($configDataValue);
      $parameters = array();
      foreach ($parametersArray as $key => $value) {
        $parameters[] = implode("~~", $value);
      }
      return implode("\n", $parameters);
    }
  }

  /**
   * {@inheritdoc}
   *
   * Function Get the parameters string in serialized form to store in db.
   *
   * @param string $parameterString
   *
   * @return type
   */
  private function _getParametersSerialized($parameterString) {
    if (!empty($parameterString)) {
      $parameters = array();
      foreach (explode("\n", $parameterString) as $key => $value) {
        $value = trim($value);
        if (empty($value)) {
          continue;
        }
        $arrayValues = explode("~~", str_replace("\r", "", $value));
        $parameters[$key] = $arrayValues;
      }
      return serialize($parameters);
    }
  }

  /**
   * {@inheritdoc}
   *
   * Function to get the updated user request parameters. Default values will be
   * updated in this function.
   *
   * @param array $configurationParametersArray
   * @param obj $UrlProxyObj
   *
   * @return type
   */
  public function getUpdatedUserRequestParameters($configurationParametersArray, &$UrlProxyObj) {
    $userRequestDataArray = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());

    $updatedUserRequestParameters = array();

    // Check the parameters with the configuration parameters.
    $updatedUserRequestParameters = $userRequestDataArray;
    if (!empty($updatedUserRequestParameters['q'])) {
      unset($updatedUserRequestParameters['q']);
    }
    $temp_array = $configurationParametersArray;
    if (!empty($configurationParametersArray)) {
      foreach ($configurationParametersArray as $key => $value) {
        if (!empty($updatedUserRequestParameters[$key])) {
          $temp_array[$key] = $updatedUserRequestParameters[$key];
        }
      }
    }
    $updatedUserRequestParameters = $temp_array;
    $UrlProxyObj->_skipEmptyParam($updatedUserRequestParameters);

    return $updatedUserRequestParameters;
  }

  /**
   * {@inheritdoc}
   *
   * Functio to handle the preety url fucntionality. It will replace the tokens
   * in the url and update the with parameters values before the guzzle call.
   *
   * @param int $isPreetyUrl
   * @param string $sourceUrl
   * @param array $updatedParameters
   *
   * @return type
   */
  public function checkPrettyUrl($isPreetyUrl, $sourceUrl, $updatedParameters) {
    // If pretty url configuration is set.
    if ($isPreetyUrl == 1) {
      $url = trim($sourceUrl);
      $urlLength = strlen($url);
      // Remove the last "/" in URL.
      if (substr($url, ($urlLength - 1)) == "/") {
        $url = substr($url, 0, ($urlLength - 1));
      }

      // Update preety URL with token replace logic.
      foreach ($updatedParameters as $key => $value) {
        if ($value == "['skip']") {
          $url = str_replace(PRETTY_URL_TOKEN_IDENTIFIRE . $key, "", $url);
        }
        else {
          $url = str_replace(PRETTY_URL_TOKEN_IDENTIFIRE . $key, $value, $url);
        }
      }

      \Drupal::logger('mid_url_proxy')->debug("url: ".$url);
      return $url;
    }
    return $sourceUrl;
  }

  /**
   * {@inheritdoc}
   *
   * Function to generate the cache id with help of the Proxy url key and the
   * parameters.
   *
   * @param string $urlProxyKey
   * @param array $updatedParameters
   */
  public function generateCacheId($urlProxyKey, $updatedParameters) {

    $this->cacheTitle = str_replace("-", "_", $urlProxyKey);
    $this->cacheTitle = str_replace(" ", "_", $this->cacheTitle);
    if (!empty($updatedParameters)) {
      $this->cacheTitle .= "_" . implode("_", $updatedParameters);
    }
  }

  /**
   * {@inheritdoc}
   *
   * Function to generate the data from remote call or cache.
   *
   * @param array $configData
   * @param array $updatedParameters
   * @param int $isBlock
   *
   * @return type
   */
  public function getMidUrlProxyOutputData($configData, $updatedParameters, $isBlock = 0) {

    $this->generateCacheId($configData['proxy_url_key'], $updatedParameters);

    // Code to get the chche from database.
    $cacheGetData = $this->getMidUrlProxyCache($this->cacheTitle);
    
    if (!empty($cacheGetData)) {
      // Check status of the cached data expired.
     
      $cacheExpired = $this->checkMidUrlProxyCacheExpired($configData['cache_timeout_minutes'], $cacheGetData);
    }

    // Code block if cache data not empty and cache is not expired.
    if (!empty($cacheGetData) && $cacheExpired != false) {

      $outputData = unserialize($cacheGetData->data[0]);

      if ($isBlock) {
        return $outputData;
      }
      else {
        drupal_http_header_attributes(array('Content-Type' => $outputData['header']['Content-Type']));
        print $outputData['data'];
        exit(0);
      }
    }
    else {
      // Cache not exist.
      $headersData = array();
      $cacheSetData = $this->midUrlProxyGetRemoteData($configData, $updatedParameters, $headersData, $isBlock);

      if (!empty($cacheSetData)) {
        // Save responce header in cache.
        $cacheInputData = array();
        $cacheInputData['header'] = $headersData;
        $cacheInputData['data'] = $cacheSetData;

        // Set the result text to the cache bin.
        $serialisedData = serialize($cacheInputData);
        
        $this->setMidUrlProxyCache($this->cacheTitle, $serialisedData);
        if ($isBlock) {
          return $cacheInputData;
        }
        else {
          drupal_http_header_attributes(array('Content-Type' => $headersData['Content-Type']));
          print $cacheSetData;
          exit(0);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * Function return the cache if exist.
   *
   * @param string $cacheKey
   *
   * @return type
   */
  protected function getMidUrlProxyCache($cacheKey) {
    return \Drupal::cache()->get($cacheKey);
  }

  /**
   * {@inheritdoc}
   *
   * Function to set the data in the cache.
   *
   * @param string $cacheKey
   * @param string $cacheData
   */
  protected function setMidUrlProxyCache($cacheKey, $cacheData) {
    \Drupal::cache()->set($cacheKey, array($cacheData, time()));
  }

  /**
   * {@inheritdoc}
   *
   * Funciton to check the cache is expired or not.
   *
   * @param int $cacheConfigMinutes
   * @param string $cacheData
   *
   * @return boolean
   */
  protected function checkMidUrlProxyCacheExpired($cacheConfigMinutes, &$cacheData) {
    // If configuration is set to 0 minutes.
    if ($cacheConfigMinutes == 0) {
      return TRUE;
    }

    // If configuration is set to -1. Cache never expires.
    if ($cacheConfigMinutes == -1) {
      return FALSE;
    }

    // Get the cashed stord time.
    $cacheUpdated = $cacheData->data;

    if ((round(abs(time() - $cacheUpdated[1]) / 60)) >= $cacheConfigMinutes) {
      return TRUE;
    }
    return FALSE;
  }

}
