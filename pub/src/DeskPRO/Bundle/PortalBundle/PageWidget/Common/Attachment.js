import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import FileIcon from './FileIcon';
import FullImage from './FullImage';

export default class Attachment extends PageWidget {

  init() {
    this.addWidgetDef(FileIcon, '.dpx-attachment-file-icon');
    this.addWidgetDef(FullImage, '.dpx-attachment-full-image');
  }
}
