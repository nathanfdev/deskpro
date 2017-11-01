import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';
import 'blueimp-file-upload';
import { AbstractFileUpload } from './AbstractFileUpload';

export default class DropZone extends AbstractFileUpload {

  static propTypes = {
    getExternalInput: PropTypes.func.isRequired,
    getDropZoneNode:  PropTypes.func,
    children:         PropTypes.node
  };

  getInput() {
    return this.props.getExternalInput();
  }

  initializeFileUpload() {
    const { uploadUrl, uploadParams } = this.props;
    const { onSubmit, onSend, onSuccess, onFail, getDropZoneNode } = this.props;
    const overlayNode = getDropZoneNode ? getDropZoneNode() : this.node;

    const $input = $(this.getInput());
    $input.fileupload({
      fileInput: $input,
      url:       uploadUrl,
      formData:  uploadParams,
      dropZone:  $(overlayNode),
      submit:    onSubmit,
      send:      onSend,
      done:      onSuccess,
      fail:      onFail
    });
  }

  render() {
    return <div ref={(node) => { this.node = node; }}>{this.props.children}</div>;
  }
}
