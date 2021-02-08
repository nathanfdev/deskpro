import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

export class HelpcenterTicketResolve extends PageWidget {

  renderWidget() {
    const submitForm = document.getElementById('helpcenter-ticket-resolve-form');
    const button =  window.document.getElementById('helpcenter-ticket-resolve-submit');

    submitForm.addEventListener('submit', () => {
      button.disabled = true;
    });
  }

}
