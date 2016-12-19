import React from 'react';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class LogoutButtonWidget extends PageWidget {

  renderWidget() {
    let $logoutBtn = this.$element;
    $logoutBtn.click(function () {
      return confirm(portalPhrases.get('portal.account.logout-confirm'));
    });
  }
}
