import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { chatIdSelector, agentNameSelector, attachmentsSelector } from '../../../../Selectors/chat';
import { sendChatMessage } from '../../../../Actions/chatActions';
import { ReplyForm } from './ReplyForm';
import { ReopenChatContainer } from '../ReopenChatContainer';
import { addAttachment } from '../../../../Actions/chatActions';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';

@connect(state => ({
  chatId: chatIdSelector(state),
  agentName: agentNameSelector(state),
  attachments: attachmentsSelector(state)
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
    console.log('onRemoveFile', attachment);
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
