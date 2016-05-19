import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { AbstractFileUpload } from './AbstractFileUpload';
import $ from 'jquery';
import 'blueimp-file-upload';

export class UploadButton extends AbstractFileUpload {

  static propTypes = {
    name:      PropTypes.string,
    className: PropTypes.string,
    multiple:  PropTypes.bool
  };

  initializeFileUpload() {
    const { uploadUrl, uploadParams } = this.props;
    const { onSubmit, onSend, onSuccess, onFail } = this.props;

    const $input = $(this.getInput());
    $input.fileupload({
      fileInput: $input,
      url:       uploadUrl,
      formData:  uploadParams,
      submit:    onSubmit,
      send:      onSend,
      done:      onSuccess,
      fail:      onFail
    });
  }

  getInput() {
    return ReactDOM.findDOMNode(this.refs.input);
  }

  render() {
    const { name, className, multiple } = this.props;
    const inputName = multiple ? `${name}[]` : name;

    return (
      <input
        ref="input"
        className={className}
        type="file"
        name={inputName}
        multiple={multiple ? 'multiple' : null}
      />
    );
  }
}
