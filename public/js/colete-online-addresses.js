"use strict";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

jQuery(function ($) {
  if (typeof wc_checkout_params === 'undefined' && typeof woocommerce_params === 'undefined') {
    return false;
  }

  function initShippingPointSelects() {
    var shippingPointSelects = $("select[id^='coleteonline_service_shipping_point-']");

    for (var i = 0; i < shippingPointSelects.length; ++i) {
      var select = $(shippingPointSelects[i]);
      select.select2();
    }
  }

  $(document.body).on("updated_checkout", function () {
    initShippingPointSelects();
  });

  function sanitizeStr(val) {
    val = val.replace(/[iIîÎâÂaAăĂâÂ]/g, "a");
    val = val.replace(/[tTȚț]/g, "t");
    val = val.replace(/[sSȘș]/g, "s");
    return val;
  }

  var ajaxUrl = '';

  if (typeof wc_checkout_params !== 'undefined') {
    ajaxUrl = wc_checkout_params.ajax_url;
  } else if (typeof woocommerce_params !== 'undefined') {
    ajaxUrl = woocommerce_params.ajax_url;
  }

  var optimizedAjax = ajaxUrl;

  if (typeof customAjaxPath !== 'undefined') {
    if (typeof customAjaxPath.pluginsUrl !== 'undefined') {
      optimizedAjax = customAjaxPath.pluginsUrl;
    }
  }

  function isWooCityField() {
    return coleteOnlineOptions.addressCityField === "woocommerce";
  }

  function getStreetOp() {
    return coleteOnlineOptions.addressStreetField;
  }

  function getStreetFieldId(type) {
    return getStreetOp() === "displayWithAutocomplete" ? "#".concat(type, "_street") : "#".concat(type, "_address_1");
  }

  function hasStreetNrField() {
    return coleteOnlineOptions.addressStreetNumberField !== "no";
  }

  function groupBy(xs, key) {
    return xs.reduce(function (rv, x) {
      (rv[x[key]] = rv[x[key]] || []).push(x);
      return rv;
    }, {});
  }

  ;

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

  function checkPhone(type) {
    if ($("#".concat(type, "_phone")).length) {
      $.ajax({
        type: 'POST',
        url: optimizedAjax,
        data: {
          'action': 'coleteonline_phone_number_check',
          'phone_number': $("#".concat(type, "_phone")).val(),
          'country': $("#".concat(type, "_country")).val()
        },
        success: function success(result) {
          result = JSON.parse(result);

          if (result === null || result.valid || result.error) {
            $("#".concat(type, "_phone_field")).removeClass("woocommerce-invalid");
            $("#".concat(type, "_phone_field")).addClass("woocommerce-valid");
          } else {
            $("#".concat(type, "_phone_field")).removeClass("woocommerce-valid");
            $("#".concat(type, "_phone_field")).addClass("woocommerce-invalid");
          }
        }
      });
    }
  }

  function validatePostalCode(type) {
    var postcode = $("#".concat(type, "_postcode")).val();

    if (postcode === undefined || !postcode.length) {
      return;
    }

    var countyCode = $("#".concat(type, "_state")).val();
    var city = $("#".concat(type, "_city")).val();

    if ($("#".concat(type, "_country")).val() !== "RO") {
      countyCode = "--";
    } else {
      if (isSpecialCountyCode(city)) {
        var _ref = [countyCode, city];
        city = _ref[0];
        countyCode = _ref[1];
        city = "Bucuresti";
      }
    }

    var streetField = getStreetFieldId(type);
    var street = $(streetField).val();

    if (street === undefined || street === null || !street.length) {
      street = "--";
    }

    if (city === undefined || !city.length) {
      city = "--";
    }

    $.ajax({
      type: 'GET',
      url: optimizedAjax,
      data: {
        'action': 'coleteonline_validate_postal_code',
        'country': $("#".concat(type, "_country")).val(),
        'street': street,
        "county": countyCode,
        "city": city,
        "postal_code": postcode
      },
      success: function success(result) {
        result = JSON.parse(result);

        if (result.error == true) {
          $("body").trigger('update_checkout');
          return;
        }

        if (result.format === false || result.location === false) {
          $("#".concat(type, "_postcode_field")).addClass('woocommerce-invalid');
          $("body").trigger('update_checkout');
          return;
        }

        $("#".concat(type, "_postcode_field")).removeClass('woocommerce-invalid');
        $("body").trigger('update_checkout');
      }
    });
  }

  function checkPostalCode(type) {
    $.ajax({
      type: 'GET',
      url: optimizedAjax,
      data: {
        'action': 'coleteonline_reverse_postal_code_search',
        'postal_code': $("#".concat(type, "_postcode")).val(),
        'country': $("#".concat(type, "_country")).val()
      },
      success: function success(result) {
        result = JSON.parse(result);

        if (result.error === true) {
          return;
        }

        var setStr = function setStr() {
          var streetField = getStreetFieldId(type);
          var currentStreet = $(streetField).val();
          var found = false;

          var _iterator = _createForOfIteratorHelper(result.street),
              _step;

          try {
            for (_iterator.s(); !(_step = _iterator.n()).done;) {
              var s = _step.value;

              if (sanitizeStr(s).toLowerCase() === sanitizeStr(currentStreet).toLowerCase()) {
                found = true;
              }
            }
          } catch (err) {
            _iterator.e(err);
          } finally {
            _iterator.f();
          }

          if (!found) {
            if (getStreetOp() === "displayWithAutocomplete") {
              setOption($("#".concat(type, "_street")), result.street[0]);
            }

            $("#".concat(type, "_address_1")).val(result.street[0]);
          }
        };

        if (!result.length && !Object.keys(result).length) {
          // err
          $("#".concat(type, "_postcode_field")).addClass("woocommerce-invalid");
          return;
        } else {
          $("#".concat(type, "_postcode_field")).removeClass("woocommerce-invalid");
        }

        if (coleteOnlineOptions.separateFields) {
          if (isSpecialCountyCode(result.countyCode)) {
            $("#".concat(type, "_state")).val("B");
            $("#".concat(type, "_state")).trigger("change");
            $("#".concat(type, "_city")).val(result.countyCode);

            if (!isWooCityField()) {
              setOption($("#".concat(type, "_locality")), result.locality.county);
            }
          } else {
            $("#".concat(type, "_state")).val(result.countyCode);
            $("#".concat(type, "_state")).trigger("change");
            $("#".concat(type, "_city")).val(result.locality.city);

            if (!isWooCityField()) {
              setOption($("#".concat(type, "_locality")), result.locality.city);
            }
          }

          if (result.street !== undefined && result.street.length) {
            setStr();
          }

          if (!isWooCityField()) {
            $("#".concat(type, "_locality")).removeAttr("disabled");
          }

          $("body").trigger('update_checkout');
        } else {
          $("#".concat(type, "_state")).val(result.countyCode);
          $("#".concat(type, "_city")).val(result.locality.city);
          var l = result.locality;
          var loc = "".concat(l.city, ", ").concat(l.county);

          if (result.street !== undefined && result.street.length) {
            setStr();
          } else {
            if (getStreetOp() === "displayWithAutocomplete") {
              setOption($("#".concat(type, "_street")), "");
            }

            $("#".concat(type, "_address_1")).val("");
          }

          if (l.city && l.county) {
            setOption($("#".concat(type, "_city_state")), "".concat(l.city, ", ").concat(l.county));
          }

          $("#".concat(type, "_city_state")).trigger({
            type: 'select2:select',
            params: {
              data: {
                countyCode: result.countyCode,
                id: loc,
                text: loc,
                countyName: l.county,
                cityName: l.city,
                trigger: false
              }
            }
          });
        }
      }
    });
  }

  function searchPostalCode(type) {
    var countyCode = $("#".concat(type, "_state")).val();
    var city = $("#".concat(type, "_city")).val();

    if ($("#".concat(type, "_country")).val() !== "RO") {
      countyCode = "--";
    } else {
      if (isSpecialCountyCode(city)) {
        var _ref2 = [countyCode, city];
        city = _ref2[0];
        countyCode = _ref2[1];
        city = "Bucuresti";
      }
    }

    var streetField = getStreetFieldId(type);
    var street = $(streetField).val();

    if (street === undefined || street === null || !street.length) {
      street = "--";
    }

    $.ajax({
      type: 'GET',
      url: optimizedAjax,
      data: {
        'action': 'coleteonline_postal_code_search',
        'country': $("#".concat(type, "_country")).val(),
        'street': street,
        "county": countyCode,
        "city": city,
        "validate_street": 2
      },
      success: function success(result) {
        result = JSON.parse(result);

        if (result.error == true) {
          return;
        }

        if (!result.codes.length) {
          return;
        }

        $("#".concat(type, "_postcode")).val(result.codes[0].code);
        $("#".concat(type, "_postcode_field")).removeClass('woocommerce-invalid');
        $("body").trigger('update_checkout');
      }
    });
  }

  var previousCountry = {
    shipping: undefined,
    billing: undefined
  };
  var initialized = {
    shipping: false,
    billing: false
  };

  function countryChangeHandler(type, value) {
    if (previousCountry[type] === value) {
      return;
    }

    previousCountry[type] = value;

    if (initialized[type]) {
      setOption($("#".concat(type, "_city_state")), "");

      if (getStreetOp() === "displayWithAutocomplete") {
        setOption($("#".concat(type, "_street")), "");
      }

      setOption($("#".concat(type, "_locality")), "");
      $("#".concat(type, "_city")).val("");
      $("#".concat(type, "_postcode")).val("");
      $("#".concat(type, "_address_1")).val("");

      if (hasStreetNrField()) {
        $("#".concat(type, "_street_number")).val("");
      }
    }

    if (value === "RO") {
      if (coleteOnlineOptions.postalCodePosition === "no") {
        $("#".concat(type, "_postcode_field")).addClass("hidden-field");
      }

      if (getStreetOp() === "displayWithAutocomplete") {
        $("#".concat(type, "_address_1_field")).addClass("hidden-field");
        $("#".concat(type, "_street_field")).removeClass("hidden-field");
        $("#".concat(type, "_street_field")).find(".optional").addClass("hidden-field");
      }

      if (hasStreetNrField()) {
        $("#".concat(type, "_street_number_field")).removeClass("hidden-field");
        $("#".concat(type, "_street_number_field")).find(".optional").addClass("hidden-field");
      }

      if (coleteOnlineOptions.separateFields && !isWooCityField()) {
        $("#".concat(type, "_city_field")).addClass("hidden-field");
        $("#".concat(type, "_city_field")).find(".optional").addClass("hidden-field");
        $("#".concat(type, "_state_field")).removeClass("hidden-field");
        $("#".concat(type, "_locality_field")).removeClass("hidden-field");
      } else if (!isWooCityField()) {
        if (!$("#".concat(type, "_state")).val() || !$("#".concat(type, "_city")).val()) {
          setOption($("#".concat(type, "_city_state")), "");

          if (getStreetOp() === "displayWithAutocomplete") {
            setOption($("#".concat(type, "_street")), "");
          }
        }

        $("#".concat(type, "_state_field")).addClass("hidden-field");
        $("#".concat(type, "_city_field")).addClass("hidden-field");
        $("#".concat(type, "_locality_field")).addClass("hidden-field");
        $("#".concat(type, "_city_state_field")).removeClass("hidden-field");
        $("#".concat(type, "_city_state_field")).find(".optional").addClass("hidden-field");
      }

      if (initialized[type]) {
        return;
      }

      if (!isWooCityField()) {
        if ($("#".concat(type, "_city")).val()) {
          var city = $("#".concat(type, "_city")).val();

          if (isSpecialCountyCode(city)) {
            city = city.replace("S", "Sectorul ");
          }

          setOption($("#".concat(type, "_locality")), city);
        }
      }

      var street = $("#".concat(type, "_address_1")).val();

      if (street !== undefined && street.length && getStreetOp() === "displayWithAutocomplete") {
        setOption($("#".concat(type, "_street")), street);
      }
    } else {
      if (coleteOnlineOptions.postalCodePosition === "no") {
        $("#".concat(type, "_postcode_field")).removeClass("hidden-field");
      }

      if (hasStreetNrField()) {
        $("#".concat(type, "_street_number_field")).addClass("hidden-field");
        $("#".concat(type, "_street_number_field")).find(".optional").addClass("hidden-field");
      }

      if (getStreetOp() === "displayWithAutocomplete") {
        $("#".concat(type, "_address_1_field")).removeClass("hidden-field");
        $("#".concat(type, "_street_field")).addClass("hidden-field");
      }

      if (!coleteOnlineOptions.separateFields) {
        $("#".concat(type, "_state_field")).removeClass("hidden-field");
        $("#".concat(type, "_city_field")).removeClass("hidden-field");
        $("#".concat(type, "_city_state_field")).addClass("hidden-field");
      } else if (!isWooCityField()) {
        $("#".concat(type, "_city_field")).removeClass("hidden-field");
        $("#".concat(type, "_locality_field")).addClass("hidden-field");
      }

      if (value === "" && !isWooCityField()) {
        $("#".concat(type, "_state_field")).addClass("hidden-field");
      }
    }

    if (!initialized[type]) {
      initialized[type] = true;
      $("#".concat(type, "_postcode")).change(function () {
        if ($("#".concat(type, "_country")).val() === 'RO') {
          checkPostalCode(type);
        } else {
          validatePostalCode(type);
        }
      });
      $("#".concat(type, "_postcode")).keydown(function (ev) {
        ev.stopPropagation();
      });

      if (getStreetOp() === "displayWithAutocomplete") {
        $("#".concat(type, "_street")).select2({
          ajax: {
            url: optimizedAjax,
            dataType: 'json',
            delay: 300,
            data: function data(params) {
              var _params$page;

              var countyCode = $("#".concat(type, "_state")).val();
              var city = $("#".concat(type, "_city")).val();

              if (isSpecialCountyCode(city)) {
                var _ref3 = [countyCode, city];
                city = _ref3[0];
                countyCode = _ref3[1];
              }

              if (city === "B") {
                city = "Bucuresti";
              }

              return {
                action: "coleteonline_autocomplete_street",
                country: $("#".concat(type, "_country")).val(),
                county: countyCode,
                city: city,
                q: params.term,
                // search term
                page: (_params$page = params.page) !== null && _params$page !== void 0 ? _params$page : 1
              };
            },
            processResults: function processResults(data, second) {
              if (data === null || data === undefined || data.error === true) {
                return {
                  results: [],
                  pagination: {
                    more: false
                  }
                };
              }

              var d = data.data.map(function (v) {
                return {
                  text: v.name,
                  id: v.name
                };
              });
              var pag = data.pagination;
              return {
                results: d,
                pagination: {
                  more: pag.currentPage < pag.totalPages
                }
              };
            }
          },
          language: {
            errorLoading: function errorLoading() {
              return coleteOnlineLanguage.streetSearch.errorLoading;
            },
            inputTooLong: function inputTooLong(args) {
              var remainingChars = args.minimum - args.input.length;
              var message = coleteOnlineLanguage.streetSearch.inputTooLong;
              message = message.replace("%d", remainingChars);
              return message;
            },
            inputTooShort: function inputTooShort(args) {
              var remainingChars = args.minimum - args.input.length;
              var message = coleteOnlineLanguage.streetSearch.inputTooShort;
              message = message.replace("%d", remainingChars);
              return message;
            },
            loadingMore: function loadingMore() {
              return coleteOnlineLanguage.streetSearch.loadingMore;
            },
            noResults: function noResults() {
              return coleteOnlineLanguage.streetSearch.noResults;
            },
            searching: function searching() {
              return coleteOnlineLanguage.streetSearch.searching;
            },
            removeAllItems: function removeAllItems() {
              return coleteOnlineLanguage.streetSearch.removeAllItems;
            },
            removeItem: function removeItem() {
              return coleteOnlineLanguage.streetSearch.removeItem;
            },
            search: function search() {
              return coleteOnlineLanguage.streetSearch.search;
            }
          },
          width: "100%",
          minimumInputLength: 2,
          selectOnClose: coleteOnlineOptions.autoSelectStreet === "yes",
          tags: true,
          insertTag: function insertTag(data, tag) {
            var found = false;
            var b = sanitizeStr(tag.text).toLowerCase();

            for (var i = 0; i < data.length; ++i) {
              var a = sanitizeStr(data[i].text);

              if ($.trim(a).toLowerCase() === b) {
                found = true;
              }
            }

            if (!found) {
              data.unshift(tag);
            }
          }
        });
        $("#".concat(type, "_street")).on("select2:select", function (ev) {
          searchPostalCode(type);
          $("#".concat(type, "_address_1")).val(ev.params.data.text);
        });
      }

      if (!coleteOnlineOptions.separateFields) {
        $("#".concat(type, "_city_state")).select2({
          ajax: {
            // url: ajaxUrl,
            url: optimizedAjax,
            dataType: 'json',
            delay: 300,
            data: function data(params) {
              var _params$page2;

              return {
                action: "coleteonline_autocomplete_city_state_merged",
                country: $("#".concat(type, "_country")).val(),
                q: params.term,
                // search term
                page: (_params$page2 = params.page) !== null && _params$page2 !== void 0 ? _params$page2 : 1
              };
            },
            processResults: function processResults(data, second) {
              if (data.error === true) {
                // setOption($(`#${type}_state`), "B");
                $("#".concat(type, "_state")).val('B');
                $("#".concat(type, "_city")).val(second.term);
                $("body").trigger('update_checkout');
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

              var results = [];

              var fn = function fn(v) {
                var val = "".concat(v.city, ", ").concat(v.county);
                return {
                  text: val,
                  id: val,
                  countyCode: v.countyCode,
                  cityName: v.city,
                  countyName: v.county
                };
              };

              for (var i = 0; i < data.data.length; ++i) {
                var d = data.data[i];

                if (d.list) {
                  results.push({
                    text: d.upLocality + ":",
                    children: d.list.map(fn)
                  });
                } else {
                  results.push(fn(d));
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
              return coleteOnlineLanguage.localityAndStateSearch.errorLoading;
            },
            inputTooLong: function inputTooLong(args) {
              var remainingChars = args.minimum - args.input.length;
              var message = coleteOnlineLanguage.localityAndStateSearch.inputTooLong;
              message = message.replace("%d", remainingChars);
              return message;
            },
            inputTooShort: function inputTooShort(args) {
              var remainingChars = args.minimum - args.input.length;
              var message = coleteOnlineLanguage.localityAndStateSearch.inputTooShort;
              message = message.replace("%d", remainingChars);
              return message;
            },
            loadingMore: function loadingMore() {
              return coleteOnlineLanguage.localityAndStateSearch.loadingMore;
            },
            noResults: function noResults() {
              return coleteOnlineLanguage.localityAndStateSearch.noResults;
            },
            searching: function searching() {
              return coleteOnlineLanguage.localityAndStateSearch.searching;
            },
            removeAllItems: function removeAllItems() {
              return coleteOnlineLanguage.localityAndStateSearch.removeAllItems;
            },
            removeItem: function removeItem() {
              return coleteOnlineLanguage.localityAndStateSearch.removeItem;
            },
            search: function search() {
              return coleteOnlineLanguage.localityAndStateSearch.search;
            }
          },
          width: '100%',
          minimumInputLength: 2,
          selectOnClose: coleteOnlineOptions.autoSelectCity === "yes"
        });
        $("#".concat(type, "_city_state")).on("select2:select", function (ev) {
          var _ev$params, _ev$params2;

          if (isSpecialCountyCode(ev.params.data.countyCode)) {
            var aux = ev.params.data.countyCode;
            ev.params.data.countyCode = "B";
            ev.params.data.cityName = aux;
          }

          $("#".concat(type, "_state")).val((_ev$params = ev.params) === null || _ev$params === void 0 ? void 0 : _ev$params.data.countyCode);
          $("#".concat(type, "_city")).val((_ev$params2 = ev.params) === null || _ev$params2 === void 0 ? void 0 : _ev$params2.data.cityName);

          if (ev.params.data.trigger !== false) {
            if (getStreetOp() === "displayWithAutocomplete") {
              $("#".concat(type, "_street")).val(null).trigger("change");
            }

            $("#".concat(type, "_address_1")).val("");
            searchPostalCode(type);
          } else {}

          $("body").trigger('update_checkout');
        });
      } else {
        var options = {
          ajax: {
            url: optimizedAjax,
            dataType: 'json',
            delay: 300,
            data: function data(params) {
              var _params$page3;

              if ($("#".concat(type, "_state")).val() === "B") {
                if (!params.term) {
                  params.term = "S";
                }
              }

              return {
                action: "coleteonline_autocomplete_city",
                country: $("#".concat(type, "_country")).val(),
                county_code: $("#".concat(type, "_state")).val(),
                q: params.term,
                // search term
                page: (_params$page3 = params.page) !== null && _params$page3 !== void 0 ? _params$page3 : 1
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

                if ($("#".concat(type, "_state")).val() === "B") {
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
          minimumInputLength: $("#".concat(type, "_state")).val() === "B" ? 0 : 2,
          selectOnClose: coleteOnlineOptions.autoSelectCity === "yes",
          templateSelection: function templateSelection(data) {
            if (data.id === '') {
              if ($("#".concat(type, "_state")).val() === "") {
                return shippingCityTranslations.emptyState;
              } else {
                return shippingCityTranslations.nonEmptyState;
              }
            }

            return data.text;
          }
        };

        if (!isWooCityField()) {
          $("#".concat(type, "_locality")).select2(options);

          if ($("#".concat(type, "_state")).val() === "") {
            $("#".concat(type, "_locality")).attr("disabled", "disabled");
          }

          setTimeout(function () {
            $("#".concat(type, "_state")).on('select2:select', function () {
              $("#".concat(type, "_locality")).val(null).trigger("change");

              if (getStreetOp() === "displayWithAutocomplete") {
                $("#".concat(type, "_street")).val(null).trigger("change");
              }

              $("#".concat(type, "_city")).val(null).trigger("change");

              if ($("#".concat(type, "_state")).val() === "") {
                $("#".concat(type, "_locality")).attr("disabled", "disabled");
                $("#".concat(type, "_locality")).trigger("change");
              } else {
                $("#".concat(type, "_locality")).removeAttr("disabled");
                $("#".concat(type, "_locality")).trigger("change");
              }

              if ($("#".concat(type, "_state")).val() === "B") {
                $("#".concat(type, "_locality")).select2(_objectSpread(_objectSpread({}, options), {}, {
                  minimumInputLength: 0
                }));
              } else {
                $("#".concat(type, "_locality")).select2(_objectSpread(_objectSpread({}, options), {}, {
                  minimumInputLength: 2
                }));
              }
            });
            $("#".concat(type, "_locality")).on('select2:select', function (ev) {
              if ($("#".concat(type, "_state")).val() === "B") {
                var _ev$params3;

                $("#".concat(type, "_city")).val((_ev$params3 = ev.params) === null || _ev$params3 === void 0 ? void 0 : _ev$params3.data.countyCode);
              } else {
                var _ev$params4;

                $("#".concat(type, "_city")).val((_ev$params4 = ev.params) === null || _ev$params4 === void 0 ? void 0 : _ev$params4.data.cityName);
              }

              if (getStreetOp() === "displayWithAutocomplete") {
                $("#".concat(type, "_street")).val(null).trigger("change");
              }

              searchPostalCode(type);
            });
          }, 500);
        }
      }
    }
  }

  function init() {
    $('form.checkout').on('change', 'input[name="payment_method"]', function () {
      $(document.body).trigger('update_checkout');
    });
    $('form.checkout').on('change', 'input[name="open_package"]', function () {
      $(document.body).trigger('update_checkout');
    });

    if (coleteOnlineOptions.checkoutFormType === "woocommerceDefault") {
      return;
    }

    if (coleteOnlineOptions.validatePhone) {
      $("#billing_phone").change(function () {
        checkPhone("billing");
      });
      checkPhone("billing");
      $("#shipping_phone").change(function () {
        checkPhone("shipping");
      });
      checkPhone("shipping");
    }

    $("#billing_country").on("select2:select select2:unselecting", function () {
      countryChangeHandler("billing", $("#billing_country").val());
    });
    countryChangeHandler("billing", $("#billing_country").val());
    $("#ship-to-different-address-checkbox").change(function (ev) {
      if ($("#ship-to-different-address-checkbox").val()) {
        countryChangeHandler("shipping", $("#shipping_country").val());
      }
    });
    countryChangeHandler("shipping", $("#shipping_country").val());
    $("#shipping_country").on("select2:select select2:unselecting", function () {
      countryChangeHandler("shipping", $("#shipping_country").val());
    });
  }

  if (!coleteOnlineOptions.isEditAddressPage) {
    var ran = false;
    $("body").on("updated_checkout", function () {
      if (!ran) {
        ran = true;
        init();
      }
    });
  } else {
    setTimeout(function () {
      init();
    }, 100);
  }

  $(document).on('select2:open', function () {
    setTimeout(function () {
      var el = document.querySelector('.select2-search__field');

      if (el) {
        el.focus();
      }
    }, 200);
  });
});