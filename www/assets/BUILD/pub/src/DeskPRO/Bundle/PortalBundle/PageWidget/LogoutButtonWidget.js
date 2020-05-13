import React from 'react';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class LogoutButtonWidget extends PageWidget {

  renderWidget() {
    const $logoutBtn = this.$element;
    $logoutBtn.click(() => confirm(portalPhrases.get('helpcenter.account.logout_confirm')));
  }
}
