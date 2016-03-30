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

  }

  public function getArtistID($name) {

  }

  public function getTopTrackList($id, $limit) {

  }

  public function getEmbedPlaylist($embedSettings, $trackList) {

  }
}
