import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { getFileIcon } from 'DeskPRO/Component/Util/Filename';

export class FileIcon extends PageWidget {

  renderWidget() {
    const contentType = this.$element.data('content-type');
    const iconClass = getFileIcon(contentType);

    this.$element.prepend(`<span><i class="${iconClass}"></i></span>`);
  }
}
