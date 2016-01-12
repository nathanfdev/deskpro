import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import history from '../../../../Services/history';
import moment from 'moment';
import { pollingChat, unsetLoaded, unsetChatId } from '../../Actions/chatActions';
import {
  chatIdSelector,
  hasChatInfoSelector,
  agentIdSelector,
  lastMessageIdSelector,
  authorEmailSelector,
  isEndedSelector,
  needValidateEmailSelector
} from '../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state),
  hasChatInfo: hasChatInfoSelector(state),
  agentId: agentIdSelector(state),
  lastMessageId: lastMessageIdSelector(state),
  authorEmail: authorEmailSelector(state),
  isEnded: isEndedSelector(state),
  needValidateEmail: needValidateEmailSelector(state)
}))
export class ChatPollingContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    chatId: PropTypes.number,
    hasChatInfo: PropTypes.bool,
    agentId: PropTypes.number,
    lastMessageId: PropTypes.any,
    children: PropTypes.node,
    authorEmail: PropTypes.string,
    isEnded: PropTypes.bool,
    needValidateEmail: PropTypes.bool
  };

  componentDidMount() {
    this.mounted = true;
    this.pollingRequest();
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  pollingRequest = () => {
    const { dispatch, chatId, hasChatInfo, agentId, lastMessageId, needValidateEmail } = this.props;
    if (!chatId || !this.mounted) {
      return;
    }

    this._unlisten = history.listen(location => {
      // Redirect if has chat info only
      if (!hasChatInfo) {
        return;
      }

      if (needValidateEmail) {
        // Auto redirect on validate email stage
        if (location.pathname !== '/chat/validation/email') {
          history.replace('/chat/validation/email');
        }
      } else {
        if (agentId && location.pathname !== '/chat/active') {
          // If agent id is defined auto redirect to active stage
          history.replace('/chat/active');
          // Mark chat unloaded to show spinner until get messages in next polling request
          dispatch(unsetLoaded());
        } else {
          // Auto redirect on waiting stage
          if (location.pathname !== '/chat/waiting') {
            history.replace('/chat/waiting');
          }
        }
      }
    });

    this._unlisten();

    // Send ajax next request
    const queryParams = {
      last_timestamp: moment().format(),
      last_message_id: lastMessageId
    };
    const promise = dispatch(pollingChat(chatId, queryParams));
    const onSuccessResponse = () => {
      setTimeout(this.pollingRequest, 3000);
    };

    const onErrorResponse = response => {
      // Stop polling on wring session code
      const data = response.data;
      if (data && data.code === 400 && data.message === 'wrong_session_code') {
        dispatch(unsetChatId());
        return;
      }

      setTimeout(this.pollingRequest, 3000);
    };

    promise.then(onSuccessResponse, onErrorResponse);
  };

  render() {
    return (
      <div>
        {this.props.children}
      </div>
    );
  }
}
