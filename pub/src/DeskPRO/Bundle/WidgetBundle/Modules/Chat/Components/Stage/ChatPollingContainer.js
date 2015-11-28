import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { pollingChat } from '../../Actions/chatActions';
import { chatInfoSelector } from '../../Selectors/chat';
import history from '../../../../Services/history';
import moment from 'moment';

@connect(state => ({
  chatInfo: chatInfoSelector(state)
}))
export class ChatPollingContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    chatInfo: PropTypes.object.isRequired,
    children: PropTypes.node
  };

  componentDidMount() {
    this.pollingRequest();
  }

  pollingRequest = () => {
    const { dispatch, chatInfo } = this.props;
    const conversationId = chatInfo.get('conversation_id');
    if (!conversationId) {
      return;
    }

    const queryParams = {
      last_timestamp: moment().format(),
      last_message_id: 1
    };

    const promise = dispatch(pollingChat(conversationId, queryParams));
    promise.then(() => {
      if (chatInfo.get('agent_id') && history.state !== '/chat/active') {
        history.replaceState(null, '/chat/active');
      }

      setTimeout(this.pollingRequest, 3000);
    });
  };

  render() {
    return (
      <div>
        {this.props.children}
      </div>
    );
  }
}
