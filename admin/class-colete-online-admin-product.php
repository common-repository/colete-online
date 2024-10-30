<?php

defined('ABSPATH') || exit;

require_once COLETE_ONLINE_ROOT . '/admin/class-colete-online-admin-user-guard.php';

class ColeteOnlineAdminProduct {

  private $prefix = "coleteonline-shipping-product";
  public function __construct() {}
  public function custom_product_options_dimensions() {
    global $product_object;

    if (get_option("coleteonline_advanced_product_fields", "no") === "no") {
      return;
    }

    $product_id = $product_object->get_id();

    $product_type = get_post_meta($product_id, "$this->prefix-type", true) ?? "default";
    $product_multiple = get_post_meta($product_id, "$this->prefix-multiple", true) ?? "1";
    $not_eligible_for_locker = get_post_meta($product_id, "$this->prefix-not-eligible-for-locker", true) ?? "on";
    $sel = "selected";
    $checked = "checked";
    ?>
    <div class="options_group">
      <p><b>
        <?php _e("Colete online shipping options:", "coleteonline"); ?>
      </b></p>
      <p class="form-field">
        <label for="coleteonline-product-type">
          <?php _e("Product type:", "coleteonline"); ?>
        </label>
        <select
          name="<?php echo "$this->prefix-type";?>"
          id="<?php echo "$this->prefix-type";?>"
        >
          <option
            value="default"
            <?php echo $product_type === "default" ? $sel : "" ?>
          >
            <?php echo _x("Default", "producttype", "coleteonline");?>
          </option>
          <option
            value="white"
            <?php echo $product_type === "white" ? $sel : "" ?>
          >
            <?php echo _x("\"White\" product", "producttype", "coleteonline");?>
          </option>
        </select>
      </p>
      <p class="form-field">
        <label for="<?php echo "$this->prefix-multiple"?>">
          <?php _e("Product formed from multiple boxes:", "coleteonline"); ?>
        </label>
        <select name="<?php echo "$this->prefix-multiple"?>"
          id="<?php echo "$this->prefix-multiple"?>"
        >
          <option
            value="1"
            <?php echo $product_multiple === "1" ? $sel : "" ?>
          >1</option>
          <option
            value="2"
            <?php echo $product_multiple === "2" ? $sel : "" ?>
          >2</option>
          <option
            value="3"
            <?php echo $product_multiple === "3" ? $sel : "" ?>
          >3</option>
          <option
            value="4"
            <?php echo $product_multiple === "4" ? $sel : "" ?>
          >3</option>
          <option
            value="5"
            <?php echo $product_multiple === "5" ? $sel : "" ?>
          >3</option>
          <option
            value="6"
            <?php echo $product_multiple === "6" ? $sel : "" ?>
          >3</option>
        </select>
      </p>
      <p class="form-field">
        <label for="<?php echo "$this->prefix-not-eligible-for-locker" ?>">
          <?php echo _x("Not eligible for locker", "product", "coleteonline") ?>
        </label>
        <input
          <?php echo $not_eligible_for_locker === "on" ? $checked : "" ?>
          type="checkbox"
          name="<?php echo "$this->prefix-not-eligible-for-locker";?>"
          id="<?php echo "$this->prefix-not-eligible-for-locker";?>"
        />
      </p>
    </div>
    <?php
  }

  public function save_product($post_id, $product) {
    if (isset($_REQUEST["$this->prefix-type"])) {
      update_post_meta($post_id, "$this->prefix-type",
        $_REQUEST["$this->prefix-type"]);
    }

    if (isset($_REQUEST["$this->prefix-multiple"])) {
      update_post_meta($post_id, "$this->prefix-multiple",
        $_REQUEST["$this->prefix-multiple"]);
    }

    if (isset($_REQUEST["$this->prefix-not-eligible-for-locker"])) {
      update_post_meta($post_id, "$this->prefix-not-eligible-for-locker",
        $_REQUEST["$this->prefix-not-eligible-for-locker"]);
    } else {
      delete_post_meta($post_id, "$this->prefix-not-eligible-for-locker");
    }

  }

}
