<?php
/**
 * Order items HTML for meta box.
 */

defined( 'ABSPATH' ) || exit;

$payment_gateway     = wc_get_payment_gateway_by_order( $order );
$line_items          = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
$discounts           = $order->get_items( 'discount' );
$line_items_fee      = $order->get_items( 'fee' );
$line_items_shipping = $order->get_items( 'shipping' );
$order_total_amount  = $order->get_total();

if ( wc_tax_enabled() ) {
	$order_taxes      = $order->get_taxes();
	$tax_classes      = WC_Tax::get_tax_classes();
	$classes_options  = wc_get_product_tax_class_options();
	$show_tax_columns = count( $order_taxes ) === 1;
}

$order_products_skus = [];
$orders_count_needed = 1;

$meta_data = [];

$coleteonline_shipping_meta = $order->get_meta('_coleteonline_courier_order', false);
foreach ( $order->get_shipping_methods() as $shipping_method ) {
  if ($shipping_method->get_method_id() === "coleteonline") {
    $meta_data = $shipping_method->get_formatted_meta_data();
  }
}

$service_selection = array(array("default" => true));
if (isset($meta_data)) {
  foreach ($meta_data as $meta) {
    if ($meta->key === 'co_service_selections') {
      if ($meta->value !== "") {
        $service_selection = json_decode($meta->value, true);
        $orders_count_needed = count($service_selection);
      }
    }
  }
}

$count = $order->get_meta("_coleteonline_courier_extra_order_count");
if ($count < 5) {
  $allow_extra_add = true;
}
while ($count > 0) {
  $service_selection[] = (array("extra" => true));
  --$count;
}
$extra_key = 0;


if (isset($is_extra) && $is_extra === true) {
  $service_selection = array((array("extra" => true)));
  $extra_key = 10;
}

