import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
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
    const originalEvent = event.originalEvent;

    if (originalEvent.clipboardData) {
      const items = originalEvent.clipboardData.items;
      if (items) {
        this.getBlobsFromItems(items);
      }
    } else {
      const $pasteCatcher = $(this.getPasteCatcher());
      $pasteCatcher.children().remove();
      $pasteCatcher.focus();

      setTimeout(() => this.getBlobFromPasteChecker(), 1);
    }
  };

  getBlobFromPasteChecker = () => {
    this.getBlobsFromNode(this.getPasteCatcher());
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

  getBlobsFromNode = node => {
    const { onPasteImage } = this.props;

    $('img', node).each((i, child) => {
      const imgSrc = child.src;
      getImageDataUrl(imgSrc, dataUrl => {
        const blob = dataUrlToBlob(dataUrl);
        const urlObj = window.URL || window.webkitURL;
        const imgUrl = urlObj.createObjectURL(blob);

        onPasteImage(PasteCatcher.createFile(blob, 'image/png'), imgUrl, 'image/png');
      });
    });
  };

  getPasteCatcher() {
    return ReactDOM.findDOMNode(this.refs.pasteCatcher);
  }

  static createFile(blob, contentType) {
    return new File([blob], `clipboard_${moment().format()}.${extension(contentType)}`);
  }

  render() {
    return (
      <div className="paste-catcher"
           ref="pasteCatcher"
           contentEditable="true"
           style={{
             position: 'absolute',
             left: -999,
             width: 0,
             height: 0,
             overflow: 'hidden',
             outline: 0
           }} />
    );
  }
}
