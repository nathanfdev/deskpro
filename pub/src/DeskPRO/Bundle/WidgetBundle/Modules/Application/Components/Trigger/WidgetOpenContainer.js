import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { openWidget } from '../../Actions/dpWindowActions';
import { onlineAgentsCountSelector } from '../../Selectors/agent';
import { chatModeSelector, widgetHasChatSelector, liveDemoSelector } from '../../Selectors/dpWindow';
import { chatIdSelector, agentIdSelector, dateEndedSelector } from '../../../Chat/Selectors/chat';
import history from '../../../../Services/history';

@connect(state => ({
  widgetHasChat: widgetHasChatSelector(state),
  chatMode: chatModeSelector(state),
  chatId: chatIdSelector(state),
  agentId: agentIdSelector(state),
  dateEnded: dateEndedSelector(state),
  agentsCounts: onlineAgentsCountSelector(state),
  liveDemo: liveDemoSelector(state)
}))
export class WidgetOpenContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.node,
    widgetHasChat: PropTypes.bool,
    chatId: PropTypes.number,
    chatMode: PropTypes.string,
    agentId: PropTypes.number,
    agentsCounts: PropTypes.number,
    dateEnded: PropTypes.string,
    liveDemo: PropTypes.bool
  };

  onClick = () => {
    const { widgetHasChat, agentsCounts, chatId, chatMode, agentId, dateEnded, liveDemo, dispatch } = this.props;

    if (widgetHasChat && (liveDemo || agentsCounts > 0)) {
      if (chatId) {
        if (agentId || dateEnded) {
          history.replace('/chat/active');
        } else {
          history.replace('/chat/waiting');
        }
      } else {
        switch (chatMode) {
          case 'simple':
          default:
            history.replace('/chat/begin/simple');
            break;
          case 'conversation':
            history.replace('/chat/begin/conversation');
            break;
          case 'form':
            history.replace('/chat/begin/form');
            break;
        }
      }
    } else {
      history.replace('/ticket/form');
    }

    dispatch(openWidget());
  };

  render() {
    const props = this.props;
    const child = props.children;
    const childProps = child.props;

    return React.cloneElement(child, {
      ...props,
      ...childProps,

      onClick: this.onClick
    });
  }
}
