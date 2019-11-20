import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { pageWidgetEmitter } from 'DeskPRO/Component/PageWidget/PageWidgetEmitter';

const deps = import('../../../../page-widget-deps');

export class TicketFormWidget extends PageWidget {
  init() {
    return deps.then(({TicketForm, PortalFormWidget}) => {
      this.addWidgetDef(TicketForm, '#new_ticket_page');
      this.addWidgetDef(PortalFormWidget, '.dpx-form');

      pageWidgetEmitter.on('refresh', () => this.refresh(this.$element));
    });
  }
}
