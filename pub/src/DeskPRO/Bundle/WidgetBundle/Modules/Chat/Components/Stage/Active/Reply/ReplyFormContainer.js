import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ReplyForm } from './ReplyForm';
import { ReopenChatContainer } from '../ReopenChatContainer';
import { addAttachment } from '../../../../Actions/chatActions';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';
import { sendChatMessage, removeAttachment, addUploadingFile, removeUploadingFile } from '../../../../Actions/chatActions';
import {
  chatIdSelector,
  agentNameSelector,
  attachmentsSelector,
  attachedImagesSelector,
  attachedImagesCountSelector,
  attachedFilesSelector,
  uploadingFilesSelector
} from '../../../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state),
  agentName: agentNameSelector(state),
  attachments: attachmentsSelector(state),
  attachedImages: attachedImagesSelector(state),
  attachedImagesCount: attachedImagesCountSelector(state),
  attachedFiles: attachedFilesSelector(state),
  uploadingFiles: uploadingFilesSelector(state)
}))
export class ReplyFormContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    chatId: PropTypes.number,
    attachments: PropTypes.object
  };

  onSendMessage = message => {
    const { dispatch, chatId, attachments } = this.props;
    const data = {
      message: replaceSmileCodes(message, true),
      attachments: attachments.map(attachment => attachment.get('blob_auth_id'))
    };

    dispatch(sendChatMessage(chatId, data));
  };

  onUploadedStarted = (event, data) => {
    data.files.forEach(file => this.props.dispatch(addUploadingFile(file)));
  };

  onUploadedSuccess = (event, response) => {
    const attachments = response.result && response.result.data || [];
    attachments.forEach(attachment => this.props.dispatch(addAttachment(attachment)));

    this.removeFilesFromQueue(response);
  };

  onUploadedFail = (event, data) => {
    this.removeFilesFromQueue(data);
  };

  onRemoveFile = attachment => {
    this.props.dispatch(removeAttachment(attachment));
  };

  removeFilesFromQueue(data) {
    data.files.forEach(file => this.props.dispatch(removeUploadingFile(file)));
  }

  render() {
    return (
      <ReopenChatContainer>
        <ReplyForm onSendMessage={this.onSendMessage}
                   onUploadedStarted={this.onUploadedStarted}
                   onUploadedSuccess={this.onUploadedSuccess}
                   onUploadedFail={this.onUploadedFail}
                   onRemoveFile={this.onRemoveFile} {...this.props} />

      </ReopenChatContainer>
    );
  }
}
