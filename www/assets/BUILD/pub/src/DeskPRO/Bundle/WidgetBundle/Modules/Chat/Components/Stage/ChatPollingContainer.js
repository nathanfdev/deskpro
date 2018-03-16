import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import moment from 'moment';
import { history, getLocation } from '../../../../Services/history';
import { pollingChat, unsetLoaded, unsetChatId } from '../../Actions/chatActions';
import {
  chatIdSelector,
  hasChatInfoSelector,
  lastMessageIdSelector,
  authorEmailSelector,
  isEndedSelector
} from '../../Selectors/chat';

@connect(state => ({
  chatId:        chatIdSelector(state),
  hasChatInfo:   hasChatInfoSelector(state),
  lastMessageId: lastMessageIdSelector(state),
  authorEmail:   authorEmailSelector(state),
  isEnded:       isEndedSelector(state)
}))
export class ChatPollingContainer extends React.Component {

  static propTypes = {
    dispatch:      PropTypes.func.isRequired,
    chatId:        PropTypes.string,
    hasChatInfo:   PropTypes.bool,
    lastMessageId: PropTypes.number,
    children:      PropTypes.node
  };

  componentDidMount() {
    this.mounted = true;
    this.pollingRequest();
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  pollingRequest = () => {
    const { dispatch, chatId, hasChatInfo, lastMessageId } = this.props;
    if (!chatId || !this.mounted) {
      return;
    }

    getLocation((location) => {
      // Redirect if has chat info only
      if (!hasChatInfo) {
        return;
      }

      if (location.pathname !== '/chat/active') {
        // If agent id is defined auto redirect to active stage
        history.replace('/chat/active');
        // Mark chat unloaded to show spinner until get messages in the next polling request
        dispatch(unsetLoaded());
      }
    });

    // Send ajax next request
    const queryParams = {
      last_timestamp:  moment().format(),
      last_message_id: lastMessageId
    };

    const promise = dispatch(pollingChat(chatId, queryParams));
    if (!promise || !promise.then) {
      return;
    }

    const onSuccessResponse = () => setTimeout(this.pollingRequest, 3000);
    const onErrorResponse = (response) => {
      // Stop polling on wring session code
      const data = response.data;
      if (data) {
        if (data.code === 400 && data.message === 'wrong_session_code') {
          dispatch(unsetChatId());
          return;
        }
        if (data.code === 403) {
          return;
        }
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
