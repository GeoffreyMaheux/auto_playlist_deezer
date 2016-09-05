<?php
/**
 * @file
 * Groups Fields hooks to implement widget inline form playlist deezer.
 */

/**
 * Implements hook_field_widget_info().
 */
function auto_playlist_deezer_field_widget_info() {
  $widgets = array();

  $widgets['inline_form_playlist_deezer'] = array(
    'label' => t('Inline form playlist Deezer'),
    'field types' => array('text_long'),
    'settings' => array(
      'rows' => 5,
      'auto_playlist_deezer' => array(
        'manually_embed_deezer' => 1,
        'automatic_embed_deezer' => 0,
        'embed_configuration' => array(
          'width' => 600,
          'height' => 200,
          'layout' => 'dark',
          'playlist' => 0,
          'autoplay' => 0,
          'format' => 'classic',
          'size' => 'medium',
          'type' => 'tracks',
          'color' => '007FEB',
        ),
        'tracks_configuration' => array(
          'limit_tracks' => 10,
        ),
      ),
    ),
  );

  return $widgets;
}

/**
 * Implements hook_field_instance_settings_form().
 */
function auto_playlist_deezer_field_widget_settings_form($field, $instance) {
  $widget = $instance['widget'];
  $settings = $widget['settings'];
  $settings_embed_configuration = $settings['auto_playlist_deezer']['embed_configuration'];

  $element = array();

  $element['rows'] = array(
    '#type' => 'textfield',
    '#title' => t('Rows'),
    '#default_value' => $settings['rows'],
    '#required' => TRUE,
    '#element_validate' => array('element_validate_integer_positive'),
  );

  switch ($widget['type']) {
    case 'inline_form_playlist_deezer' :
      // Generate the part of form for AUto playlist deezer widget.
      $element['auto_playlist_deezer'] = array(
        '#type' => 'fieldset',
        '#title' => t('Auto playlist Deezer widget configuration'),
      );

      // Checkbox user can copy and paste embed code directly. By default TRUE.
      $element['auto_playlist_deezer']['manually_embed_deezer'] = array(
        '#type' => 'checkbox',
        '#title' => t('Allow users to choose which kind of player they want to embed'),
        '#default_value' => $settings['auto_playlist_deezer']['manually_embed_deezer'],
      );

      // Checkbox user can use Deezer API to generate embed code. By default FALSE
      $element['auto_playlist_deezer']['automatic_embed_deezer'] = array(
        '#type' => 'checkbox',
        '#title' => t('Allow users to use Deezer API to generate embed code.'),
        '#default_value' => $settings['auto_playlist_deezer']['automatic_embed_deezer'],
      );

      // Embed configuration instance field. Group data for the Deezer API Embed.
      $element['auto_playlist_deezer']['embed_configuration'] = array(
        '#type' => 'fieldset',
        '#title' => t('Embed configuration for Deezer API'),
        '#states' => array(
          'invisible' => array(
            ':input[name="instance[widget][settings][auto_playlist_deezer][automatic_embed_deezer]"]' => array(
              'checked' => FALSE,
            ),
          ),
        ),
      );

      auto_playlist_deezer_config_iframe_form($element, $settings_embed_configuration);

      // Tracks configuration instance field. Group data for the Deezer API.
      $element['auto_playlist_deezer']['tracks_configuration'] = array(
        '#type' => 'fieldset',
        '#title' => t('Tracks configuration for Deezer API'),
      );

      // Deezer configuration limit tracks. By default 10.
      $element['auto_playlist_deezer']['tracks_configuration']['limit_tracks'] = array(
        '#type' => 'textfield',
        '#size' => 3,
        '#maxlength' => 3,
        '#title' => t('The number limit of tracks that you want to show in the playlist.'),
        '#default_value' => $settings['auto_playlist_deezer']['tracks_configuration']['limit_tracks'],
      );

      break;
  }

  return $element;
}

/**
 * Implements hook_field_widget_form().
 */
