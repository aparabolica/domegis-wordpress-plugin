<?php

if(!class_exists('DomeGIS_Plugin_Shortcodes')) {

  class DomeGIS_Plugin_Shortcodes {

    public function __construct() {
      add_shortcode('domegis_map', array($this, 'map'));
      add_action('wp_head', array($this, 'wp_head'));
    }

    function map($atts) {
      $options = get_domegis_options();
      $a = shortcode_atts( array(
        'width' => '100%',
        'height' => '400',
        'views' => '',
        'baselayer' => 'openstreetmap',
      ), $atts );
      if(!$a['views']) {
        global $post;
        if($post) {
          $related = get_post_meta($post->ID, '_domegis_related_layers', true);
          $a['views'] = '';
        } else {
          return '';
        }
      }
      return '<div class="domegis-map"><iframe src="' . $options['url'] . '#!/map/?views=' . $a['views'] . '&base=' . $a['baselayer'] . '" width="' . $a['width'] . '" height="' . $a['height'] . '" frameborder="0"></iframe></div>';
    }

    function wp_head() {
      ?>
      <style>
        .domegis-map {
          margin: 0 0 2rem;
        }
      </style>
      <?php
    }
  }

}

if(class_exists('DomeGIS_Plugin_Shortcodes')) {
  new DomeGIS_Plugin_Shortcodes();
}
