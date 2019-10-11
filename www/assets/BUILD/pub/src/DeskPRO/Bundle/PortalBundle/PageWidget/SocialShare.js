import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { copyTextToClipboard } from 'DeskPRO/Component/Util/ClipBoard';

export class SocialShare extends PageWidget {
  init() {
    this.copyButton = window.document.getElementById('share-copy-url');
    this.url = window.document.getElementById('item-url').value;
    this.copyButton.onclick = (ev) => {
      ev.stopPropagation();
      copyTextToClipboard(this.url);
    };
  }
}
