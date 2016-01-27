import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class HideAlertsWidget extends PageWidget {

  renderWidget() {
    const $alerts = this.$element.find('.alert');

    const setCookie = (cname, cvalue, exdays) => {
      var d = new Date();
      d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
      var expires = 'expires=' + d.toUTCString();
      document.cookie = cname + '=' + cvalue + '; ' + expires;
    };

    const getCookie = (cname) => {
      var name = cname + '=';
      var ca = document.cookie.split(';');
      for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') {
          c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
          return c.substring(name.length, c.length);
        }
      }

      return '';
    };

    const appendDismissedAlert = (alertId) => {
      let cookie = getCookie('dismissed_ui_alerts'); // session cookie
      if (!cookie) {
        cookie = '';
      }

      const dismissed = cookie.split('#!#') || [];
      dismissed.push(alertId);

      setCookie('dismissed_ui_alerts', dismissed.join('#!#')); // append the alert ID so server can render it hidden
    };

    $alerts.each(function() {
      const $alert = $(this);
      const alertId = $(this).data('alert-id');

      $alert.find('i.click-to-dismiss').click(() => {
        $alert.hide();
        appendDismissedAlert(alertId);
      });
    });
  }
}
