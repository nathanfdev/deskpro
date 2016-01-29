import React, { PropTypes } from 'react';
import { getImageDataUrl, dataUrlToBlob } from 'DeskPRO/Component/Util/Blob';
import $ from 'jquery';
import { extension } from 'mime-types';
import moment from 'moment';

export class PasteCatcher extends React.Component {

  static propTypes = {
    context: PropTypes.object,
    onPasteImage: PropTypes.func
  };

  componentDidMount() {
    const { context = document } = this.props;
    $(context).on('paste', this.onPaste);
  }

  componentWillUnmount() {
    const { context = document } = this.props;
    $(context).off('paste', this.onPaste);
  }

  onPaste = event => {
    const clipboardData = event.originalEvent.clipboardData;
    if (!clipboardData) {
      return;
    }

    const items = clipboardData.items;
    if (items) {
      this.getBlobsFromItems(items);
    } else {
      this.getBlobsFromHtml(clipboardData.getData('text/html'));
    }
  };

  getBlobsFromItems = items => {
    const { onPasteImage } = this.props;
    if (!items) {
      return;
    }

    for (var i = 0; i < items.length; i++) {
      const item = items[i];

      if (item.kind === 'file' && item.type.indexOf('image') !== -1) {
        const blob = item.getAsFile();
        const urlObj = window.URL || window.webkitURL;
        const imgUrl = urlObj.createObjectURL(blob);

        onPasteImage(PasteCatcher.createFile(blob, item.type), imgUrl, item.type);
      }
    }
  };

  getBlobsFromHtml = html => {
    const { onPasteImage } = this.props;
    const regex = /<img.*?src=['"](.*?)['"].*?>/;
    const callback = dataUrl => {
      const blob = dataUrlToBlob(dataUrl);
      onPasteImage(PasteCatcher.createFile(blob, 'image/png'), dataUrl, 'image/png');
    };

    const match = regex.exec(html);
    if (match) {
      getImageDataUrl(match[1], callback);
    }
  };

  static createFile(blob, contentType) {
    return new File([blob], `clipboard_${moment().format()}.${extension(contentType)}`);
  }

  render() {
    return null;
  }
}
