import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { DropZoneOverlay } from './DropZoneOverlay';
import { DragOverlayListener } from 'DeskPRO/Component/Uploader/DragOverlayListener';
import { PasteCatcher } from 'DeskPRO/Component/Uploader/PasteCatcher';
import 'blueimp-file-upload';
import $ from 'jquery';
import { extension } from 'mime-types';
import moment from 'moment';
import { getImageDataUrl, dataUrlToBlob } from 'DeskPRO/Component/Util/Blob';

export class DropZone extends React.Component {

  static propTypes = {
    getExternalInput: PropTypes.func.isRequired,
    uploadUrl: PropTypes.string.isRequired,
    context: PropTypes.any,
    repeatFiles: PropTypes.object,
    onSend: PropTypes.func,
    onSuccess: PropTypes.func,
    onFail: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      overlay: false
    };
  }

  componentDidMount() {
    this.initializeFileUpload();
    this.getContext().forEach(context => $(context).on('paste', this.onPaste));
  }

  componentWillReceiveProps(newProps) {
    if (newProps.repeatFiles.size) {
      newProps.repeatFiles.forEach(file => this.pushFileToQueue(file));
    }
  }

  componentWillUnmount() {
    $(this.getInput()).fileupload('destroy');
    this.getContext().forEach(context => $(context).off('paste', this.onPaste));
  }

  onGetBlobFromPasteChecker = () => {
    const $pasteCatcher = $(this.getPasteCatcher());
    const child = $pasteCatcher.children().last().get(0);

    if (child) {
      if (child.tagName === 'IMG') {
        const imgSrc = child.src;
        getImageDataUrl(imgSrc, dataUrl => {
          const blob = dataUrlToBlob(dataUrl);
          this.pushBlobToQueue(blob, 'image/png');
        });
      }
    }
  };

  onPaste = event => {
    const originalEvent = event.originalEvent;
    if (originalEvent.clipboardData) {
      const items = originalEvent.clipboardData.items;
      if (items) {
        for (var i = 0; i < items.length; i++) {
          const item = items[i];

          if (item.kind === 'file' && item.type.indexOf('image') !== -1) {
            const blob = item.getAsFile();
            this.pushBlobToQueue(blob, item.type);
          }
        }
      } else {
        const $pasteCatcher = $(this.getPasteCatcher());
        $pasteCatcher.focus();

        setTimeout(this.onGetBlobFromPasteChecker, 100);
      }
    }
  };

  getContext() {
    const { context = document } = this.props;
    return Array.isArray(context) ? context : [...context];
  }

  getInput() {
    return ReactDOM.findDOMNode(this.props.getExternalInput());
  }

  getPasteCatcher() {
    return ReactDOM.findDOMNode(this.refs.pasteCatcher);
  }

  initializeFileUpload() {
    const { uploadUrl, onSend, onSuccess, onFail } = this.props;
    const overlayNode = ReactDOM.findDOMNode(this);

    const $input = $(this.getInput());
    $input.fileupload({
      fileInput: $input,
      url: uploadUrl,
      dropZone: $(overlayNode),
      send: onSend,
      done: onSuccess,
      fail: onFail
    });
  }

  pushFileToQueue(file) {
    const $input = $(this.getInput());
    this.initializeFileUpload();

    $input.fileupload('send', {
      fileInput: $input,
      files: [file]
    });
  }

  pushBlobToQueue(blob, contentType) {
    const file = new File([blob], `clipboard_${moment().format()}.${extension(contentType)}`);
    this.pushFileToQueue(file);
  }

  render() {
    return (
      <div>
        <DragOverlayListener context={this.props.context}>
          <DropZoneOverlay />
        </DragOverlayListener>
        <PasteCatcher ref="pasteCatcher" />
      </div>
    );
  }
}
