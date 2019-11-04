import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { getFileIcon } from 'DeskPRO/Component/Util/Filename';

export class FileIcon extends PageWidget {

  renderWidget() {
    const contentType = this.$element.data('content-type');
    const noSpan = this.$element.data('no-span');
    const iconClass = getFileIcon(contentType);

    this.$element.prepend(`${noSpan ? '' : '<span class="dpx-file-icon">'}<i class="${iconClass}"></i>${noSpan ? '' : '</span>'}`);
  }
}
