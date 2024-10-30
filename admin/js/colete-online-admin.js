"use strict";

(function ($) {
  'use strict'; // setTimeout(() => {

  $('table.shipping-services-table-coleteonline tbody').sortable(); // }, 5000);

  $('table.shipping-services-table-coleteonline tbody tr').each(function () {
    var el = $(this).find("td:first");
    el.addClass("ui-sortable-handle");
    el.css("display: table-cell");
  }); // .addClass("ui-sortable-handle");
  // $('table tbody td:first').addStyle("display: table-cell");
  // hide/display options based on selection --- start

  function coleteOnlineSelectionChoiceChange(selection) {
    if (selection === "provided") {
      $("#coleteonline_courier_display_order_type").parents("tr").hide();
      $("#coleteonline_service_selection_type").parents("tr").show();
      $("#coleteonline_service_selection_display_count").parents("tr").hide();
      $("#coleteonline_service_selection_custom_name_toggle").val("no");
      $("#coleteonline_service_selection_custom_name_toggle").parents("tr").hide();
      $("#coleteonline_service_selection_custom_name").parents("tr").hide();
    } else {
      $("#coleteonline_courier_display_order_type").parents("tr").show();
      $("#coleteonline_service_selection_type").parents("tr").hide();
      $("#coleteonline_service_selection_display_count").parents("tr").show();
      $("#coleteonline_service_selection_custom_name_toggle").parents("tr").show();
    }
  }

  $("#coleteonline_courier_selection_choice_type").change(function () {
    coleteOnlineSelectionChoiceChange(this.value);
  });
  coleteOnlineSelectionChoiceChange($("#coleteonline_courier_selection_choice_type").val()); // hide/display options based on selection --- end

  function coleteOnlineSelectionDisplayCustomNameToggle(checked) {
    if (checked) {
      $("#coleteonline_service_selection_custom_name").parents("tr").show();
    } else {
      $("#coleteonline_service_selection_custom_name").parents("tr").hide();
    }
  }

  $("#coleteonline_service_selection_custom_name_toggle").change(function () {
    coleteOnlineSelectionDisplayCustomNameToggle(this.checked);
  });
  coleteOnlineSelectionDisplayCustomNameToggle($("#coleteonline_service_selection_custom_name_toggle").is(":checked"));

  function coleteOnlineSelectionDisplayCustomNameFirstToggle(checked) {
    if (checked) {
      $("#coleteonline_service_selection_custom_name_first").parents("tr").show();
    } else {
      $("#coleteonline_service_selection_custom_name_first").parents("tr").hide();
    }
  }

  $("#coleteonline_service_selection_first_custom_name_toggle").change(function () {
    coleteOnlineSelectionDisplayCustomNameFirstToggle(this.checked);
  });
  coleteOnlineSelectionDisplayCustomNameFirstToggle($("#coleteonline_service_selection_first_custom_name_toggle").is(":checked"));

  function createSelectedServicesList() {
    var selected = [];
    $(".shipping-services-table-coleteonline tbody").find("input[type='checkbox']:checked").parents("tr").find("td.id").each(function (k, el) {
      selected.push(el.innerHTML);
    });
    $("#coleteonline_service_list_hidden").val(JSON.stringify(selected));
  }

  $(".shipping-services-table-coleteonline tbody").find("input[type='checkbox']").change(function () {
    createSelectedServicesList();
  });
  $("#cb-select-all-2, #cb-select-all-1").change(function () {
    return createSelectedServicesList();
  });
  $(".shipping-services-table-coleteonline").on("sortstop", createSelectedServicesList);

  function priceTypeChange() {
    if ($("#coleteonline_price_type").val() === "fixed_price") {
      $("#coleteonline_price_fixed_price_amount").parents("tr").show();
      $("#coleteonline_price_add_fixed_amount").parents("tr").hide();
      $("#coleteonline_price_add_percent_amount").parents("tr").hide();
      $("#coleteonline_price_round_before_tax").parents("tr").hide();
    } else {
      $("#coleteonline_price_fixed_price_amount").parents("tr").hide();
      $("#coleteonline_price_add_fixed_amount").parents("tr").show();
      $("#coleteonline_price_add_percent_amount").parents("tr").show();
      $("#coleteonline_price_round_before_tax").parents("tr").show();
    }
  }

  $("#coleteonline_price_type").change(priceTypeChange);
  priceTypeChange();

  function freeShippingTypeChange() {
    var type = +$("#coleteonline_price_free_shipping").val();
    $("#coleteonline_price_free_shipping_min_amount").parents("tr").hide();
    $("#coleteonline_price_free_shipping_classes").parents("tr").hide();
    $("#coleteonline_price_free_shipping_after_name_text").parents("tr").hide();

    if (type === 1 || type === 3) {
      $("#coleteonline_price_free_shipping_min_amount").parents("tr").show();
      $("#coleteonline_price_free_shipping_after_name_text").parents("tr").show();
    }

    if (type === 2 || type === 3) {
      $("#coleteonline_price_free_shipping_classes").parents("tr").show();
      $("#coleteonline_price_free_shipping_after_name_text").parents("tr").show();
    }
  }

  $("#coleteonline_price_free_shipping").change(freeShippingTypeChange);
  freeShippingTypeChange();

  function openAtDeliveryChange() {
    var val = $("#coleteonline_order_open_at_delivery").val();

    if (val === "user_choice") {
      $("#coleteonline_order_open_at_delivery_text").parents("tr").show();
    } else {
      $("#coleteonline_order_open_at_delivery_text").parents("tr").hide();
    }
  }

  $("#coleteonline_order_open_at_delivery").change(openAtDeliveryChange);
  openAtDeliveryChange();

  try {
    var selected = JSON.parse($("#coleteonline_service_list_hidden").val());

    var _loop = function _loop(i) {
      var id = selected[i];
      $('.shipping-services-table-coleteonline tbody tr').each(function (k, el) {
        var elId = $(el).find(".id").text();

        if (+elId === +id) {
          $(el).appendTo('.shipping-services-table-coleteonline tbody');
        }
      });
      $("#coleteonline-service-id-".concat(id)).attr('checked', 'checked');
    };

    for (var i = 0; i < selected.length; ++i) {
      _loop(i);
    }

    var cntChecked = $(".shipping-services-table-coleteonline tbody").find("input[type='checkbox']:checked").length;
    var cntAll = $(".shipping-services-table-coleteonline tbody").find("input[type='checkbox']").length;

    if (cntChecked === cntAll) {
      $("#cb-select-all-2, #cb-select-all-1").attr('checked', 'checked');
    }
  } catch (e) {}

  $.post(ajaxurl, {
    action: "coleteonline_get_all_addresses"
  }, function (response) {
    response = JSON.parse(response);
    var element = $("#coleteonline_default_shipping_address");
    var options = [];
    var firstId = 0;

    for (var _i = 0; _i < response.addresses.length; ++_i) {
      var o = response.addresses[_i];

      if (!firstId) {
        firstId = o.id;
      }

      options.push(new Option(o.shortName + " - " + o.address, o.id, false, false));
    }

    element.append(options);
    $("#coleteonline_default_shipping_address").on("select2:select", function (ev) {
      $("#coleteonline_default_shipping_address_id").val("".concat(ev.params.data.id));
      var found = response.addresses.find(function (el) {
        return +el.id === +ev.params.data.id;
      });

      if (found === undefined) {
        return;
      }

      $("#coleteonline_default_shipping_address_full_data").val(JSON.stringify(found.addressObject));
    });

    if (response.selected) {
      element.val(response.selected);
    } else {
      $("#coleteonline_default_shipping_address").trigger({
        type: 'select2:select',
        params: {
          data: {
            id: firstId
          }
        }
      });
    }
  });
  $("#coleteonline_default_shipping_address_id").parents("tr").hide();
  $("#coleteonline_default_shipping_address_full_data").parents("tr").hide();

  function handleFallbackPrice() {
    var val = $("#coleteonline_display_fallback_price").val();

    if (val === "no") {
      $("#coleteonline_fallback_price_amount").parents("tr").hide();
      $("#coleteonline_fallback_service_name").parents("tr").hide();
    } else {
      $("#coleteonline_fallback_price_amount").parents("tr").show();
      $("#coleteonline_fallback_service_name").parents("tr").show();
    }
  }

  $("#coleteonline_display_fallback_price").change(handleFallbackPrice);
  handleFallbackPrice();

  function handleOrderShippingStatus() {
    var val = $("#coleteonline_order_add_custom_shipping_status").val();

    if (val === "no") {
      $("#coleteonline_order_change_to_shipping_status").parents("tr").hide();
    } else {
      $("#coleteonline_order_change_to_shipping_status").parents("tr").show();
    }
  }

  $("#coleteonline_order_add_custom_shipping_status").change(handleOrderShippingStatus);
  handleOrderShippingStatus();
})(jQuery);