"use strict";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

jQuery(function ($) {
  if (typeof wc_cart_params === 'undefined') {
    return false;
  }

  var optimizedAjax = wc_cart_params.ajax_url;

  if (typeof customAjaxPath !== 'undefined') {
    if (typeof customAjaxPath.pluginsUrl !== 'undefined') {
      optimizedAjax = customAjaxPath.pluginsUrl;
    }
  }

  function isSpecialCountyCode(code) {
    var codes = ["S1", "S2", "S3", "S4", "S5", "S6"];
    return codes.indexOf(code) > -1;
  }

  function setOption(element, value) {
    if (element.find("option[value='" + value + "']").length) {
      element.val(value).trigger('change');
    } else {
      var newOption = new Option(value, value, true, true);
      element.append(newOption).trigger('change');
    }
  }

  function searchPostalCode() {
    var countyCode = $("#calc_shipping_state").val();
    var city = $("#calc_shipping_city").val();

    if (isSpecialCountyCode(city)) {
      var _ref = [countyCode, city];
      city = _ref[0];
      countyCode = _ref[1];
      city = "Bucuresti";
    }

    $.ajax({
      type: 'GET',
      url: optimizedAjax,
      data: {
        'action': 'coleteonline_postal_code_search',
        'country': $("#calc_shipping_country").val(),
        'street': "--",
        "county": countyCode,
        "city": city,
        "validate_street": 2
      },
      success: function success(result) {
        result = JSON.parse(result);

        if (result.error === true) {
          return;
        }

        if (!result.codes.length) {
          return;
        }

        $("#calc_shipping_postcode").val(result.codes[0].code);
        $("body").trigger('update_checkout');
      }
    });
  }

  var coleteOnlinePostalCodeNeedsUpdate = false;

  function checkPostalCode() {
    coleteOnlinePostalCodeNeedsUpdate = true;
    $.ajax({
      type: 'GET',
      url: optimizedAjax,
      data: {
        'action': 'coleteonline_reverse_postal_code_search',
        'postal_code': $("#calc_shipping_postcode").val(),
        'country': $("#calc_shipping_country").val()
      },
      success: function success(result) {
        result = JSON.parse(result);

        if (result.error === true) {
          coleteOnlinePostalCodeNeedsUpdate = false;
          return;
        }

        if (!result.length && !Object.keys(result).length) {
          // err
          coleteOnlinePostalCodeNeedsUpdate = false;
          $("#calc_shipping_postcode").addClass("woocommerce-invalid");
          return;
        }

        if (isSpecialCountyCode(result.countyCode)) {
          $("#calc_shipping_state").val("B");
          $("#calc_shipping_state").trigger("change");
          $("#calc_shipping_city").val(result.countyCode);
          setOption($("#calc_shipping_locality"), result.locality.county);
        } else {
          $("#calc_shipping_state").val(result.countyCode);
          $("#calc_shipping_state").trigger("change");
          $("#calc_shipping_city").val(result.locality.city);
          setOption($("#calc_shipping_locality"), result.locality.city);
        }

        coleteOnlinePostalCodeNeedsUpdate = false;
      }
    });
  }

  var count = 0;

  function checkIfUpdatedPostalCode(event) {
    if (coleteOnlinePostalCodeNeedsUpdate === true && count < 10) {
      event.preventDefault();
      setTimeout(function () {
        ++count;
        $("button[name='calc_shipping']").click();
      }, 1000);
    } else {
      count = 0;
    }
  }

  function initCartListeners() {
    var reset = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
    $("button[name='calc_shipping']").unbind("click");
    $("button[name='calc_shipping']").click(checkIfUpdatedPostalCode);
    $("#calc_shipping_postcode").change(checkPostalCode);
    $("#calc_shipping_country").unbind("change");
    $("#calc_shipping_country").change(function () {
      initCartListeners();
    });

    if ($("#calc_shipping_country").val() !== "RO") {
      $("#calc_shipping_city_field input").show();
      $("#calc_shipping_locality_wrapper").remove();
      return;
    }

    var options = {
      ajax: {
        url: optimizedAjax,
        dataType: 'json',
        delay: 300,
        data: function data(params) {
          var _params$page;

          if ($("#calc_shipping_state").val() === "B") {
            if (!params.term) {
              params.term = "Sectorul";
            }
          }

          return {
            action: "coleteonline_autocomplete_city",
            country: $("#calc_shipping_country").val(),
            county_code: $("#calc_shipping_state").val(),
            q: params.term,
            // search term
            page: (_params$page = params.page) !== null && _params$page !== void 0 ? _params$page : 1
          };
        },
        processResults: function processResults(data, second) {
          if (data.error === true) {
            return {
              results: [{
                text: second.term,
                id: second.term
              }],
              pagination: {
                more: false
              }
            };
          }

          var fn = function fn(v) {
            return {
              text: v.localityName,
              id: v.localityName,
              countyCode: v.countyCode,
              cityName: v.localityName,
              countyName: v.countyName
            };
          };

          var fnB = function fnB(v) {
            return {
              text: v.countyName,
              id: v.countyName,
              countyCode: v.countyCode,
              cityName: v.countyName,
              countyName: v.localityName
            };
          };

          var results = [];

          for (var i = 0; i < data.data.length; ++i) {
            var d = data.data[i];
            var usedFn = fn;

            if ($("#calc_shipping_state").val() === "B") {
              usedFn = fnB;
            }

            if (d.list) {
              results.push({
                text: d.upLocality + ":",
                children: d.list.map(usedFn)
              });
            } else {
              results.push(usedFn(d));
            }
          }

          var pag = data.pagination;
          return {
            results: results,
            pagination: {
              more: pag.currentPage < pag.totalPages
            }
          };
        }
      },
      language: {
        errorLoading: function errorLoading() {
          return coleteOnlineLanguage.localitySearch.errorLoading;
        },
        inputTooLong: function inputTooLong(args) {
          var remainingChars = args.minimum - args.input.length;
          var message = coleteOnlineLanguage.localitySearch.inputTooLong;
          message = message.replace("%d", remainingChars);
          return message;
        },
        inputTooShort: function inputTooShort(args) {
          var remainingChars = args.minimum - args.input.length;
          var message = coleteOnlineLanguage.localitySearch.inputTooShort;
          message = message.replace("%d", remainingChars);
          return message;
        },
        loadingMore: function loadingMore() {
          return coleteOnlineLanguage.localitySearch.loadingMore;
        },
        noResults: function noResults() {
          return coleteOnlineLanguage.localitySearch.noResults;
        },
        searching: function searching() {
          return coleteOnlineLanguage.localitySearch.searching;
        },
        removeAllItems: function removeAllItems() {
          return coleteOnlineLanguage.localitySearch.removeAllItems;
        },
        removeItem: function removeItem() {
          return coleteOnlineLanguage.localitySearch.removeItem;
        },
        search: function search() {
          return coleteOnlineLanguage.localitySearch.search;
        }
      },
      width: "100%",
      minimumInputLength: $("#calc_shipping_state").val() === "B" ? 0 : 2,
      selectOnClose: coleteOnlineOptions.autoSelectCity === "yes",
      templateSelection: function templateSelection(data) {
        if (data.id === '') {
          if ($("#calc_shipping_state").val() === "") {
            return shippingCityTranslations.emptyState;
          } else {
            return shippingCityTranslations.nonEmptyState;
          }
        }

        return data.text;
      }
    };
    $("#calc_shipping_city_field input").hide();
    $("#calc_shipping_locality_wrapper").remove();
    var shippingCity = $("#calc_shipping_city").val();

    if (['S1', 'S2', 'S3', 'S4', 'S5', 'S6'].includes(shippingCity)) {
      shippingCity = shippingCity.replace("S", "Sectorul ");
    }

    $("#calc_shipping_city_field").append("\n      <div id=\"calc_shipping_locality_wrapper\">\n        <select id=\"calc_shipping_locality\">\n          <option value=\"".concat(shippingCity, "\">\n            ").concat(shippingCity, "\n          </option>\n        </select>\n      </div>\n    "));
    $("#calc_shipping_locality").select2(options);

    if ($("#calc_shipping_state").val() === "") {// $(`#calc_shipping_locality`).attr("disabled", "disabled");
    }

    if (coleteOnlineOptions.postalCodePosition === 'no') {
      $('#calc_shipping_postcode_field').hide();
    } else if (coleteOnlineOptions.postalCodePosition === 'before') {
      $('#calc_shipping_postcode_field').insertAfter('#calc_shipping_country_field');
    }

    setTimeout(function () {
      $("#calc_shipping_state").on('select2:select', function () {
        $("#calc_shipping_locality").val(null).trigger("change");
        setOption($("#calc_shipping_locality"), '');
        $("#calc_shipping_city").val("");

        if ($("#calc_shipping_state").val() === "") {
          $("#calc_shipping_locality").attr("disabled", "disabled");
          $("#calc_shipping_locality").trigger("change");
        } else {
          $("#calc_shipping_locality").removeAttr("disabled");
          $("#calc_shipping_locality").trigger("change");
        }

        if ($("#calc_shipping_state").val() === "B") {
          $("#calc_shipping_locality").select2(_objectSpread(_objectSpread({}, options), {}, {
            minimumInputLength: 0
          }));
        } else {
          $("#calc_shipping_locality").select2(_objectSpread(_objectSpread({}, options), {}, {
            minimumInputLength: 2
          }));
        }
      });
      $("#calc_shipping_locality").unbind('select2:select');
      $("#calc_shipping_locality").on('select2:select', function (ev) {
        if ($("#calc_shipping_state").val() === "B") {
          var _ev$params;

          $("#calc_shipping_city").val((_ev$params = ev.params) === null || _ev$params === void 0 ? void 0 : _ev$params.data.countyCode);
        } else {
          var _ev$params2;

          $("#calc_shipping_city").val((_ev$params2 = ev.params) === null || _ev$params2 === void 0 ? void 0 : _ev$params2.data.cityName);
        }

        searchPostalCode();
      });
    }, 500);
  }

  function hideShippingPointSelects() {
    var shippingPointSelects = $("select[id^='coleteonline_service_shipping_point-']");
    console.log(shippingPointSelects, "SELECTURILE");

    for (var i = 0; i < shippingPointSelects.length; ++i) {
      var select = $(shippingPointSelects[i]);
      select.hide();
    }
  }

  initCartListeners();
  hideShippingPointSelects();
  var cartBodyEvents = "updated_wc_div wc_fragments_loaded " + "wc_fragments_refreshed wc_cart_button_updated " + "added_to_cart removed_from_cart " + "updated_shipping_method";
  $(document.body).on(cartBodyEvents, function () {
    initCartListeners();
  });
});