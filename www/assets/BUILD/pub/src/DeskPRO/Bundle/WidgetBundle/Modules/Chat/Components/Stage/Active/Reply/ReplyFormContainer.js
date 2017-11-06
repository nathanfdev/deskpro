import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';
import { ReplyForm } from './ReplyForm';
import { ReopenChatContainer } from '../ReopenChatContainer';
import { primaryColorSelector } from '../../../../../Application/Selectors/dpWindow';
import {
  sendUserTyping,
  sendChatMessage,
  removeAttachment,
  savePartialTyping
} from '../../../../Actions/chatActions';
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
  attachedImagesCount: attachedImagesCountSelector(state),
  primaryColor:        primaryColorSelector(state)
}))
export class ReplyFormContainer extends React.Component {

  static propTypes = {
    dispatch:    PropTypes.func,
    chatLoaded:  PropTypes.bool,
    chatId:      PropTypes.string,
    attachments: PropTypes.object
  };

  onUserTyping = (message) => {
    const { dispatch, chatId } = this.props;
    const data = { partial_message: message };

    dispatch(savePartialTyping(message));
    dispatch(sendUserTyping(chatId, data));
  };

  onSendMessage = (message) => {
    const { dispatch, chatId, attachments } = this.props;
    const data = {
      message:     replaceSmileCodes(message, true),
      attachments: attachments.map(attachment => attachment.get('blob_auth_id'))
    };

    dispatch(savePartialTyping(''));
    dispatch(sendChatMessage(chatId, data));
  };

  onRemoveAttachment = (attachment) => {
    this.props.dispatch(removeAttachment(attachment));
  };

  render() {
    if (!this.props.chatLoaded) {
      return null;
    }

    return (
      <ReopenChatContainer>
        <ReplyForm
          onUserTyping={this.onUserTyping}
          onSendMessage={this.onSendMessage}
          onRemoveAttachment={this.onRemoveAttachment}
          {...this.props}
        />
      </ReopenChatContainer>
    );
  }
}
