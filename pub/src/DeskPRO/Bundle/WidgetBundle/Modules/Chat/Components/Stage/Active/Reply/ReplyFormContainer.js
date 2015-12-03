import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { chatIdSelector, agentNameSelector } from '../../../../Selectors/chat';
import { sendChatMessage } from '../../../../Actions/chatActions';
import { ReplyForm } from './ReplyForm';
import { ReopenChatContainer } from '../ReopenChatContainer';

@connect(state => ({
  chatId: chatIdSelector(state),
  agentName: agentNameSelector(state)
}))
export class ReplyFormContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    chatId: PropTypes.number
  };

  onSendMessage = message => {
    const { dispatch, chatId } = this.props;
    const data = {
      message: message
    };

    dispatch(sendChatMessage(chatId, data));
  };

  render() {
    return (
      <ReopenChatContainer>
        <ReplyForm onSendMessage={this.onSendMessage} {...this.props} />
      </ReopenChatContainer>
    );
  }
}