?>
<div id="coleteonline_order_shipping_wrapper"
  data-order-id="<?php echo $order->get_id(); ?>">

  <?php foreach ($service_selection as $key => $selection):
    unset($order_result);
    if (isset($selection["extra"]) && $selection["extra"] === true) {
      $meta_key = "extra-" . $extra_key;
      ++$extra_key;
    } else {
      $meta_key = "main-" . $key;
    }
    foreach ($coleteonline_shipping_meta as $shipping_meta) {
      $shipping_meta = $shipping_meta->get_data()['value'];
      if (isset($shipping_meta["shippingKey"])) {
        if ($shipping_meta["shippingKey"] === $meta_key) {
          $order_result = $shipping_meta["result"];
        }
      } else {
        $order_result = $shipping_meta;
      }
    }

    if (!isset($order_result)):
      $split_value = $order_total_amount;
      if (isset($selection["extra"]) && $selection["extra"] === true) {
      } else if (isset($selection["default"]) && $selection["default"] === true) {
        foreach ($meta_data as $meta) {
          if ($meta->key === 'service_id') {
            $selected_shipping_id = $meta->value;
          } else if ($meta->key === 'shipping_point_id') {
            $selected_shipping_point_id = $meta->value;
          }

        }
        foreach ( $line_items as $item_id => $item ) {
          for ($i = 0; $i < $item->get_quantity(); ++$i) {
            $product = $item->get_product();
            if ($product) {
              $order_products_skus[] = $product->get_sku();
            }
          }
        }
      } else {
        $selected_shipping_id = $selection["selected"]["service"]["id"];
        $order_products_skus = array();
        $split_value = 0;
        foreach ($selection["metadata"] as $meta) {
          foreach ($meta as $m) {
            $split_value += $m["price"];
            $order_products_skus[] = $m["product"]["sku"];
          }
          $split_value += $selection["selected"]["price"]["total"];
        }
      }
      ?>
      <div class="order" data-shipping-order-key="<?php echo $meta_key; ?>">
        <div class="addresses">
          <div class="pick-up-address">
            <h3 class="coleteonline-address-title">
              <?php echo __("Pick up address", "coleteonline"); ?>
            </h3>
            <button type="button" class="button coleteonline-change-address">
              <?php echo __("Change pick up address", "coleteonline"); ?>
            </button>
            <div class="coleteonline-address-select-wrapper">
              <select id="coleteonline-address-select"
                class="wc-enhanced-select"
              >
              </select>
            </div>
            <?php
              $address = get_option("coleteonline_default_shipping_address_full_data");
              $addressObj = json_decode($address, true);
            ?>
            <table class="coleteonline-address-table"
              data-address-id="<?php echo $addressObj["locationId"];?>">
              <thead></thead>
              <tbody>
                <tr>
                  <td colspan="2" class="coleteonline-address-short-name">
                    <b><?php echo $addressObj["shortName"]; ?></b>
                  </td>
                </tr>
                <tr class="title contact-title">
                  <td colspan="2">
                    <b><?php echo __("Contact", "coleteonline"); ?></b>
                  </td>
                </tr>
                <tr>
                  <td class="coleteonline-address-name">
                    <?php echo $addressObj["contact"]["name"]; ?>
                  </td>
                  <td class="coleteonline-address-company">
                    <?php
                      echo isset($addressObj["contact"]["company"]) ?
                      $addressObj["contact"]["company"] :
                      ""
                      ?>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" class="coleteonline-address-phone">
                    <?php
                      echo $addressObj["contact"]["phone"];
                      if (isset($addressObj["contact"]["phone2"])) {
                        echo "<br>";
                        echo $addressObj["contact"]["phone2"];
                      }
                    ?>
                  </td>
                </tr>
                <tr class="title address-title">
                  <td colspan="2">
                    <b><?php echo __("Address", "coleteonline"); ?></b>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" class="coleteonline-address-city-county">
                    <?php echo $addressObj["address"]["city"] . ", " .
                              $addressObj["address"]["county"]; ?>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" class="coleteonline-address-street-number">
                    <?php echo $addressObj["address"]["street"] . ", " .
                              $addressObj["address"]["number"]; ?>
                  </td>
                </tr>
                <tr>
                <td colspan="2" class="coleteonline-address-postal-country">
                    <?php echo $addressObj["address"]["postalCode"] . ", " .
                              $addressObj["address"]["countryCode"]; ?>
                  </td>
                </tr>
                <tr>
                <td colspan="2" class="coleteonline-address-other-data">
                    <?php
                      if (isset($addressObj["address"]["building"])) {
                        echo __("Building", "coleteonline") . " "
                            . $addressObj["address"]["building"];
                      }
                      if (isset($addressObj["address"]["entrance"])) {
                        echo ", " . __("Ent. ", "coleteonline") . " "
                            . $addressObj["address"]["entrance"];
                      }
                      if (isset($addressObj["address"]["floor"])) {
                        echo ", " . __("Floor ", "coleteonline") . " "
                            . $addressObj["address"]["floor"];
                      }
                      if (isset($addressObj["address"]["intercom"])) {
                        echo ", " . __("Intercom ", "coleteonline") . " "
                            . $addressObj["address"]["intercom"];
                      }
                      if (isset($addressObj["address"]["entrance"])) {
                        echo ", " . __("Ent. ", "coleteonline") . " "
                            . $addressObj["address"]["entrance"];
                      }
                      if (isset($addressObj["address"]["apartment"])) {
                        echo ", " . __("Ap. ", "coleteonline") . " "
                            . $addressObj["address"]["apartment"];
                      }
                    ?>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" class="coleteonline-address-landmark">
                    <?php
                      if (isset($addressObj["address"]["landmark"])) {
                        echo $addressObj["address"]["landmark"];
                      }
                    ?>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" class="coleteonline-address-additional-info">
                    <?php
                      if (isset($addressObj["address"]["additionalInfo"])) {
                        echo $addressObj["address"]["AdditionalInfo"];
                      }
                    ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        <div class="delivery-address">
          <h3 class="coleteonline-address-title">
            <?php echo __("Delivery address", "coleteonline");?>
          </h3>
          <?php
            if ( $order->get_formatted_shipping_address() ) {
              echo '<p>' . wp_kses( $order->get_formatted_shipping_address(), array( 'br' => array() ) ) . '</p>';
            } else {
              echo '<p class="none_set"><strong>' . __( 'Address:', 'woocommerce' ) . '</strong> ' . __( 'No shipping address set.', 'woocommerce' ) . '</p>';
            }
          ?>
        </div>
      </div>
        <h3>
          <?php echo __("Extra options", "coleteonline"); ?>
        </h3>
        <table>
          <tbody>
            <tr>
              <td>
                <label for="coleteonline-repayment-amount">
                  <?php echo wc_help_tip( __( 'The amount that will be requested at delivery', 'coleteonline' ) ); ?>
                  <?php esc_html_e( 'Repayment amount:', 'coleteonline' ); ?>
                </label>
              </td>
              <td>
                <input type="text" id="coleteonline-repayment-amount" name="coleteonline-repayment-amount"
                value="<?php echo $order->get_payment_method() === "cod" ? $split_value : 0;?>"/>
                <?php echo $order->get_currency(); ?>
              </td>
            </tr>
            <tr>
              <td>
                <label for="coleteonline-insurance-amount">
                  <?php echo wc_help_tip( __( 'The amount for which the order will be insured', 'coleteonline' ) ); ?>
                  <?php esc_html_e( 'Insurance amount:', 'coleteonline' ); ?>
                </label>
              </td>
              <td>
                <input type="text" id="coleteonline-insurance-amount" name="coleteonline-insurance-amount"
                value="<?php
                  switch (get_option("coleteonline_order_insurance")) {
                    case "no":
                      echo 0;
                      break;
                    case "always":
                      echo $split_value;
                      break;
                    case "when_no_repayment":
                      if ($order->get_payment_method() === "cod") {
                        echo 0;
                      } else {
                        echo $split_value;
                      }
                      break;
                  }?>"/>
                  <?php echo $order->get_currency(); ?>
              </td>
            </tr>
            <tr>
              <td>
                <label for="coleteonline-open-package">
                  <?php echo wc_help_tip( __( 'Use the extra service open at delivery', 'coleteonline' ) ); ?>
                  <?php esc_html_e( 'Open package at delivery:', 'coleteonline' ); ?>
                </label>
              </td>
              <td>
                <input type="checkbox" id="coleteonline-open-package" name="coleteonline-open-package"
                <?php
                  switch (get_option("coleteonline_order_open_at_delivery")) {
                    case "no":
                      break;
                    case "always":
                      echo "checked";
                      break;
                    case "user_choice":
                      if ($order->get_meta("_coleteonline_open_package") === "on") {
                        echo "checked";
                      }
                      break;
                  }?>>
              </td>
            </tr>
          </tbody>
        </table>
        <h3><?php echo __("Packages", "coleteonline"); ?></h3>
        <table class="coleteonline-packages-table">
          <thead style="text-align: center;">
            <td colspan="2">
              <?php echo __("Product", "coleteonline"); ?>
            </td>
            <td colspan="1">
              <?php echo __("Package", "coleteonline"); ?>
            </td>
            <td colspan="1">
              <?php echo __("Weight", "coleteonline"); ?>
            </td>
            <td colspan="3">
              <?php echo __("Dimensions", "coleteonline"); ?>
            </td>
            <td colspan="1">
              <?php echo __("Remove", "coleteonline"); ?>
            </td>
          </thead>
          <tbody>
            <?php
              $packages = array();
              $packaging_option = get_option("coleteonline_packaging_method");
              if ($packaging_option === "all_in_package") {
                $packages[1] = array();
                foreach ( $line_items as $item_id => $item ) {
                  for ($i = 0; $i < $item->get_quantity(); ++$i) {
                    $product = $item->get_product();
                    if (!$product) {
                      continue;
                    }
                    if (in_array($product->get_sku(), $order_products_skus)) {
                      $packages[1][] = $item;
                      $pos = array_search($product->get_sku(),
                                          $order_products_skus);
                      unset($order_products_skus[$pos]);
                    }
                  }
                }
              } else if ($packaging_option === "each_in_package") {
                $cnt = 0;
                foreach ( $line_items as $item_id => $item ) {
                  for ($i = 0; $i < $item->get_quantity(); ++$i) {
                    $product = $item->get_product();
                    if (in_array($product->get_sku(), $order_products_skus)) {
                      $packages[$cnt++] = array($item);
                      $pos = array_search($product->get_sku(),
                                          $order_products_skus);
                      unset($order_products_skus[$pos]);
                    }
                  }
                }
              }
            ?>
            <?php foreach ($packages as $package => $contents): ?>
              <?php foreach ($contents as $item): ?>
                <?php
                  $product = $item->get_product();
                  $thumbnail = $product ?
                              apply_filters( 'woocommerce_admin_order_item_thumbnail', $product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) :
                              '';
                  $product_weight = wc_get_weight($product->get_weight(), "kg");
                  $product_dimensions = array(
                    "width" => wc_get_dimension($product->get_dimensions(false)["width"], "cm"),
                    "length" => wc_get_dimension($product->get_dimensions(false)["length"], "cm"),
                    "height" => wc_get_dimension($product->get_dimensions(false)["height"], "cm")
                  );
                  $prefix = "coleteonline-shipping-product";
                  $product_id = $product->get_id();

                  $product_type = get_post_meta($product_id, "$prefix-type", true) ?: "default";
                  $product_multiple = get_post_meta($product_id, "$prefix-multiple", true) ?: "1";

                  if (get_option("coleteonline_advanced_product_fields", "no") === "no") {
                    $product_type = "default";
                    $product_multiple = "1";
                  }

                  $product_metadata = array(
                    "type" => $product_type,
                    "multiple" => $product_multiple,
                    "price" => wc_get_price_including_tax($product),
                    "product" => array(
                      "sku" => $product->get_sku()
                    )
                  );
                ?>
                <tr
                  data-package="<?php echo $package;?>"
                  data-product-metadata='<?php echo json_encode($product_metadata);?>'
                  class="product-row"
                >
                  <td class="coleteonline-thumb">
                    <?php
                      echo '<div class="wc-order-item-thumbnail">' . wp_kses_post( $thumbnail ) . '</div>';
                    ?>
                  </td>
                  <td class="coleteonline-product-name"><?php echo trim($item->get_name()); ?></td>
                  <td class="coleteonline-package-select">
                  </td>
                  <td class="coleteonline-package-weight"
                    data-package-weight="<?php echo $product_weight?>"
                  >
                    <?php echo $product_weight; ?> kg
                  </td>
                  <td class="coleteonline-package-dimensions width"
                    data-package-width="<?php echo $product_dimensions["width"];?>"
                  >
                    <?php echo $product_dimensions["width"]; ?> cm
                  </td>
                  <td class="coleteonline-package-dimensions length"
                  data-package-length="<?php echo $product_dimensions["length"];?>"
                  >
                    <?php echo $product_dimensions["length"]; ?> cm
                  </td>
                  <td class="coleteonline-package-dimensions height"
                    data-package-height="<?php echo $product_dimensions["height"];?>"
                  >
                    <?php echo $product_dimensions["height"] ?> cm
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2">
                <button type="button" class="button coleteonline-add-package">
                  <?php echo __("Add package", "coleteonline"); ?>
                </button>
              </td>
              <td colspan="4"></td>
              <td colspan="2">
                <button type="button" class="button coleteonline-reset-packages">
                  <?php echo __("Reset packages", "coleteonline"); ?>
                </button>
              </td>
            </tr>
            <tr>
              <td colspan="4">
              <?php
                $package_prefer_envelope = get_option("coleteonline_packaging_prefer_envelope", "no") === "yes";
              ?>
              <label for="coleteonline-address-select">
                Package type
              </label>
              <select id="coleteonline-order-package-type"
                class="wc-enhanced-select"
                data-prefer-envelope="<?php echo get_option("coleteonline_packaging_prefer_envelope"); ?>"
              >
                <option value="1" <?php echo $package_prefer_envelope ? "selected" : "" ?>>Envelope</option>
                <option value="2" <?php echo !$package_prefer_envelope ? "selected" : "" ?>>Parcel</option>
              </select>
              </td>
              <td colspan="4"></td>
            </tr>
            <tr>
              <td colspan="2">
                <label for="coleteonline-order-content-input">
                  <?php _e('Order content:', 'coleteonline'); ?>
                </label>
                <input type="text"
                  class="coleteonline-order-content-input"
                  data-autocomplete-type="<?php echo get_option("coleteonline_packaging_content_autocomplete"); ?>"
                  data-autocomplete-fixed-string="<?php echo get_option("coleteonline_packaging_content_autocomplete_fixed_string"); ?>"
                >
              </td>
            </tr>
          </tfoot>
        </table>

        <div class="coleteonline-packages-errors">
          <p>
            <?php echo __("Before placing an order, please be sure to fill the dimensions for each package! You can preset your products dimensions in the 'Products' section for future orders.", "coleteonline"); ?>
          </p>
        </div>

        <div class="coleteonline-show-offers-errors">
        </div>

        <h3 class="coleteonline-courier-list-title">
          <?php echo __("Available services", "coleteonline"); ?>
        </h3>

        <button type="button" class="button button-primary coleteonline-do-fetch-services-list">
          <?php _e('Show offers', 'coleteonline'); ?>
        </button>
        <div class="coleteonline-offers">
          <div class="coleteonline-offers-loading">
            <div class="coleteonline-lds-ring">
              <div></div><div></div><div></div><div></div>
            </div>
          </div>
          <table
            class="coleteonline-couriers-offers"
            <?php
              if (isset($selected_shipping_id)) {
                echo "data-selected-courier-id='{$selected_shipping_id}'";
              }

              if (isset($selected_shipping_point_id)) {
                echo "data-selected-shipping-point-id='{$selected_shipping_point_id}'";
              }
            ?>
          >
            <thead>
              <tr>
                <td></td>
                <td><?php _e("Courier", "coleteonline") ?></td>
                <td><?php _e("Service", "coleteonline") ?></td>
                <td><?php _e("Price", "coleteonline") ?></td>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>

        <div class="coleteonline-show-order-errors">
        </div>
        <div class="coleteonline-orders-loading">
          <div class="coleteonline-lds-ring">
            <div></div><div></div><div></div><div></div>
          </div>
        </div>
        <button type="button" class="button button-primary coleteonline-do-create-courier-order"
        >
          <?php _e('Create courier order', 'coleteonline'); ?>
        </button>
      </div>

      <?php else:
        require COLETE_ONLINE_ROOT .
              "/admin/views/html-order-shipping-meta-box-courier-orders.php";
      endif; ?>

  <?php endforeach; ?>

  <?php if (isset($allow_extra_add) && $allow_extra_add === true): ?>
    <div class="add-extra-orders">
      <button type="button" class="button button-primary do-add-extra-order">
        <?php _e("Add extra shipping order", "coleteonline"); ?>
      </button>
    </div>
  <?php endif; ?>

</div>