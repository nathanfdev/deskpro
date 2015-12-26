import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import history from '../../../../Services/history';
import moment from 'moment';
import { pollingChat, sendTranscriptData, unsetLoaded } from '../../Actions/chatActions';
import {
  chatIdSelector,
  agentIdSelector,
  dateEndedSelector,
  canReopenSelector,
  lastMessageIdSelector,
  transcriptCheckedSelector,
  transcriptSendingSelector,
  transcriptSentSelector,
  authorEmailSelector,
  isEndedSelector
} from '../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state),
  agentId: agentIdSelector(state),
  dateEnded: dateEndedSelector(state),
  canReopen: canReopenSelector(state),
  lastMessageId: lastMessageIdSelector(state),
  authorEmail: authorEmailSelector(state),
  transcriptChecked: transcriptCheckedSelector(state),
  transcriptSending: transcriptSendingSelector(state),
  transcriptSent: transcriptSentSelector(state),
  isEnded: isEndedSelector(state)
}))
export class ChatPollingContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    chatId: PropTypes.number,
    agentId: PropTypes.number,
    dateEnded: PropTypes.string,
    canReopen: PropTypes.bool,
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
  }

  pollingRequest = () => {
    const { dispatch, chatId, agentId, lastMessageId } = this.props;
    const { isEnded, authorEmail, transcriptChecked, transcriptSending, transcriptSent } = this.props;

    if (!chatId) {
      return;
    }

    history.listen(location => {
      if (agentId && location.pathname !== '/chat/active') {
        // If agent id is defined redirect to active stage
        history.replace('/chat/active');
        // Mark chat unloaded to show spinner until get messages in next polling request
        dispatch(unsetLoaded());
      }
    });

    // If can send chat transcript data and chat is ended
    if (isEnded && authorEmail && transcriptChecked && !transcriptSending && !transcriptSent) {
      // send transcript data
      dispatch(sendTranscriptData(chatId));
    }

    // Send ajax next request
    const queryParams = {
      last_timestamp: moment().format(),
      last_message_id: lastMessageId
    };
    const promise = dispatch(pollingChat(chatId, queryParams));
    const onResponse = () => {
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
