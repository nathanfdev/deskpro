import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

export class CloseFlashWidget extends PageWidget {
  renderWidget() {
    this.$element.on('click', (e) => {
      console.log(e);
      this.$element.parents('.dp-po-message-bar').remove();
    });
  }
}
