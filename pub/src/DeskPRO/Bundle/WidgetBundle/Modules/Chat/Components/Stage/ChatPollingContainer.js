import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { pollingChat } from '../../Actions/chatActions';
import history from '../../../../Services/history';
import moment from 'moment';
import {
  chatIdSelector,
  agentIdSelector,
  lastMessageIdSelector,
  transcriptCheckedSelector,
  transcriptSentSelector,
  authorEmailSelector,
  isEndedSelector
} from '../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state),
  agentId: agentIdSelector(state),
  lastMessageId: lastMessageIdSelector(state),
  authorEmail: authorEmailSelector(state),
  transcriptChecked: transcriptCheckedSelector(state),
  transcriptSent: transcriptSentSelector(state),
  isEnded: isEndedSelector(state)
}))
export class ChatPollingContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    chatId: PropTypes.number,
    agentId: PropTypes.number,
    lastMessageId: PropTypes.any,
    children: PropTypes.node,
    authorEmail: PropTypes.string,
    transcriptSent: PropTypes.bool,
    isEnded: PropTypes.bool
  };

  componentDidMount() {
    this.pollingRequest();
    this.mounted = true;
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  pollingRequest = () => {
    const { dispatch, chatId, agentId, lastMessageId } = this.props;

    // Handle state changes
    if (!chatId) {
      return;
    }
    if (agentId && history.state !== '/chat/active') {
      history.replace('/chat/active');
    }

    // Send next request
    const queryParams = {
      last_timestamp: moment().format(),
      last_message_id: lastMessageId
    };
    const promise = dispatch(pollingChat(chatId, queryParams));
    const onResponse = () => {
      if (!this.mounted) {
        return;
      }

      setTimeout(this.pollingRequest, 3000);
    };

    promise.then(onResponse, onResponse);
  };

  render() {
    return (
      <div>
        {this.props.children}
      </div>
    );
  }
}