function auto_playlist_deezer_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {
  $widget = $instance['widget'];
  $settings = $widget['settings'];

  switch ($widget['type']) {
    case 'inline_form_playlist_deezer':
      // Build parents array for the element's widget.
      $parents = array_merge($element['#field_parents'], array($element['#field_name'], $element['#language']));

      // Get langcode of the parent entity.
      $parent_langcode = entity_language($element['#entity_type'], $element['#entity']);

      // Assign a unique identifier to each auto playlist deezer widget.
      // Use sha1() because $parents can be long.
      $apd_id = sha1(implode('-', $parents));

      // Create the wrapper ID for the element.
      $wrapper = 'automatic-playlist-deezer-' . $apd_id;

      $element = array(
          '#type' => 'fieldset',
          '#tree' => TRUE,
          '#description' => filter_xss_admin($instance['description']),
          '#prefix' => '<div id="' . $wrapper . '">',
          '#suffix' => '</div>',
          '#apd_id' => $apd_id,
          '#apd_root' => TRUE,
        ) + $element;

      // Initialize the auto playlist deezer array in form state.
      if (empty($form_state['auto_playlist_deezer'][$apd_id])) {
        $form_state['auto_playlist_deezer'][$apd_id] = array(
          'form' => NULL,
          'settings' => $settings,
          'instance' => $instance,
        );

        // Load data from $items variable.
        $form_state['auto_playlist_deezer'][$apd_id]['values'] = array();

        $delta = 0;
        foreach ($items as $item) {
          $form_state['auto_playlist_deezer'][$apd_id]['values'][$delta] = array(
            'value' => $item['value'],
            'weight' => $delta,
            'form' => NULL,
          );
          $delta++;
        }
      }

      // Start to prepare the render of form value in form field.
      $element['values'] = array(
        '#tree' => TRUE,
        '#theme' => 'auto_playlist_deezer_theme_values_element',
      );

      $weight_delta = max(ceil(count($form_state['auto_playlist_deezer'][$apd_id]['values']) * 1.2), 50);
      foreach ($form_state['auto_playlist_deezer'][$apd_id]['values'] as $key => $info) {
        $element['values'][$key]['#info_value'] = $info['value'];
        $element['values'][$key]['#weight'] = $info['weight'];

        if (!empty($info['form'])) {
          $element['values'][$key]['delta'] = array(
            '#type' => 'value',
            '#value' => $info['weight'],
          );

          $element['values'][$key]['form'] = array(
            '#type' => 'container',
            '#attributes' => array('class' => array('apd-form', 'apd-form-row')),
            '#op' => $info['form'],
            '#parents' => array_merge($parents, array('values', $key, 'form')),
            '#parent_language' => $parent_langcode,
            '#apd_id' => $apd_id,
            '#apd_row_delta' => $key,
          );

          $form_element = &$element['values'][$key]['form'];

          // Add the appopriate form.
          if ($info['form'] == 'manual_edit') {
            $form_element += auto_playlist_deezer_manually_form($form_element, $form_state, $info['value']);
          }
          elseif ($info['form'] == 'automatic_edit') {
            $form_element += auto_playlist_deezer_automatic_form($form_element, $form_state);
          }
          elseif ($info['form'] == 'remove') {
            $form_element += auto_playlist_deezer_remove_form($form_element, $form_state);
          }
        }
        else {
          $row = &$element['values'][$key];
          $row['delta'] = array(
            '#type' => 'weight',
            '#deltat' => $weight_delta,
            '#default_value' => $info['weight'],
            '#attributes' => array('class' => array('apd-values-delta')),
          );

          // Add container action.
          $row['actions'] = array(
            '#type' => 'container',
            '#attributes' => array('class' => array('apd-container-form-operations')),
          );

          // Add the Manual edit action for value element row.
          $row['actions']['apd_value_manual_edit'] = array(
            '#type' => 'submit',
            '#value' => t('Manual editing'),
            '#limit_validation_errors' => array(),
            '#name' => 'apd-' . $apd_id . '-values-manual-edit-' . $key,
            '#ajax' => array(
              'callback' => 'auto_playlist_deezer_ajax_get_element',
              'wrapper' => $wrapper
            ),
            '#submit' => array('auto_playlist_deezer_open_row_element'),
            '#apd_row_delta' => $key,
            '#apd_row_form' => 'manual_edit',
          );

          // Add the Automatic edit action for value element row.
          $row['actions']['apd_value_automatic_edit'] = array(
            '#type' => 'submit',
            '#value' => t('Automatic editing'),
            '#limit_validation_errors' => array(),
            '#name' => 'apd-' . $apd_id . '-values-automatic-edit-' . $key,
            '#ajax' => array(
              'callback' => 'auto_playlist_deezer_ajax_get_element',
              'wrapper' => $wrapper
            ),
            '#submit' => array('auto_playlist_deezer_open_row_element'),
            '#apd_row_delta' => $key,
            '#apd_row_form' => 'automatic_edit',
          );

          // Add the delete action for value element row.
          $row['actions']['apd_value_delete'] = array(
            '#type' => 'submit',
            '#value' => t('Remove'),
            '#limit_validation_errors' => array(),
            '#name' => 'apd-' . $apd_id . '-values-remove-' . $key,
            '#ajax' => array(
              'callback' => 'auto_playlist_deezer_ajax_get_element',
              'wrapper' => $wrapper
            ),
            '#submit' => array('auto_playlist_deezer_open_row_element'),
            '#apd_row_delta' => $key,
            '#apd_row_form' => 'remove',
          );
        }
      }

      // If cardinality equals number of items return form.
      if ($field['cardinality'] > 0 && $field['cardinality'] == count($form_state['auto_playlist_deezer'][$apd_id]['values'])) {
        return $element;
      }

      // Test if we need to load specific form.
      if (empty($form_state['auto_playlist_deezer'][$apd_id]['form'])) {
        if ($settings['auto_playlist_deezer']['manually_embed_deezer']) {
          $element['action']['manually_embed_deezer'] = array(
            '#type' => 'submit',
            '#value' => t('Embed deezer manually'),
            '#name' => 'apd-' . $apd_id . '-manually',
            '#ajax' => array(
              'callback' => 'auto_playlist_deezer_ajax_get_element',
              'wrapper' => $wrapper
            ),
            '#limit_validation_errors' => array(),
            '#submit' => array('auto_playlist_deezer_open_automatic_form'),
            '#apd_form' => 'apd_manually',
          );
        }

        if ($settings['auto_playlist_deezer']['automatic_embed_deezer']) {
          $element['action']['automatic_embed_deezer'] = array(
            '#type' => 'submit',
            '#value' => t('Embed deezer @type automatically',
              array(
                '@type' => ucfirst($settings['auto_playlist_deezer']['embed_configuration']['type']))
            ),
            '#name' => 'apd-' . $apd_id . '-automatic',
            '#ajax' => array(
              'callback' => 'auto_playlist_deezer_ajax_get_element',
              'wrapper' => $wrapper
            ),
            '#limit_validation_errors' => array(),
            '#submit' => array('auto_playlist_deezer_open_automatic_form'),
            '#apd_form' => 'apd_automatic',
          );
        }
      }
      else {
        $element['form'] = array(
          '#type' => 'fieldset',
          '#attributes' => array('class' => array('apd-form', 'apd-form-bottom')),
          '#parents' => array_merge($parents, array('form')),
          '#apd_id' => $apd_id,
          '#parent_language' => $parent_langcode,
          '#entity_type' => $instance['entity_type'],
        );

        // When action form is manually or edit we just need to load a
        // textarea field.
        if ($form_state['auto_playlist_deezer'][$apd_id]['form'] == 'apd_manually') {
          $element['form']['#op'] = 'add';
          $element['form'] += auto_playlist_deezer_manually_form($element['form'], $form_state);
        }
        // When action form is automatic embed or edit automatic we load
        // new form with textfield and submit action.
        if ($form_state['auto_playlist_deezer'][$apd_id]['form'] == 'apd_automatic') {
          $element['form']['#op'] = 'add';
          $element['form'] += auto_playlist_deezer_automatic_form($element['form'], $form_state);
        }
      }
      break;
  }

  return $element;
}

/**
 * Implements hook_field_formatter_info().
 */
function auto_playlist_deezer_field_formatter_info() {
  return array(
    'auto_playlist_deezer_embed' => array(
      'label' => t('Auto playlist Deezer Embed'),
      'field types' => array('text_long'),
    ),
  );
}

/**
 * Implements hook_field_formatter_view().
 */
function auto_playlist_deezer_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $element = array();

  switch ($display['type']) {
    case 'auto_playlist_deezer_embed' :
      foreach ($items as $item) {
        $element[] = array(
          '#theme' => 'auto_playlist_deezer_display_iframe',
          '#element' => $item['value'],
        );
      }
      break;
  }
  return $element;
}
