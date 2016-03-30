<?php
/**
 * @file
 * Groups all connections and operations with the Deezer APIs.
 */

// @TODO create 2 classes : 1 for create auto_playlist_deezer, 1 for communication with deezer.

// @TODO auto_playlist_deezer interface between drupal and deezer api => use to clean before/after all action with deezer api

// @TODO deezer class use to call with deezer. Group all api that deezer can use.

// @TODO ajouter un décorateur pour l'utilisation des class api.

function deezer_get_artist_id($artiste_name) {

}

function deezer_get_top_track_artist($id_artist, $limit_tracks) {
  $tracks = array();
  $test = 0;

  return $tracks;
}

function deezer_get_embed_code($settings, $list_id = array()) {

}
