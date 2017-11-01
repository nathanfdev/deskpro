import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';
import 'blueimp-file-upload';
import { AbstractFileUpload } from './AbstractFileUpload';

export class UploadButton extends AbstractFileUpload {

  static propTypes = {
    name:            PropTypes.string,
    className:       PropTypes.string,
    multiple:        PropTypes.bool,
    acceptFileTypes: PropTypes.func
  };

  initializeFileUpload() {
    const { uploadUrl, uploadParams, acceptFileTypes } = this.props;
    const { onSubmit, onSend, onSuccess, onFail } = this.props;

    const $input = $(this.input);
    $input.fileupload({
      acceptFileTypes,

      fileInput: $input,
      url:       uploadUrl,
      formData:  uploadParams,
      submit:    onSubmit,
      send:      onSend,
      done:      onSuccess,
      fail:      onFail
    });
  }

  /**
   * BC. use this.input instead.
   *
   * @returns {input}
   */
  getInput() {
    return this.input;
  }

  render() {
    const { name, className, multiple } = this.props;
    const inputName = multiple ? `${name}[]` : name;

    return (
      <input
        ref={(c) => { this.input = c; }}
        className={className}
        type="file"
        name={inputName}
        multiple={multiple ? 'multiple' : null}
      />
    );
  }
}
