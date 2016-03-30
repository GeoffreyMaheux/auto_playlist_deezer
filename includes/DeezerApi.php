<?php
/**
 * Created by PhpStorm.
 * User: gmaheux
 * Date: 29/03/16
 * Time: 17:54
 */

class DeezerApi {

  public function __construct() {
    // @TODO to create the url we need to know if we want to use http or https.
    // @TODO Arguments of url are creating by all method. each method can use the appropriate argument.
    // @TODO a part of setting is used to set the parameters of url like limit=10 in the call method.
    // @TODO settings need to be a properties of DeezerApi Class and the name of properties need to be like the Deezer parameters name.
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

  public function getArtistID($name) {
    // http://api.deezer.com/search/artist?q=Lenorman
    // http://api.deezer.com/search/artist?q=NAME_ARTIST

    // For the moment we take the first item of result.
    // @TODO maybe create a form step that propose to the user to select which artist he wants to take.

    $test = 0;
  }

  public function getTopTrackList($id, $limit) {
    // http://api.deezer.com/artist/4674/top?limit=20
    // http://api.deezer.com/artist/ID_ARTIST/top?limit=20

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
    $test = 0;
  }

  public function getEmbedPlaylist($embedSettings, $trackList) {
    // http://www.deezer.com/plugins/player?format=classic&autoplay=true&playlist=true&width=700&height=350&color=007FEB&layout=dark&size=medium&type=tracks&id=2711991,1041861,65525990,17199746,17199748,17199743,29191331&app_id=1
    $data = array(
      'type' => 'GET',
      //'url' => 'https://api.deezer.com/artist/' . $id . '/top',
      'arguments' => array(
        //'limit' => $limit,
      ),
      'encodeData' => FALSE,
      'returnHeaders' => FALSE,
    );
    $test = 0;
  }
}
