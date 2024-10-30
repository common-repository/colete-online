<?php
defined( 'ABSPATH' ) || exit;

class ColeteOnline_Meta_Box_Order {

  /**
   * Output the metabox.
   *
   * @param WP_Post $post
   */
  public static function output($post) {
    global $post, $thepostid, $theorder;

    if (!is_int($thepostid)) {
      $thepostid = $post->ID;
    }

    if (!is_object($theorder)) {
      $theorder = wc_get_order($thepostid);
    }

    $order = $theorder;
    $data  = get_post_meta($post->ID);

    include __DIR__ . '/views/html-order-shipping-meta-box.php';
  }

  /**
   * Save meta box data.
   *
   * @param int $post_id
   */
  public static function save($post_id) {
  }
}
