import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { chatIdSelector } from '../../../../../../Selectors/chat';
import {
  sendChatMessage,
  addAttachment,
  addUploadingFile,
  removeUploadingFile,
  markUploadingFileFailed
} from '../../../../../../Actions/chatActions';

@connect(state => ({
  chatId: chatIdSelector(state)
}))
export class DropZoneContainer extends React.Component {

  static propTypes = {
    instant:  PropTypes.bool,
    chatId:   PropTypes.number,
    dispatch: PropTypes.func,
    children: PropTypes.node
  };

  onUploadStarted = (event, data) => {
    const { dispatch } = this.props;
    data.files.forEach(file => dispatch(addUploadingFile(file)));
  };

  onUploadSuccess = (event, response) => {
    const attachments = response.result && response.result.data || [];
    const { dispatch, chatId, instant } = this.props;

    response.files.forEach(file => dispatch(removeUploadingFile(file)));
    attachments.forEach(attachment => dispatch(addAttachment(attachment)));

    if (instant) {
      dispatch(sendChatMessage(chatId, {
        message:     null,
        attachments: attachments.map(attachment => attachment.blob_auth_id)
      }));
    }
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
