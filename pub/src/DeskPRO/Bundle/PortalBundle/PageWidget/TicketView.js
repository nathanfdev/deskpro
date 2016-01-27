import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class TicketView extends PageWidget {

  renderWidget() {
    $('.button-reply').on('click', (ev) => {
      ev.preventDefault();
      $('.reply-form').slideToggle();
    });
  }
}
