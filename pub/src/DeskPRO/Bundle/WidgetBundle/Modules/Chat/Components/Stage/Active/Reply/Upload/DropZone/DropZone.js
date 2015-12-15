import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { DropZoneOverlay } from './DropZoneOverlay';
import fileupload from 'blueimp-file-upload';
import $ from 'jquery';

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
    this.getContext().forEach(selector => {
      $(selector).on('dragover', this.onDragStarted);
      $(selector).on('dragover', this.onDefaultDrop);
    });
  }

  componentWillReceiveProps(newProps) {
    if (newProps.repeatFiles.size) {
      const $input = $(this.getInput());
      this.initializeFileUpload();

      newProps.repeatFiles.forEach(file => $input.fileupload('send', {fileInput: $input, files: [file]}));
    }
  }

  componentWillUnmount() {
    $(this.getInput()).fileupload('destroy');

    this.getContext().forEach(selector => {
      $(selector).off('dragover', this.onDragStarted);
      $(selector).off('dragover', this.onDefaultDrop);
    });
  }

  onDefaultDrop = e => {
    e.preventDefault();
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

  render() {
    return <DropZoneOverlay opened={this.state.overlay} />;
  }
}
