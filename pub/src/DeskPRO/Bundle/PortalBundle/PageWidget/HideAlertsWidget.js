import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import $ from "jquery"

export default class HideAlertsWidget extends PageWidget {
  renderWidget() {
    let $alerts = this.$element.find('.alert');

    let setCookie = function(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    };

    let getCookie = function(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1);
            if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
        }
        return "";
    };

    let appendDismissedAlert = function(alertId) {
      let cookie = getCookie('dismissed_ui_alerts'); // session cookie
      if (!cookie) {
        cookie = '';
      }

      let dismissed = cookie.split('#!#') || [];
      dismissed.push(alertId);

      setCookie('dismissed_ui_alerts', dismissed.join('#!#')); // append the alert ID so server can render it hidden
    };

    $alerts.each(function() {
      let $alert = $(this);
      let alertId = $(this).data('alert-id');
      $alert.find('i.click-to-dismiss').click(function() {
        $alert.hide();
        appendDismissedAlert(alertId);
      });
    });
  }
}
