"use strict";

(function ($) {
  'use strict';

  function getLabel(element) {
    var href = $(element).attr('href');
    var uniqueId = href.split("=")[1];
    $.get(ajaxurl, {
      action: "coleteonline_get_order_label",
      data: {
        uniqueId: uniqueId
      }
    }, function (response) {
      response = JSON.parse(response);
      var name = "awb_".concat(uniqueId, ".pdf");
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

      $('.coleteonline-file-download-loading').hide();
      $('.coleteonline-do-download-awb').show();
    });
  }

  $('.wc-action-button-download_label').click(function (ev) {
    getLabel(ev.target);
    return ev.preventDefault();
  });
})(jQuery);