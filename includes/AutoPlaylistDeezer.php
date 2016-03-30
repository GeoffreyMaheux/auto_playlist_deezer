<?php
/**
 * @file
 * AutoPlaylistDeezer class
 *
 * Groups all methods and properties used to create automatic playlist Deezer.
 * Facade for Deezer Api.
 */

require_once 'DeezerApi.php';

class AutoPlaylistDeezer {

  public function __construct($embed_settings, $track_settings) {
    $this->embed_settings = $embed_settings;
    $this->track_settings = $track_settings;
  }

  public function getTopTracksListByArtistName($name) {
    $test = 0;
  }

  public function getTopTracksListByArtistId($id) {
    // Firts we need to take the list of top tracks od the artist
    $deezerEmbed = new DeezerApi();
    $deezerEmbed->getTopTrackList($id, $this->track_settings['limit_tracks']);

    // At this moment we just need to collect the list of track's ID.
    $this->extractIdTrack($deezerEmbed);

    $deezerEmbed->getEmbedPlaylist($this->embed_settings, $this->listIdTrack);
    $test = 0;
  }

  protected function extractIdTrack($deezerEmbed) {
    $listIdTrack = array();
    foreach ($deezerEmbed->trackList->data as $track) {
      if (is_object($track)) {
        $listIdTrack[] = $track->id;
      }
    }

    $this->listIdTrack = $listIdTrack;
  }
}
