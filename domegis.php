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
      add_action('save_post', array($this, 'save_post'), 10, 3);
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
      wp_register_script('domegis-language', $this->get_dir() . 'language.js');
      wp_enqueue_script('domegis-relation', $this->get_dir() . 'relation.js', array('jquery', 'underscore', 'domegis', 'domegis-language'));
      wp_localize_script('domegis-relation', 'domegis_relation', array(
        'settings' => get_domegis_options()
      ));
    }

    function register_relationship_meta_box() {
      $options = get_domegis_options();
      $post_types = $options['post_types'];
      if(is_array($post_types) && !empty($post_types)) {
        add_meta_box('domegis-meta-box', __('DomeGIS', 'domegis'), array($this, 'relationship_meta_box'), $post_types, 'side', 'high');
      }
    }

    function relationship_meta_box() {
      global $post;
      $options = get_domegis_options();
      $layers = get_post_meta($post->ID, '_domegis_related_layers', true);
      $feature = get_post_meta($post->ID, '_domegis_related_feature', true);
      if(!$feature) $feature = array();
      if($layers) {
        $layers = json_encode($layers);
      } else {
        $layers = '[]';
      }
      ?>
      <p><?php _e('Connect your content to DomeGIS layers:', 'domegis'); ?></p>
      <p>
        <input type="text" class="domegis-search" placeholder="<?php _e('Search layers...', 'domegis'); ?>" style="width:100%;" />
      </p>
      <div class="search-results"></div>
      <hr />
      <div class="domegis-related-layers">
        <h4><?php _e('Associated layers', 'domegis'); ?></h4>
        <div class="related-results"></div>
        <input id="domegis_related_layers_input" type="hidden" name="domegis_related_layers" value='<?php echo $layers; ?>' />
      </div>
      <div class="domegis-related-feature">
        <h4><?php _e('Connect to a single feature based on associated layers', 'domegis'); ?></h4>
        <input type="text" class="domegis-feature-search" placeholder="<?php _e('Search features...', 'domegis'); ?>" style="width:100%;" />
        <div class="feature-results"></div>
        <div class="selected-feature">
          <h4><?php _e('Selected feature', 'domegis'); ?></h4>
          <h5><?php if(isset($feature['label'])) echo $feature['label']; ?></h5>
          <input id="domegis_related_feature_id" type="hidden" name="domegis_related_feature[id]" value="<?php if(isset($feature['id'])) echo $feature['id']; ?>" />
          <input id="domegis_related_feature_layerid" type="hidden" name="domegis_related_feature[layer_id]" value="<?php if(isset($feature['layer_id'])) echo $feature['layer_id']; ?>" />
          <input id="domegis_related_feature_label" type="hidden" name="domegis_related_feature[label]" value="<?php if(isset($feature['label'])) echo $feature['label']; ?>" />
          <a href="#" class="domegis-reset-feature">[<?php _e('reset feature', 'domegis'); ?>]</a>
        </div>
      </div>
      <?php
    }

    function save_post($post_id, $post, $update) {

      if(!current_user_can("edit_post", $post_id))
        return $post_id;

      if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

      // if(isset($_REQUEST['domegis_related_layers'])) {
      //   $layers = explode(',', $_REQUEST['domegis_related_layers']);
      //   update_post_meta($post_id, '_domegis_related_layers', $layers);
      // }

      if(isset($_REQUEST['domegis_loaded'])) {
        if(isset($_REQUEST['domegis_layer_view'])) {
          update_post_meta($post_id, '_domegis_related_layers', $_REQUEST['domegis_layer_view']);
        } else {
          delete_post_meta($post_id, '_domegis_related_layers');
        }

        if(isset($_REQUEST['domegis_related_feature'])) {
          update_post_meta($post_id, '_domegis_related_feature', $_REQUEST['domegis_related_feature']);
        } else {
          delete_post_meta($post_id, '_domegis_related_feature');
        }
      }

    }

  }

}

if(class_exists('DomeGIS_Plugin')) {

  register_activation_hook(__FILE__, array('DomeGIS_Plugin', 'activate'));
  register_deactivation_hook(__FILE__, array('DomeGIS_Plugin', 'deactivate'));

  $domegis_plugin = new DomeGIS_Plugin();

}

include_once($domegis_plugin->get_path() . '/settings.php');
include_once($domegis_plugin->get_path() . '/shortcodes.php');
