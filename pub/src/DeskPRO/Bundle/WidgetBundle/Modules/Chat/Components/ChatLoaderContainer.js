import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { loadChatInfo, setChatId, unsetChatId } from '../Actions/chatActions';
import { openWidget } from '../../Application/Actions/dpWindowActions';
import { widgetLoadedSelector } from '../../Application/Selectors/bootstrap';
import { widgetHasChatSelector } from '../../Application/Selectors/dpWindow';
import { onlineAgentsCountSelector } from '../../Application/Selectors/agent';
import history from '../../../Services/history';

@connect(state => ({
  widgetLoaded: widgetLoadedSelector(state),
  widgetHasChat: widgetHasChatSelector(state),
  agentsCounts: onlineAgentsCountSelector(state)
}))
export class ChatLoaderContainer extends React.Component {

  static propTypes = {
    widgetLoaded: PropTypes.bool,
    widgetHasChat: PropTypes.bool,
    agentsCounts: PropTypes.number,
    dispatch: PropTypes.func
  };

  componentDidMount() {
    this.onLoad();
  }

  componentDidUpdate() {
    this.onLoad();
  }

  onLoad() {
    const { dispatch, widgetLoaded, widgetHasChat, agentsCounts } = this.props;
    const storedChatId = Number(localStorage.getItem('dpWidget.chat.chatId'));

    if (!widgetLoaded || !widgetHasChat || !storedChatId || !agentsCounts) {
      return;
    }

    const promise = dispatch(loadChatInfo(storedChatId));
    promise.then(chatInfo => {
      // Reset stored chat id on reload page if chat was ended
      if (chatInfo.date_ended) {
        dispatch(unsetChatId());
        return;
      }

      dispatch(setChatId(storedChatId));
      dispatch(openWidget());

      if (chatInfo.agent_id) {
        history.replace('/chat/active');
      } else {
        history.replace('/chat/waiting');
      }
    });
  }

  render() {
    return null;
  }
}
