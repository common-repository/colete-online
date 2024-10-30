"use strict";

function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

(function ($) {
  'use strict';

  function checkFieldsValidity(element) {
    var el = $(element);
    var len = el.find(".coleteonline-invalid").length;

    if (len > 0) {
      $(el).find(".coleteonline-do-fetch-services-list").prop("disabled", true);
    } else {
      $(el).find(".coleteonline-do-fetch-services-list").prop("disabled", false);
    }
  }

  function validateAmountField(el) {
    var tar = $(el.target);
    var val = +tar.val();

    if (isNaN(val) || val < 0) {
      $(tar).addClass('coleteonline-invalid');
    } else {
      $(tar).removeClass('coleteonline-invalid');
    }

    checkFieldsValidity();
  }

  function coleteonlineOrderGetExtraOptions(el) {
    var info = {
      repaymentAmount: +el.find("#coleteonline-repayment-amount").val(),
      insuranceAmount: +el.find("#coleteonline-insurance-amount").val(),
      openAtDelivery: el.find("#coleteonline-open-package").is(":checked")
    };
    return info;
  }

  function initOrder(element) {
    var _productTables$;

    var el = $(element);
    var productTables = el.find(".coleteonline-packages-table").ColeteOnlineProductTable();
    productTables === null || productTables === void 0 ? void 0 : (_productTables$ = productTables[0]) === null || _productTables$ === void 0 ? void 0 : _productTables$.subscribe('validityChanges', function () {
      checkFieldsValidity(el);
    });
    el.find("#coleteonline-repayment-amount, #coleteonline-insurance-amount").change(validateAmountField);
    el.find("#coleteonline-repayment-amount, #coleteonline-insurance-amount").trigger("change");
    el.find(".coleteonline-change-address").click(function () {
      el.find(".coleteonline-address-select-wrapper").show();
      $.post(ajaxurl, {
        action: "coleteonline_get_all_addresses"
      }, function (response) {
        response = JSON.parse(response);
        var addr = el.find("#coleteonline-address-select");
        var options = [];

        for (var i = 0; i < response.addresses.length; ++i) {
          var o = response.addresses[i];
          options.push(new Option(o.shortName + " - " + o.address, o.id, false, false));
        }

        addr.append(options);

        if (response.selected) {
          addr.val(response.selected);
        }

        $(addr).on("select2:select", function (ev) {
          var found = response.addresses.find(function (el) {
            return +el.id === +ev.params.data.id;
          });

          if (found === undefined) {
            return;
          }

          var json = found.addressObject;
          $(".coleteonline-address-short-name").html("<b>".concat(json.shortName, "</b>"));
          $(".coleteonline-address-name").text(json.contact.name);
          $(".coleteonline-address-company").text(json.contact.company ? json.contact.company : "");
          $(".coleteonline-address-phone").html("".concat(json.contact.phone, "\n                ").concat(json.contact.phone2 ? "<br> " + json.contact.phone2 : ""));
          $(".coleteonline-address-city-county").text("".concat(json.address.city, ", ").concat(json.address.county));
          $(".coleteonline-address-street-number").text("".concat(json.address.street, ", ").concat(json.address.number));
          $(".coleteonline-address-postal-country").text("".concat(json.address.postalCode, ", ").concat(json.address.countryCode));
          var data = [json.address.building, json.address.entrance, json.address.floor, json.address.intercom, json.address.entrance, json.address.apartment];
          var stringData = data.filter(function (d) {
            return d;
          }).join(", ");
          $(".coleteonline-address-other-data").text(stringData);
          $(".coleteonline-address-landmark").text(json.address.landmark ? json.address.landmark : "");
          $(".coleteonline-address-additional-info").text(json.address.additionalInfo ? json.address.additionalInfo : "");
          $(".coleteonline-address-table").attr("data-address-id", json.locationId);
        });
      });
    });

    function handleResponseError(response, element) {
      if (response.error === "ServerError" || response.error === "Error") {
        el.find(element).html("<div class=\"coleteonline-notice-error\">\n            ".concat(response.message, "\n          </div>"));
      }

      if (response.error === "BadRequestError") {
        var list = "";

        if (response.validationErrors) {
          list = "<ul>";

          var _iterator = _createForOfIteratorHelper(response.validationErrors),
              _step;

          try {
            for (_iterator.s(); !(_step = _iterator.n()).done;) {
              var error = _step.value;
              list += "<li>".concat(error.field, " - ").concat(error.message, "</li>");
            }
          } catch (err) {
            _iterator.e(err);
          } finally {
            _iterator.f();
          }

          list += "</ul>";
        }

        el.find(element).html("<div class=\"coleteonline-notice-error\">\n            ".concat(response.message, "\n            ").concat(list, "\n          </div>"));
      }
    }

    el.find('.coleteonline-do-create-courier-order').prop('disabled', true);
    el.find(".coleteonline-couriers-offers").hide();
    el.find('.coleteonline-offers-loading').hide();
    el.find(".coleteonline-do-fetch-services-list").click(function () {
      el.find(".coleteonline-show-offers-errors").empty();
      el.find(".coleteonline-show-order-errors").empty();
      el.find(".coleteonline-couriers-offers").show();
      el.find(".coleteonline-offers-loading").show();
      el.find(".coleteonline-do-fetch-services-list").hide();
      el.find(".coleteonline-couriers-offers").find('tbody').empty();
      var fromAddressId = el.find(".coleteonline-address-table")[0].dataset.addressId;
      $.post(ajaxurl, {
        action: "coleteonline_get_services_list",
        data: {
          orderId: $("#coleteonline_order_shipping_wrapper")[0].dataset.orderId,
          fromAddressId: fromAddressId,
          packages: productTables[0].getPackagesTotals(),
          packageType: $("#coleteonline-order-package-type").val(),
          extraOptions: coleteonlineOrderGetExtraOptions(el)
        }
      }, function (response) {
        var _el$find$0$dataset, _el$find$0$dataset2, _response;

        $('.coleteonline-offers-loading').hide();
        $(".coleteonline-do-fetch-services-list").show();
        response = JSON.parse(response);

        if (response.error) {
          handleResponseError(response, ".coleteonline-show-offers-errors");
          return;
        }

        var processedServices = [];
        var displayList = [];
        var domesticToAddressServices = [];
        var domesticToPointServices = [];
        var domesticFromPointServices = [];

        var _iterator2 = _createForOfIteratorHelper((_response = response) === null || _response === void 0 ? void 0 : _response.list),
            _step2;

        try {
          var _loop = function _loop() {
            var service = _step2.value;

            if (processedServices.includes(service.service.id)) {
              return "continue";
            }

            processedServices.push(service.service.id);

            if (service.service.shippingPoint) {
              service.service.shippingPoints = response.list.filter(function (s) {
                return service.service.id === s.service.id;
              }).map(function (s) {
                return {
                  shippingPoint: s.service.shippingPoint,
                  activationId: s.service.activationId
                };
              });
              domesticToPointServices.push(service);
              return "continue";
            }

            if (service.service.fixedLocationsFrom) {
              domesticFromPointServices.push(service);
              return "continue";
            }

            domesticToAddressServices.push(service);
          };

          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
            var _ret = _loop();

            if (_ret === "continue") continue;
          }
        } catch (err) {
          _iterator2.e(err);
        } finally {
          _iterator2.f();
        }

        console.log("display list", displayList);
        var selectedId = (_el$find$0$dataset = el.find(".coleteonline-couriers-offers")[0].dataset) === null || _el$find$0$dataset === void 0 ? void 0 : _el$find$0$dataset.selectedCourierId;
        var selectedShippingPointId = (_el$find$0$dataset2 = el.find(".coleteonline-couriers-offers")[0].dataset) === null || _el$find$0$dataset2 === void 0 ? void 0 : _el$find$0$dataset2.selectedShippingPointId;

        if (domesticToAddressServices.length) {
          el.find(".coleteonline-couriers-offers").find('tbody').append("\n              <tr class=\"bg-gray-100\">\n                <td colspan=\"4\" class=\"full-width text-center padding-bottom-1 border-bottom-black\">\n                  <b class=\"vertical-align-middle\">Address to address services</b>\n                </td>\n              </tr>\n            ");
        }

        for (var _i = 0, _domesticToAddressSer = domesticToAddressServices; _i < _domesticToAddressSer.length; _i++) {
          var offer = _domesticToAddressSer[_i];
          var offerHtml = "\n              <tr class=\"".concat(+selectedId === +offer.service.id ? 'coleteonline-selected-by-client' : '', "\">\n                <td>\n                  <input type=\"checkbox\"\n                    class=\"coleteonline-selected-courier\"\n                    data-courier-id=").concat(offer.service.id, "\n                    ").concat(+selectedId === +offer.service.id ? 'checked' : '', "\n                  >\n                </td>\n                <td>").concat(offer.service.courierName, "</td>\n                <td>").concat(offer.service.name, "</td>\n                <td><b>").concat(offer.price.total, " ron</b><br>\n                    (").concat(offer.price.noVat, " ron + TVA)\n                </td>\n              </tr>\n            ");
          el.find(".coleteonline-couriers-offers").find('tbody').append(offerHtml);
        }

        if (domesticToPointServices.length) {
          el.find(".coleteonline-couriers-offers").find('tbody').append("\n              <tr class=\"bg-gray-100\">\n                <td colspan=\"4\" class=\"full-width text-center padding-bottom-1 border-bottom-black\">\n                  <b class=\"vertical-align-middle\">Address to locker services</b>\n                </td>\n              </tr>\n            ");
        }

        for (var _i2 = 0, _domesticToPointServi = domesticToPointServices; _i2 < _domesticToPointServi.length; _i2++) {
          var _offer = _domesticToPointServi[_i2];

          var _offerHtml = "\n              <tr class=\"".concat(+selectedId === +_offer.service.id ? 'coleteonline-selected-by-client' : '', "\">\n                <td>\n                  <input type=\"checkbox\"\n                    class=\"coleteonline-selected-courier\"\n                    data-courier-id=").concat(_offer.service.id, "\n                    ").concat(+selectedId === +_offer.service.id ? 'checked' : '', "\n                  >\n                </td>\n                <td>").concat(_offer.service.courierName, "</td>\n                <td>").concat(_offer.service.name, "</td>\n                <td><b>").concat(_offer.price.total, " ron</b><br>\n                    (").concat(_offer.price.noVat, " ron + TVA)\n                </td>\n              </tr>\n            ");

          var shippingPointsOptions = "";

          var _iterator3 = _createForOfIteratorHelper(_offer.service.shippingPoints),
              _step3;

          try {
            for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
              var shippingPoint = _step3.value;
              shippingPointsOptions += "\n                <option ".concat(+shippingPoint.shippingPoint.id === +selectedShippingPointId ? 'selected' : '', " value=\"").concat(shippingPoint.shippingPoint.id, "\">\n                  ").concat(shippingPoint.shippingPoint.name, "\n                </option>\n              ");
            }
          } catch (err) {
            _iterator3.e(err);
          } finally {
            _iterator3.f();
          }

          _offerHtml += "\n              <tr>\n                <td colspan=\"4\">\n                  <select class=\"full-width to-sp-sel-service-id-".concat(_offer.service.id, "\">\n                    ").concat(shippingPointsOptions, "\n                  </select>\n                </td>\n              </tr>\n            ");
          el.find(".coleteonline-couriers-offers").find('tbody').append(_offerHtml);
        }

        if (domesticFromPointServices.length) {
          el.find(".coleteonline-couriers-offers").find('tbody').append("\n              <tr class=\"bg-gray-100\">\n                <td colspan=\"4\" class=\"full-width text-center padding-bottom-1 border-bottom-black\">\n                  <b class=\"vertical-align-middle\">Locker to address services</b>\n                </td>\n              </tr>\n            ");
        }

        for (var _i3 = 0, _domesticFromPointSer = domesticFromPointServices; _i3 < _domesticFromPointSer.length; _i3++) {
          var _offer2 = _domesticFromPointSer[_i3];

          var _offerHtml2 = "\n              <tr class=\"".concat(+selectedId === +_offer2.service.id ? 'coleteonline-selected-by-client' : '', "\">\n                <td>\n                  <input type=\"checkbox\"\n                    class=\"coleteonline-selected-courier\"\n                    data-courier-id=").concat(_offer2.service.id, "\n                    ").concat(+selectedId === +_offer2.service.id ? 'checked' : '', "\n                  >\n                </td>\n                <td>").concat(_offer2.service.courierName, "</td>\n                <td>").concat(_offer2.service.name, "</td>\n                <td><b>").concat(_offer2.price.total, " ron</b><br>\n                    (").concat(_offer2.price.noVat, " ron + TVA)\n                </td>\n              </tr>\n            ");

          var fixedLocationsFromOptions = "";

          var _iterator4 = _createForOfIteratorHelper(_offer2.service.fixedLocationsFrom),
              _step4;

          try {
            for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
              var fixedLocationFrom = _step4.value;
              fixedLocationsFromOptions += "\n                <option ".concat(+fixedLocationFrom.id === +selectedShippingPointId ? 'selected' : '', " value=\"").concat(fixedLocationFrom.id, "\">\n                  ").concat(fixedLocationFrom.name, "\n                </option>\n              ");
            }
          } catch (err) {
            _iterator4.e(err);
          } finally {
            _iterator4.f();
          }

          _offerHtml2 += "\n              <tr>\n                <td colspan=\"4\">\n                  <select class=\"full-width from-sp-sel-service-id-".concat(_offer2.service.id, "\"\">\n                    ").concat(fixedLocationsFromOptions, "\n                  </select>\n                </td>\n              </tr>\n            ");
          el.find(".coleteonline-couriers-offers").find('tbody').append(_offerHtml2);
        }

        if (el.find('.coleteonline-selected-courier:checked').length) {
          el.find('.coleteonline-do-create-courier-order').prop('disabled', false);
        }

        el.find('.coleteonline-selected-courier').unbind('change');
        el.find('.coleteonline-selected-courier').change(function (element) {
          el.find('.coleteonline-selected-courier').prop('checked', false);
          el.find(element.target).prop('checked', true);
          el.find('.coleteonline-do-create-courier-order').prop('disabled', false);
        });
      });
    });

    function initLabelDownloadListener(shadowed) {
      shadowed.find('.coleteonline-file-download-loading').hide();
      shadowed.find('.coleteonline-do-download-awb').click(function (element) {
        shadowed.find('.coleteonline-file-download-loading').show();
        shadowed.find('.coleteonline-do-download-awb').hide();
        $.get(ajaxurl, {
          action: "coleteonline_get_order_label",
          data: {
            uniqueId: element.target.dataset.uniqueId,
            formatType: shadowed.find("#coleteonline-format-select").val()
          }
        }, function (response) {
          response = JSON.parse(response);
          var name = "awb_".concat(element.target.dataset.awb, "_").concat(element.target.dataset.uniqueId, ".pdf");
          var len = response.data.length;
          var buffer = new ArrayBuffer(len);
          var view = new Uint8Array(buffer);
          view.set(response.data);
          var blob = new Blob([view], {
            type: "application/pdf"
          }); // IE 11

          if (window.navigator.msSaveOrOpenBlob) {
            window.navigator.msSaveOrOpenBlob(blob, name);
          } else {
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement("a");
            a.href = url;
            a.download = name;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
          }

          shadowed.find('.coleteonline-file-download-loading').hide();
          shadowed.find('.coleteonline-do-download-awb').show();
        });
      });
    }

    el.find('.coleteonline-orders-loading').hide();
    el.find('.coleteonline-do-create-courier-order').click(function () {
      var _el$find, _el$find2;

      el.find(".coleteonline-show-offers-errors").empty();
      el.find(".coleteonline-show-order-errors").empty();
      el.find('.coleteonline-orders-loading').show();
      el.find('.coleteonline-do-create-courier-order').hide();
      var fromAddressId = el.find(".coleteonline-address-table")[0].dataset.addressId;
      var courierId = el.find('.coleteonline-selected-courier:checked')[0].dataset.courierId;
      var shippingOrderKey = el[0].dataset.shippingOrderKey;
      var toShippingPoint = (_el$find = el.find('.to-sp-sel-service-id-' + courierId)) === null || _el$find === void 0 ? void 0 : _el$find.val();
      var fromShippingPoint = (_el$find2 = el.find('.from-sp-sel-service-id-' + courierId)) === null || _el$find2 === void 0 ? void 0 : _el$find2.val();
      $.post(ajaxurl, {
        action: "coleteonline_create_courier_order",
        data: {
          orderId: $("#coleteonline_order_shipping_wrapper")[0].dataset.orderId,
          fromAddressId: fromAddressId,
          shippingOrderKey: shippingOrderKey,
          packages: productTables[0].getPackagesTotals(),
          packageType: $("#coleteonline-order-package-type").val(),
          content: el.find(".coleteonline-order-content-input").val(),
          selectedCourierId: courierId,
          selectedToShippingPointId: toShippingPoint,
          selectedFromShippingPointId: fromShippingPoint,
          extraOptions: coleteonlineOrderGetExtraOptions(el)
        }
      }, function (response) {
        $('.coleteonline-orders-loading').hide();
        $(".coleteonline-do-create-courier-order").show();
        response = JSON.parse(response);

        if (response.error) {
          handleResponseError(response, ".coleteonline-show-order-errors");
        } else {
          var html = $(response.fragments.courierOrders);
          el.replaceWith(html);
          initLabelDownloadListener(html);
        }
      });
    });
    initLabelDownloadListener(el);
  }

  $(".order").each(function (key, element) {
    initOrder(element);
  });

  function initAddExtra() {
    $(".do-add-extra-order").click(function () {
      $.post(ajaxurl, {
        action: "coleteonline_add_extra_courier_order",
        data: {
          orderId: $("#coleteonline_order_shipping_wrapper")[0].dataset.orderId
        }
      }, function (response) {
        response = JSON.parse(response);

        if (response.error) {} else {
          $(".do-add-extra-order").parent().remove();
          var html = $(response.fragments.extraOrder);
          var order = html.find(".order");
          var addButton = html.find(".add-extra-orders");
          $("#coleteonline_order_shipping_wrapper").append(order);

          if (addButton.length) {
            $("#coleteonline_order_shipping_wrapper").append(addButton);
          }

          initOrder(order);
          initAddExtra();
        }
      });
    });
  }

  initAddExtra();
})(jQuery);