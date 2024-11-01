(function () {
'use strict';

/**
 * TODO: Phasing out jQuery.
 *
 * Make the code less reliant on jQuery.
 */
jQuery(function ($) {
  if ($('body').hasClass('wp-admin')) {
    return;
  }
  /**
   * Display the notice on and off.
   *
   * @param noticeElem
   * @param noticeStatus
   * @param noticeMessage
   */


  var toggelNotice = function toggelNotice(noticeElem, noticeStatus, noticeMessage) {
    var currentStatus;
    var className = "wp-chimp-subscription-form__notice--".concat(noticeStatus);
    currentStatus = noticeElem.data('current-status');

    if (currentStatus) {
      noticeElem.removeClass(currentStatus);
    }

    if (className && noticeMessage) {
      noticeElem.text(noticeMessage).addClass("".concat(className, " is-displayed")).data('current-status', className);
    }
  };

  $('body').on('submit', '.wp-chimp-form', function (event) {
    event.preventDefault();
    var form = $(event.currentTarget);
    var formData = form.serializeArray();
    var formParent = form.parents('.wp-chimp-subscription-form');
    var formNotice = formParent.children('.wp-chimp-subscription-form__notice');
    var formFieldSet = form.children('.wp-chimp-form__fieldset');
    var formButton = formFieldSet.children('.wp-chimp-form__button');
    var apiUrl = form.attr('action');
    $.ajax({
      type: 'POST',
      url: apiUrl,
      data: formData,
      beforeSend: function beforeSend() {
        formParent.addClass('is-submitting').fadeTo(200, 0.5, function () {
          formFieldSet.prop('disabled', true);
          formButton.prop('disabled', true);
        });
      }
    }).always(function () {
      formParent.removeClass('is-submitting').fadeTo(200, 1, function () {
        formFieldSet.prop('disabled', false);
        formButton.prop('disabled', false);
      });
    }).done(function (response) {
      var notice = response.notice || {};
      toggelNotice(formNotice, notice.type, notice.message);
    }).fail(function (xhr) {
      var response = xhr.responseJSON;
      var noticeMessage = response.message;
      var noticeStatus = 'error';
      toggelNotice(formNotice, noticeStatus, noticeMessage);
    });
  });
});

}());
