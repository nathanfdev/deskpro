import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { DropZoneOverlay } from './DropZoneOverlay';
import fileupload from 'blueimp-file-upload';
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
    this.getContext().forEach(context => {
      $(context).on('dragover', this.onDragStarted);
      $(context).on('dragover', this.onDefaultDrop);
      $(context).on('paste', this.onPaste);
    });

    // for Firefox
    this.$pasteCatcher = $('<div/>')
      .attr('contenteditable', 'true')
      .css({
        position: 'absolute',
        left: -999,
        width: 0,
        height: 0,
        overflow: 'hidden',
        outline: 0
      });

    $(window.widgetFrame.document.body).prepend(this.$pasteCatcher);
  }

  componentWillReceiveProps(newProps) {
    if (newProps.repeatFiles.size) {
      newProps.repeatFiles.forEach(file => this.pushFileToQueue(file));
    }
  }

  componentWillUnmount() {
    $(this.getInput()).fileupload('destroy');
    this.$pasteCatcher.remove();

    this.getContext().forEach(context => {
      $(context).off('dragover', this.onDragStarted);
      $(context).off('dragover', this.onDefaultDrop);
      $(context).off('paste', this.onPaste);
    });
  }

  onDefaultDrop = event => {
    event.preventDefault();
  };

  onDragStarted = () => {
    if (!this.timeout) {
      this.setState({
        overlay: true
      });
    } else {
      clearTimeout(this.timeout);
    }

    this.timeout = setTimeout(this.onDragEnd, 100);
  };

  onDragEnd = () => {
    this.timeout = null;
    this.setState({
      overlay: false
    });
  };

  onGetBlobFromPasteChecker = () => {
    const child = this.$pasteCatcher.children().last().get(0);
    if (child) {
      if (child.tagName === 'IMG') {
        const imgSrc = child.src;
        getImageDataUrl(imgSrc, dataUrl => {
          const blob = dataUrlToBlob(dataUrl);
          this.pushBlobToQueue(blob, 'image/png');
        });
      }

      //this.$pasteCatcher.html('');
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
        this.$pasteCatcher.focus();
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
    return <DropZoneOverlay opened={this.state.overlay} />;
  }
}
