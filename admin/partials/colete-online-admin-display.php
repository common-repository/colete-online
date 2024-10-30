<?php

defined( 'ABSPATH' ) || exit;

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       www.colete-online.ro
 * @since      1.0.0
 *
 * @package    Colete_Online
 * @subpackage Colete_Online/admin/partials
 */
?>

<div class="coleteonline-not-logged-notice">
<p>
  <?php
    esc_html_e("For seeing the options please insert the client_id and " .
        "client_secret provided by the ", "coleteonline");
      echo ' <a href="https://colete-online.ro">' .
      __("Colete-Online", "coleteonline") .  '</a> ';
    esc_html_e("staff and save the changes!", "coleteonline");
    echo '<br><b>';
    esc_html_e("The API credentials are different from the user and password used to login on www.colete-online.ro!", "coleteonline");
    echo '</b>'
  ?>
</p>
</div>
