import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';

export class AbstractFileUpload extends React.Component {

  static propTypes = {
    uploadUrl:    PropTypes.string,
    uploadParams: PropTypes.object,
    onSubmit:     PropTypes.func,
    onSend:       PropTypes.func,
    onSuccess:    PropTypes.func,
    onFail:       PropTypes.func
  };

  componentDidMount() {
    this.initializeFileUpload();
  }

  componentWillUnmount() {
    try {
      $(this.getInput()).fileupload('destroy');
    } catch (e) {
      console.warn('unable to destroy fileupload');
    }
  }

  pushFileToQueue(file) {
    const $input = $(this.getInput());
    this.initializeFileUpload();

    $input.fileupload('send', {
      files: [file]
    });
  }
}
