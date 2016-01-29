import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import 'blueimp-file-upload';
import $ from 'jquery';

export class DropZone extends React.Component {

  static propTypes = {
    getExternalInput: PropTypes.func.isRequired,
    uploadUrl: PropTypes.string.isRequired,
    uploadParams: PropTypes.object,
    repeatFiles: PropTypes.object,
    onSubmit: PropTypes.func,
    onSend: PropTypes.func,
    onSuccess: PropTypes.func,
    onFail: PropTypes.func,
    children: PropTypes.node
  };

  componentDidMount() {
    this.initializeFileUpload();
  }

  componentWillReceiveProps(newProps) {
    if (newProps.repeatFiles && newProps.repeatFiles.size) {
      newProps.repeatFiles.forEach(file => this.pushFileToQueue(file));
    }
  }

  componentWillUnmount() {
    $(this.getInput()).fileupload('destroy');
  }

  getInput() {
    return ReactDOM.findDOMNode(this.props.getExternalInput());
  }

  initializeFileUpload() {
    const { uploadUrl, uploadParams } = this.props;
    const { onSubmit, onSend, onSuccess, onFail } = this.props;
    const overlayNode = ReactDOM.findDOMNode(this);

    const $input = $(this.getInput());
    $input.fileupload({
      fileInput: $input,
      url: uploadUrl,
      formData: uploadParams,
      dropZone: $(overlayNode),
      submit: onSubmit,
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

  render() {
    return <div>{this.props.children}</div>;
  }
}
