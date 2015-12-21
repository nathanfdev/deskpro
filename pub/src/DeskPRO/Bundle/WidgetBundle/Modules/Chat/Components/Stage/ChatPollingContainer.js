import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { pollingChat, sendTranscriptData } from '../../Actions/chatActions';
import history from '../../../../Services/history';
import moment from 'moment';
import { widgetOpenedSelector } from '../../../Application/Selectors/dpWindow';
import {
  chatIdSelector,
  agentIdSelector,
  lastMessageIdSelector,
  transcriptCheckedSelector,
  transcriptSendingSelector,
  transcriptSentSelector,
  authorEmailSelector,
  isEndedSelector
} from '../../Selectors/chat';

@connect(state => ({
  widgetOpened: widgetOpenedSelector(state),
  chatId: chatIdSelector(state),
  agentId: agentIdSelector(state),
  lastMessageId: lastMessageIdSelector(state),
  authorEmail: authorEmailSelector(state),
  transcriptChecked: transcriptCheckedSelector(state),
  transcriptSending: transcriptSendingSelector(state),
  transcriptSent: transcriptSentSelector(state),
  isEnded: isEndedSelector(state)
}))
export class ChatPollingContainer extends React.Component {

  static propTypes = {
    widgetOpened: PropTypes.bool,
    dispatch: PropTypes.func.isRequired,
    chatId: PropTypes.number,
    agentId: PropTypes.number,
    lastMessageId: PropTypes.any,
    children: PropTypes.node,
    authorEmail: PropTypes.string,
    transcriptChecked: PropTypes.bool,
    transcriptSending: PropTypes.bool,
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
    const { dispatch, chatId, agentId, lastMessageId, widgetOpened } = this.props;
    const { isEnded, authorEmail, transcriptChecked, transcriptSending, transcriptSent } = this.props;

    // Handle state changes
    if (!chatId || !widgetOpened) {
      return;
    }
    if (agentId && history.state !== '/chat/active') {
      history.replace('/chat/active');
    }

    // Can send chat transcript
    if (isEnded && authorEmail && transcriptChecked && !transcriptSending && !transcriptSent) {
      dispatch(sendTranscriptData(chatId));
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
