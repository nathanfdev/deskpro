import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { pollingChat } from '../../Actions/chatActions';
import { chatIdSelector, agentIdSelector, lastMessageIdSelector } from '../../Selectors/chat';
import history from '../../../../Services/history';
import moment from 'moment';

@connect(state => ({
  chatId: chatIdSelector(state),
  agentId: agentIdSelector(state),
  lastMessageId: lastMessageIdSelector(state)
}))
export class ChatPollingContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    chatId: PropTypes.number,
    agentId: PropTypes.number,
    lastMessageId: PropTypes.any,
    children: PropTypes.node
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
    if (!chatId) {
      return;
    }

    const queryParams = {
      last_timestamp: moment().format(),
      last_message_id: lastMessageId
    };

    const promise = dispatch(pollingChat(chatId, queryParams));
    promise.then(
      () => {
        if (!this.mounted) {
          return;
        }
        if (agentId && history.state !== '/chat/active') {
          history.replace('/chat/active');
        }

        setTimeout(this.pollingRequest, 3000);
      },
      () => {
        if (!this.mounted) {
          return;
        }

        setTimeout(this.pollingRequest, 3000);
      }
    );
  };

  render() {
    return (
      <div>
        {this.props.children}
      </div>
    );
  }
}
