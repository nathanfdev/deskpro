import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ReplyForm } from './ReplyForm';
import { ReopenChatContainer } from '../ReopenChatContainer';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';
import { sendChatMessage, removeAttachment } from '../../../../Actions/chatActions';
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

  onRemoveAttachment = attachment => {
    this.props.dispatch(removeAttachment(attachment));
  };

  render() {
    return (
      <ReopenChatContainer>
        <ReplyForm onSendMessage={this.onSendMessage}
                   onRemoveAttachment={this.onRemoveAttachment} {...this.props} />

      </ReopenChatContainer>
    );
  }
}
