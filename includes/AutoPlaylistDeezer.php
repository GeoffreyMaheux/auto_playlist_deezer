<?php
/**
 * @file
 * AutoPlaylistDeezer class
 *
 * Groups all methods and properties used to create automatic playlist Deezer.
 * Facade for Deezer Api.
 */

class AutoPlaylistDeezer {

  public function __construct($settings) {
    /*foreach ($settings as $key => $value) {
      $this->$key = $value;
    }*/
    $this->settings = $settings;
    $test = 0;
  }

  public function getTopTracksListByArtistName($name) {
    $test = 0;
  }

  public function getTopTracksListByArtistId($id) {

  }
}
