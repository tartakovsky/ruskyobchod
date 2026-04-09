jQuery(document).ready(function ($) {
  /**
   * Dismissable Notices
   * - Sends an AJAX request to mark the notice as dismissed
   */
  $('div.exactmetrics-notice').on('click', 'button.notice-dismiss', function (e) {
    e.preventDefault();
    $(this).closest('div.exactmetrics-notice').fadeOut();

    // If this is a dismissible notice, it means we need to send an AJAX request
    if ($(this).parent().hasClass('is-dismissible')) {
      $.post(
        exactmetrics_admin_common.ajax,
        {
          action: 'exactmetrics_ajax_dismiss_notice',
          nonce: exactmetrics_admin_common.dismiss_notice_nonce,
          notice: $(this).parent().data('notice')
        },
        function (response) {
        },
        'json'
      );
    }

  });

  /**
   * WP Consent Notice Dismiss
   * - Handles the dismissal of the WP Consent notice
   */
  $('#exactmetrics-wpconsent-notice-close').on('click', function (e) {
    e.preventDefault();

    var $notice = $('#exactmetrics-wpconsent-notice');

    // Fade out the notice immediately for better UX
    $notice.fadeOut();

    // Send AJAX request to dismiss the notice
    $.post(
      exactmetrics_admin_common.ajax,
      {
        action: 'exactmetrics_dismiss_wpconsent_notice',
        nonce: exactmetrics_admin_common.dismiss_notice_nonce
      },
      function () {},
      'json'
    );
  });

  $('div.wp-menu-name > .exactmetrics-menu-notification-indicator').on('click', function (event) {
    event.preventDefault();
    event.stopPropagation();

    location.href = exactmetrics.reports_url + '&open=exactmetrics_notification_sidebar';
  });

  // Persist dismissal of Ads addon installed notice for 30 days
  $('#exactmetrics-ads-addon-notice').on('click', 'button.notice-dismiss', function (e) {
    e.preventDefault();
    $.post(
      exactmetrics_admin_common.ajax,
      {
        action: 'exactmetrics_dismiss_ads_addon_notice',
        nonce: exactmetrics_admin_common.dismiss_notice_nonce
      },
      function () {},
      'json'
    );
  });
});

var submenu_item = document.querySelector('.exactmetrics-upgrade-submenu');
if (null !== submenu_item) {
  var anchorTag = submenu_item.parentNode;

  if ( anchorTag ) {
    anchorTag.setAttribute("target", "_blank");
    anchorTag.setAttribute("rel", "noopener");

    var li = anchorTag.parentNode;

    if (li) {
      li.classList.add('exactmetrics-submenu-highlight');
    }
  }
}

var automated_submenu_item = document.querySelector('.exactmetrics-automated-submenu');
if (null !== automated_submenu_item) {
  var anchorTag = automated_submenu_item.parentNode;

  if ( anchorTag ) {
    anchorTag.setAttribute("target", "_blank");
    anchorTag.setAttribute("rel", "noopener");
    anchorTag.setAttribute("style", "color:#1da867");
  }
}
