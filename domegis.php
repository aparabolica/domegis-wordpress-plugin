<?php
/*
Plugin Name: DomeGIS
Plugin URI: http://github.com/ecodigital/domegis-wordpress-plugin
Description: Connect your DomeGIS platform to your WordPress project
Version: 0.0.1
Author: Miguel Peixe
Author URI: http://ecologia.digital/
License: MIT
*/

if(!class_exists('DomeGIS_Plugin')) {

  class DomeGIS_Plugin {

    function __construct() {
      add_action('add_meta_boxes', array($this, 'register_relationship_meta_box'));
      add_action('admin_enqueue_scripts', array($this, 'scripts'));
    }

    public static function activate() {
      // Do nothing
    }
    public static function deactivate() {
      // Do nothing
    }
    public function get_dir() {
      return apply_filters('domegis_dir', plugin_dir_url(__FILE__));
    }
    public function get_path() {
      return apply_filters('domegis_path', dirname(__FILE__));
    }

    function scripts() {
      wp_register_script('domegis', $this->get_dir() . 'domegis.js', array('jquery'));
      wp_enqueue_script('domegis-relation', $this->get_dir() . 'relation.js', array('jquery', 'underscore', 'domegis'));
      wp_localize_script('domegis-relation', 'domegis_settings', get_domegis_options());
    }

    function register_relationship_meta_box() {
      $options = get_domegis_options();
      $post_types = $options['post_types'];
      if(is_array($post_types) && !empty($post_types)) {
        add_meta_box('domegis-meta-box', __('DomeGIS', 'domegis'), array($this, 'relationship_meta_box'), $post_types, 'side', 'high');
      }
    }

    function relationship_meta_box() {
      $options = get_domegis_options();
      ?>
      <p><?php _e('Select related tables and features:', 'domegis'); ?></p>
      <p>
        <input type="text" class="domegis-search" placeholder="<?php _e('Search contents', 'domegis'); ?>" />
      </p>
      <?php
    }

  }

}

if(class_exists('DomeGIS_Plugin')) {

  register_activation_hook(__FILE__, array('DomeGIS_Plugin', 'activate'));
  register_deactivation_hook(__FILE__, array('DomeGIS_Plugin', 'deactivate'));

  $domegis_plugin = new DomeGIS_Plugin();

}

include_once($domegis_plugin->get_path() . '/settings.php');
