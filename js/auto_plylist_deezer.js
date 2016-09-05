/**
 * @file
 * Groups all attached js for the module auto playlist deezer.
 */

(function($) {
  Drupal.behaviors.MODULENAME_form = {
    attach: function(context, settings) {
      if ($('div').hasClass('auto-playlist-deezer-format-wrapper')) {
        if ($('div.auto-playlist-deezer-format-wrapper :selected').text() == 'Classic') {
          $("div.auto-playlist-deezer-size-wrapper option[value='big']").remove();
        }
      }

      $('div.auto-playlist-deezer-format-wrapper select').change(function() {
        if ($('div.auto-playlist-deezer-format-wrapper :selected').text() == 'Classic') {
          $("div.auto-playlist-deezer-size-wrapper option[value='big']").remove();
        }
        else {
          if ($("div.auto-playlist-deezer-size-wrapper option[value='big']").length == 0) {
            $("div.auto-playlist-deezer-size-wrapper select").append('<option value="big">Big</option>');
          }
        }
      });
    }
  };
})(jQuery);
