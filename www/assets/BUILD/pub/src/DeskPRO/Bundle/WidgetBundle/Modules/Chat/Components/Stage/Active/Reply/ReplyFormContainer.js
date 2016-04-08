import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ReplyForm } from './ReplyForm';
import { ReopenChatContainer } from '../ReopenChatContainer';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';
import { sendUserTyping, sendChatMessage, removeAttachment } from '../../../../Actions/chatActions';
import {
  chatIdSelector,
  chatLoadedSelector,
  agentNameSelector,
  attachmentsSelector,
  attachedImagesCountSelector
} from '../../../../Selectors/chat';

@connect(state => ({
  chatLoaded:          chatLoadedSelector(state),
  chatId:              chatIdSelector(state),
  agentName:           agentNameSelector(state),
  attachments:         attachmentsSelector(state),
  attachedImagesCount: attachedImagesCountSelector(state)
}))
export class ReplyFormContainer extends React.Component {

  static propTypes = {
    dispatch:    PropTypes.func,
    chatLoaded:  PropTypes.bool,
    chatId:      PropTypes.number,
    attachments: PropTypes.object
  };

  onUserTyping = message => {
    const { dispatch, chatId } = this.props;
    const data = { partial_message: message };

    dispatch(sendUserTyping(chatId, data));
  };

  onSendMessage = message => {
    const { dispatch, chatId, attachments } = this.props;
    const data = {
      message:     replaceSmileCodes(message, true),
      attachments: attachments.map(attachment => attachment.get('blob_auth_id'))
    };

    dispatch(sendChatMessage(chatId, data));
  };

  onRemoveAttachment = attachment => {
    this.props.dispatch(removeAttachment(attachment));
  };

  render() {
    if (!this.props.chatLoaded) {
      return null;
    }

    return (
      <ReopenChatContainer>
        <ReplyForm onUserTyping={this.onUserTyping}
                   onSendMessage={this.onSendMessage}
                   onRemoveAttachment={this.onRemoveAttachment} {...this.props} />

      </ReopenChatContainer>
    );
  }
}
