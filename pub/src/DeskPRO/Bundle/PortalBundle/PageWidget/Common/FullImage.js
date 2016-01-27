import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { openFullImage } from 'DeskPRO/Component/Util/FullImage';

export class FullImage extends PageWidget {

  renderWidget() {
    const $image = this.$element.find('img');
    const imageNode = $image.get(0);

    if (!imageNode) {
      return;
    }

    this.$element.on('click', event => {
      event.preventDefault();
      openFullImage(imageNode);
    });
  }
}
