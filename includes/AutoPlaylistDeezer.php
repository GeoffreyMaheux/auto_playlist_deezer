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

  public function __construct($embedSettings, $trackSettings, $proxyConfig) {
    $this->proxyConfig = $proxyConfig;
    $this->embedSettings = $embedSettings;
    $this->trackSettings = $trackSettings;
    $this->iframe = '';
    $this->artistId = '';
    $this->listIdTrack = NULL;
  }

  public function getTopTracksListByArtistName($name) {
    $deezerEmbed = new DeezerApi($this->proxyConfig);

    // We take the artist ID.
    $deezerEmbed->getListArtistId($name);

    // For the moment we take the first item of result @see README.md.
    $this->extractFirstArtistId($deezerEmbed);

    if (!empty($this->artistId)) {
      $deezerEmbed->getTopTrackList($this->artistId, $this->trackSettings['limit_tracks']);
      $this->extractIdTrack($deezerEmbed);
      $deezerEmbed->getEmbedPlaylist($this->embedSettings, $this->listIdTrack);
      $this->iframe = $deezerEmbed->iframe;
    }
  }

  public function getTopTracksListByArtistId($id) {
    $deezerEmbed = new DeezerApi();

    // We need to take the list of top tracks od the artist.
    $deezerEmbed->getTopTrackList($id, $this->trackSettings['limit_tracks']);

    // At this moment we just need to collect the list of track's ID.
    $this->extractIdTrack($deezerEmbed);

    // Generate the iframe embed Deezer.
    $deezerEmbed->getEmbedPlaylist($this->embedSettings, $this->listIdTrack);
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
