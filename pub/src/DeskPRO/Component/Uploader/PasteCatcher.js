import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { getImageDataUrl, dataUrlToBlob } from 'DeskPRO/Component/Util/Blob';
import $ from 'jquery';

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
    const { onPasteImage } = this.props;
    const originalEvent = event.originalEvent;

    if (originalEvent.clipboardData) {
      const items = originalEvent.clipboardData.items;
      if (items) {
        for (var i = 0; i < items.length; i++) {
          const item = items[i];

          if (item.kind === 'file' && item.type.indexOf('image') !== -1) {
            const blob = item.getAsFile();
            const urlObj = window.URL || window.webkitURL;
            const imgUrl = urlObj.createObjectURL(blob);

            onPasteImage(blob, imgUrl, item.type);
          }
        }
      } else {
        const $pasteCatcher = $(this.getPasteCatcher());
        $pasteCatcher.focus();

        setTimeout(() => this.getBlobFromPasteChecker(), 100);
      }
    }
  };

  getBlobFromPasteChecker = () => {
    const { onPasteImage } = this.props;
    const $pasteCatcher = $(this.getPasteCatcher());
    const child = $pasteCatcher.children().last().get(0);

    if (child) {
      if (child.tagName === 'IMG') {
        const imgSrc = child.src;

        getImageDataUrl(imgSrc, dataUrl => {
          const blob = dataUrlToBlob(dataUrl);
          const urlObj = window.URL || window.webkitURL;
          const imgUrl = urlObj.createObjectURL(blob);

          onPasteImage(blob, imgUrl, 'image/png');
        });
      }
    }
  };

  getPasteCatcher() {
    return ReactDOM.findDOMNode(this.refs.pasteCatcher);
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
