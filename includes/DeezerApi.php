<?php
/**
 * Created by PhpStorm.
 * User: gmaheux
 * Date: 29/03/16
 * Time: 17:54
 */

class DeezerApi {

  public function __construct($proxyConfig) {
    $this->proxyConfig = $proxyConfig;
    $this->iframe = '';
    $this->srcParameters = '';
    $this->iframeParameters = '';
    $this->trackList = NULL;
    $this->listArtistId = NULL;
  }

  protected function call($data) {
    $type = strtoupper($data['type']);
    $arguments = $data['arguments'];
    $returnHeaders = $data['returnHeaders'];
    $encodeData = $data['encodeData'];

    if ($type == 'GET') {
      $data['url'] .= "?" . http_build_query($arguments);
    }
    $curlRequest = curl_init($data['url']);
    if ($type == 'POST') {
      curl_setopt($curlRequest, CURLOPT_POST, TRUE);
    }
    elseif ($type == 'PUT') {
      curl_setopt($curlRequest, CURLOPT_CUSTOMREQUEST, "PUT");
    }
    elseif ($type == 'DELETE') {
      curl_setopt($curlRequest, CURLOPT_CUSTOMREQUEST, "DELETE");
    }

    if (!empty($this->proxyConfig)) {
      if (!empty($this->proxyConfig['proxy_server'])) {
        curl_setopt($curlRequest, CURLOPT_PROXY, $this->proxyConfig['proxy_server']);
        curl_setopt($curlRequest, CURLOPT_PROXYPORT, $this->proxyConfig['proxy_port']);
      }

      if (!empty($this->proxyConfig['proxy_username']) && !empty($this->proxyConfig['proxy_passport'])) {
        curl_setopt($curlRequest, CURLOPT_PROXYUSERPWD, implode(':', array($this->proxyConfig['proxy_username'], $this->proxyConfig['proxy_password'])));
      }
    }

    curl_setopt($curlRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($curlRequest, CURLOPT_HEADER, $returnHeaders);
    curl_setopt($curlRequest, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curlRequest, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlRequest, CURLOPT_FOLLOWLOCATION, 0);

    if (!empty($arguments) && $type !== 'GET') {
      if ($encodeData) {
        // Encode the arguments as JSON.
        $arguments = json_encode($arguments);
      }
      curl_setopt($curlRequest, CURLOPT_POSTFIELDS, $arguments);
    }

    $result = curl_exec($curlRequest);
    $curl_ressource = curl_getinfo($curlRequest);
    if ($encodeData) {
      // Set headers from response.
      list($headers, $content) = explode("\r\n\r\n", $result, 2);
      foreach (explode("\r\n", $headers) as $header) {
        header($header);
      }
      // Return the nonheader data.
      return trim($content);
    }
    curl_close($curlRequest);
    // Decode the response from JSON.
    $response = json_decode($result);

    return $response;
  }

  public function getListArtistId($name) {
    $data = array(
      'type' => 'GET',
      'url' => 'https://api.deezer.com/search/artist',
      'arguments' => array(
        'q' => $name,
      ),
      'encodeData' => FALSE,
      'returnHeaders' => FALSE,
    );
    $this->listArtistId = $this->call($data);
  }

  public function getTopTrackList($id, $limit) {
    $data = array(
      'type' => 'GET',
      'url' => 'https://api.deezer.com/artist/' . $id . '/top',
      'arguments' => array(
        'limit' => $limit,
      ),
      'encodeData' => FALSE,
      'returnHeaders' => FALSE,
    );

    $this->trackList = $this->call($data);
  }

  public function getEmbedPlaylist($embedSettings, $trackList) {
    $iframe_config = array();
    $arguments = array();

    if (!empty($trackList)) {
      foreach ($embedSettings as $key => $value) {
        $arguments[$key] = $value;
      }
      $arguments['id'] = implode(',', $trackList);

      // Only for test @see README.md.
      $arguments['app_id'] = 1;

      // Only for the test @see README.md.
      $iframe_config['scrolling'] = 'no';
      $iframe_config['frameborder'] = 0;
      $iframe_config['allowTransparency'] = true;

      // Add width height config for iframe.
      $iframe_config['width'] = '100%';
      $iframe_config['height'] = '100%';

      $this->urlConstructParameters($arguments);
      $this->iframeConstructParameters($iframe_config);

      $this->iframe = '<iframe ' . $this->iframeParameters . ' src="https://www.deezer.com/plugins/player?' . $this->srcParameters . '"></iframe>';
    }
  }

  protected function urlConstructParameters($parameters) {
    $url_query = '';
    foreach ($parameters as $key => $value) {
      $url_query .= $key . '=' . $value . '&';
    }
    $this->srcParameters = trim($url_query, '&');
  }

  protected function iframeConstructParameters($params) {
    $iframe_param = '';
    foreach ($params as $key => $value) {
      $iframe_param .= $key . '=' . $value . ' ';
    }
    $this->iframeParameters = trim($iframe_param, ' ');
  }
}
