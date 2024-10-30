<?php
defined( 'ABSPATH' ) || exit;
?>

<?php $shipping_data = $order_result; ?>
<div class="order" data-shipping-order-key="<?php echo $meta_key; ?>">
  <div class="coleteonline-courier-order-wrapper">
    <div class="coleteonline-courier-order">
      <div class="coleteonline-courier-service">
        <?php
          echo $shipping_data['service']['service']['courierName'] . ' ' .
                $shipping_data['service']['service']['name'];
        ?>
      </div>
      <div class="coleteonline-courier-price">
        <span class="coleteonline-courier-price-total">
          <?php
            echo $shipping_data['service']['price']['total'] . ' ron';
          ?>
        </span>
        <span class="coleteonline-courier-price-no-vat">
          <?php
            echo '(' . $shipping_data['service']['price']['noVat'] . ' ron + TVA)';
          ?>
        </span>
      </div>
      <div class="coleteonline-courier-identification">
        <div class="coleteonline-courier-awb">
        <?php
          echo $shipping_data['awb'];
        ?>
        </div>
        <div class="coleteonline-courier-unique-id">
          <?php
            echo $shipping_data['uniqueId'];
          ?>
        </div>
      </div>
      <div class="coleteonline-courier-actions">
        <label for="coleteonline-format-select">Format:</label>
        <select id="coleteonline-format-select" name="co-format">
            <option value="A4">A4</option>
            <option value="A6">A6</option>
        </select>
        <button type="button"
          class="button button-primary coleteonline-do-download-awb"
          data-unique-id="<?php echo $shipping_data['uniqueId']; ?>"
          data-awb="<?php echo $shipping_data['awb']; ?>"
        >
          <?php echo __("Download AWB", "coleteonline"); ?>
        </button>
        <div class="coleteonline-file-download-loading">
          <div class="coleteonline-lds-ring">
            <div></div><div></div><div></div><div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>