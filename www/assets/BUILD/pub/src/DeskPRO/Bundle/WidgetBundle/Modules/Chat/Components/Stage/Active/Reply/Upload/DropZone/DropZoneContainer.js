import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { uploadingFilesRepeatSelector } from '../../../../../../Selectors/chat';
import {
  addAttachment,
  addUploadingFile,
  removeUploadingFile,
  markUploadingFileFailed
} from '../../../../../../Actions/chatActions';

@connect(state => ({
  repeatFiles: uploadingFilesRepeatSelector(state)
}))
export class DropZoneContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.node
  };

  onUploadStarted = (event, data) => {
    const { dispatch } = this.props;
    data.files.forEach(file => dispatch(addUploadingFile(file)));
  };

  onUploadSuccess = (event, response) => {
    const attachments = response.result && response.result.data || [];
    const { dispatch } = this.props;

    response.files.forEach(file => dispatch(removeUploadingFile(file)));
    attachments.forEach(attachment => dispatch(addAttachment(attachment)));
  };

  onUploadFail = (event, data) => {
    data.files.forEach(file => this.props.dispatch(markUploadingFileFailed(file)));
  };

  render() {
    const { children } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      onSend:    this.onUploadStarted,
      onSuccess: this.onUploadSuccess,
      onFail:    this.onUploadFail
    });
  }
}
