import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';
import TicketForm from 'DeskPRO/Bundle/PortalBundle/PageWidget/TicketForm';
import PortalFormWidget from 'DeskPRO/Bundle/PortalBundle/PageWidget/PortalFormWidget';

export class TicketFormWidget extends PageWidget {

  init() {
    this.addWidgetDef(TicketForm, '#new_ticket_page');
    this.addWidgetDef(PortalFormWidget, '.dpx-form');
  }
}
