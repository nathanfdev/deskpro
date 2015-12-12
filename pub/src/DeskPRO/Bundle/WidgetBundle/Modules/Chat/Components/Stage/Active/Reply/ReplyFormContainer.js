import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ReplyForm } from './ReplyForm';
import { ReopenChatContainer } from '../ReopenChatContainer';
import { addAttachment } from '../../../../Actions/chatActions';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';
import { sendChatMessage, removeAttachment } from '../../../../Actions/chatActions';
import {
  chatIdSelector,
  agentNameSelector,
  attachmentsSelector,
  attachedImagesSelector,
  attachedImagesCountSelector,
  attachedFilesSelector
} from '../../../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state),
  agentName: agentNameSelector(state),
  attachments: attachmentsSelector(state),
  attachedImages: attachedImagesSelector(state),
  attachedImagesCount: attachedImagesCountSelector(state),
  attachedFiles: attachedFilesSelector(state)
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

  onUploadedFile = (event, response) => {
    const attachments = response.result || [];
    attachments.forEach(attachment => this.props.dispatch(addAttachment(attachment)));
  };

  onRemoveFile = attachment => {
    this.props.dispatch(removeAttachment(attachment));
  };

  render() {
    return (
      <ReopenChatContainer>
        <ReplyForm onSendMessage={this.onSendMessage}
                   onUploadedFile={this.onUploadedFile}
                   onRemoveFile={this.onRemoveFile} {...this.props} />

      </ReopenChatContainer>
    );
  }
}
