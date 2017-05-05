<?php

if(!class_exists('DomeGIS_Plugin_Settings')) {

  class DomeGIS_Plugin_Settings {

    public function __construct() {

      add_action('admin_menu', array($this, 'admin_menu'));
      add_action('admin_init', array($this, 'init_plugin_settings'));

    }

    function get_options() {
      $options = get_option('domegis');
      if(!$options) {
        $options = array(
          'url' => '',
          'post_types' => get_post_types(array('public' => true)),
          'baselayer' => 'bing'
        );
      }
      return $options;
    }

    function admin_menu() {
      add_options_page(__('DomeGIS', 'domegis'), __('DomeGIS', 'domegis'), 'manage_options', 'domegis', array($this, 'admin_page'));
    }

    function admin_page() {
      $this->options = $this->get_options();
      ?>
      <div class="wrap">
        <?php screen_icon(); ?>
        <h2><?php _e('DomeGIS', 'domegis'); ?></h2>
        <form method="post" action="options.php">
          <?php
          settings_fields('domegis_settings_group');
          do_settings_sections('domegis');
          submit_button();
          ?>
        </form>
      </div>
      <?php
    }

    function init_plugin_settings() {

      /*
       * Settings sections
       */
      add_settings_section(
        'domegis_general',
        __('General settings', 'domegis'),
        '',
        'domegis'
      );

      add_settings_section(
        'domegis_post_types',
        __('Post types', 'domegis'),
        '',
        'domegis'
      );

      /*
       * Settings fields
       */

      add_settings_field(
        'domegis_url',
        __('DomeGIS Platform URL', 'domegis'),
        array($this, 'field_platform_url'),
        'domegis',
        'domegis_general'
      );

      add_settings_field(
        'domegis_baselayer',
        __('Default base layer', 'domegis'),
        array($this, 'field_baselayer'),
        'domegis',
        'domegis_general'
      );

      add_settings_field(
        'domegis_legend_display',
        __('Default legend display', 'domegis'),
        array($this, 'field_legend_display'),
        'domegis',
        'domegis_general'
      );

      add_settings_field(
        'domegis_post_types',
        __('Relational post types', 'domegis'),
        array($this, 'field_post_types'),
        'domegis',
        'domegis_post_types'
      );

      // Register
      register_setting('domegis_settings_group', 'domegis');

    }

    function field_platform_url() {
      $url = $this->options['url'];
      ?>
      <input class="regular-text" id="domegis_url" type="text" name="domegis[url]" value="<?php echo $url; ?>" />
      <p class="description"><?php _e('The root url for your DomeGIS platform.', 'domegis'); ?></p>
      <?php
    }

    function field_baselayer() {
      $baselayer = $this->options['baselayer'];
      $layers = array(
        'bing' => __('Bing Aerial', 'domegis'),
        'osm' => __('OpenStreetMap', 'domegis'),
        'infoamazonia' => __('InfoAmazonia', 'domegis')
      );
      ?>
      <select id="domegis_baselayer" name="domegis[baselayer]">
        <?php foreach($layers as $val => $label) : ?>
          <option value="<?php echo $val; ?>" <?php if($baselayer == $val) echo 'selected'; ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
      <?php
    }

    function field_legend_display() {
      $baselayer = $this->options['legend_display'];
      $layers = array(
        'default' => __('Default (automatic height)', 'domegis'),
        'full_height' => __('Full height', 'domegis')
      );
      ?>
      <select id="domegis_legend_display" name="domegis[legend_display]">
        <?php foreach($layers as $val => $label) : ?>
          <option value="<?php echo $val; ?>" <?php if($baselayer == $val) echo 'selected'; ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
      <?php
    }

    function field_post_types() {
      $post_types = $this->options['post_types'];
      $available = get_post_types(array('public' => true));
      ?>
      <p class="description"><?php _e('DomeGIS WordPress Plugin allows you to enable relationship between posts and data inside your DomeGIS platform.', 'domegis') ?></p>
      <p><?php _e('Here you can select which post types are enabled for a relationship between the post and DomeGIS layers and its features', 'domegis'); ?></p>
      <table>
        <thead>
          <tr>
            <th><?php _e('Name', 'domegis'); ?></th>
            <th><?php _e('Enabled', 'domegis'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($available as $type) : ?>
            <tr>
              <td><label for="domegis_post_type_<?php echo $type; ?>"><?php echo $type; ?></label></td>
              <td><input id="domegis_post_type_<?php echo $type; ?>" type="checkbox" name="domegis[post_types][]" value="<?php echo $type; ?>" <?php if(in_array($type, $post_types)) echo 'checked'; ?> /></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php
    }

  }

}

if(class_exists('DomeGIS_Plugin_Settings')) {
  $domegis_plugin_settings = new DomeGIS_Plugin_Settings();
}

function get_domegis_options() {
  global $domegis_plugin_settings;
  return $domegis_plugin_settings->get_options();
}
