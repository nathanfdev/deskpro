import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

export class HelpcenterComment extends PageWidget {

  renderWidget() {
    const submitForm = document.getElementById('helpcenter-comment-form');
    const button =  window.document.getElementById('helpcenter-comment-submit');

    submitForm.addEventListener('submit', () => {
      button.disabled = true;
    });
  }

}
