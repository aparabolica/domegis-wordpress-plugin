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
        'id' => '',
        'views' => '',
        'feature' => '',
        'lang' => '',
        'base' => $options['baselayer'],
        'scroll' => '',
        'full_legend' => ''
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

      global $post;

      $custom_views = false;

      if($a['views']) {
        $views = $a['views'];
        $custom_views = true;
      } else {
        $post_layers = get_post_meta($post->ID, '_domegis_related_layers', true);
        if($post_layers) {
          $views = array();
          foreach($post_layers as $layer) {
            if(isset($layer['id']))
              $views[] = $layer['id'] . ':' . ($layer['hidden'] ? 0 : 1);
          }
          $views = implode(',', $views);
        }
      }

      if(!$views && !$a['id'])
        return '';

      if(!$a['scroll'] && $a['scroll'] !== false) {
        $post_options = get_post_meta($post->ID, '_domegis_options', true);
        if($post_options) {
          if($post_options['scrollwheelzoom']) {
            $a['scroll'] = $post_options['scrollwheelzoom'];
          }
        }
      }

      if($a['feature']) {
        $feature = $a['feature'];
      } elseif(!$custom_views) {
        global $post;
        $post_feature = get_post_meta($post->ID, '_domegis_related_feature', true);
        if($post_feature)
          $feature = $post_feature['layer_id'] . ':' . $post_feature['id'];
      }

      if($a['id']) {
        $url = $options['url'] . '#!/maps/' . $a['id'] . '/';
      } else {
        $url = $options['url'] . '#!/map/?views=' . $views;
        if($feature)
          $url .= '&feature=' . $feature;
        if($a['base'])
          $url .= '&base=' . $a['base'];
        if($a['lang'])
          $url .= '&lang=' . $a['lang'];
        if($a['scroll'])
          $url .= '&scroll=' . $a['scroll'];
      }

      if($a['full_legend']) {
        $url .= '&full_legend=1';
      }

      return '<div class="domegis-map"><iframe src="' . $url . '" width="' . $a['width'] . '" height="' . $a['height'] . '" frameborder="0" allowfullscreen></iframe></div>';
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
