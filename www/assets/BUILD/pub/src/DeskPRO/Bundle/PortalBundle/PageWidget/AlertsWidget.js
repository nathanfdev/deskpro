import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';
import store from 'store';

/**
 * Alerts are notices that the server outputs (see CommonController::alertsAction and alerts.html.twig)
 *
 * We show or hide alert rows based on which alerts the user has dismissed in their browser.
 *
 * An alert has two properties:
 * - An alertId -- typically a URL of the research we're alerting about
 * - An alertVersion -- an integer. either explicitly set (such as a unix timestamp), or 1 by default.
 *
 * Dismissing an alert sets a localStorage var so the next time the page loads, we dont
 * re-render the same alerts.
 */
export class AlertsWidget extends PageWidget {

  static DISMISSED_STORAGE_ID = 'dp_dismissed_alerts';

  init() {
    this.currentDismissed = store.get(AlertsWidget.DISMISSED_STORAGE_ID) || {};
  }

  /**
   * Checks if an alert is dismissed yet
   *
   * @param {String} alertId
   * @param {Integer} alertVersion
   * @returns {boolean}
     */
  isDismissedAlert(alertId, alertVersion = 1) {
    return typeof this.currentDismissed[alertId] != 'undefined'
      && this.currentDismissed[alertId] >= alertVersion;
  }

  /**
   * Dismiss an alert.
   *
   * @param {String} alertId
   * @param {Integer} alertVersion
     */
  persistDismissAlert(alertId, alertVersion = 1) {
    this.currentDismissed[alertId] = alertVersion;
    store.set(AlertsWidget.DISMISSED_STORAGE_ID, this.currentDismissed);
  }

  /**
   * Pages can define a DP_DISMISS_ALERTS var to dismiss
   * an alert upon view. E.g. use this on target pages so simply
   * viewing a page implicitly dismisses the alert.
   */
  preDismissAlerts() {
    if (typeof window.DP_DISMISS_ALERTS == 'undefined') {
      return;
    }

    window.DP_DISMISS_ALERTS.forEach(a => {
      this.persistDismissAlert(a.alertId, a.alertVersion || 1);
    });
  }

  /**
   * Renders the alerts widget by checking the alerts
   * the server thinks we should show and hides the ones
   * the user has dismissed already.
   */
  renderWidget() {
    this.preDismissAlerts();
    const $alerts = this.$element.find('.alert');

    let isAny = false;
    const self = this;

    $alerts.each(function () {
      const $alert       = $(this);
      const alertId      = $alert.data('alert-id');
      const alertVersion = parseInt($alert.data('alert-version'), 10) || 0;

      if (self.isDismissedAlert(alertId, alertVersion)) {
        $alert.addClass('is-dismissed').hide();
      } else {
        isAny = true;
      }

      $alert.find('.click-to-dismiss').click(() => {
        $alert.addClass('is-dismissed').hide();

        self.persistDismissAlert(alertId, alertVersion);

        const link = $alert.data('dismiss-handler');
        if (link) {
          window.location.href = link;
        }
      });
    });

    // There is at least one, so we should show the container
    if (isAny) {
      this.$element.show();
    }
  }
}
