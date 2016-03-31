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
    $this->iframe = '';
    $this->artistId = '';
    $this->listIdTrack = NULL;
  }

  public function getTopTracksListByArtistName($name) {
    $deezerEmbed = new DeezerApi();

    // We take the artist ID.
    $deezerEmbed->getListArtistId($name);

    // For the moment we take the first item of result @see README.md.
    $this->extractFirstArtistId($deezerEmbed);

    if (!empty($this->artistId)) {
      $deezerEmbed->getTopTrackList($this->artistId, $this->track_settings['limit_tracks']);
      $this->extractIdTrack($deezerEmbed);
      $deezerEmbed->getEmbedPlaylist($this->embed_settings, $this->listIdTrack);
      $this->iframe = $deezerEmbed->iframe;
    }
  }

  public function getTopTracksListByArtistId($id) {
    $deezerEmbed = new DeezerApi();

    // We need to take the list of top tracks od the artist.
    $deezerEmbed->getTopTrackList($id, $this->track_settings['limit_tracks']);

    // At this moment we just need to collect the list of track's ID.
    $this->extractIdTrack($deezerEmbed);

    // Generate the iframe embed Deezer.
    $deezerEmbed->getEmbedPlaylist($this->embed_settings, $this->listIdTrack);
    $this->iframe = $deezerEmbed->iframe;
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

  protected function extractFirstArtistId($deezerEmbed) {
    if (!empty($deezerEmbed->listArtistId->data)) {
      $firstArtiste = array_shift($deezerEmbed->listArtistId->data);
      if (!empty($firstArtiste)) {
        $this->artistId = $firstArtiste->id;
      }
    }
  }
}
