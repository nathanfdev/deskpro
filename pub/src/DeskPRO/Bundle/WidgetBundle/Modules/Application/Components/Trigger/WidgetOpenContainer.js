import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { openChatBeginStage, openWidget } from '../../Actions/dpWindowActions';
import { onlineAgentsCountSelector } from '../../Selectors/agent';
import { requireChatLoginSelector } from '../../Selectors/bootstrap';
import { chatBeginModeSelector, widgetHasChatSelector, liveDemoSelector } from '../../Selectors/dpWindow';
import { chatIdSelector, agentIdSelector, dateEndedSelector, needValidateEmailSelector } from '../../../Chat/Selectors/chat';
import history from '../../../../Services/history';

@connect(state => ({
  widgetHasChat: widgetHasChatSelector(state),
  chatBeginMode: chatBeginModeSelector(state),
  chatId: chatIdSelector(state),
  agentId: agentIdSelector(state),
  dateEnded: dateEndedSelector(state),
  agentsCounts: onlineAgentsCountSelector(state),
  needValidateEmail: needValidateEmailSelector(state),
  requireChatLogin: requireChatLoginSelector(state),
  liveDemo: liveDemoSelector(state)
}))
export class WidgetOpenContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.node,
    widgetHasChat: PropTypes.bool,
    requireChatLogin: PropTypes.bool,
    chatId: PropTypes.number,
    chatBeginMode: PropTypes.string,
    agentId: PropTypes.number,
    agentsCounts: PropTypes.number,
    dateEnded: PropTypes.string,
    needValidateEmail: PropTypes.bool,
    liveDemo: PropTypes.bool
  };

  onClick = () => {
    const { widgetHasChat, requireChatLogin, agentsCounts, liveDemo, dispatch } = this.props;
    const { chatId, chatBeginMode, agentId, dateEnded, needValidateEmail } = this.props;

    if (widgetHasChat && (liveDemo || agentsCounts > 0)) {
      if (chatId && !liveDemo) {
        if (agentId || dateEnded) {
          history.replace('/chat/active');
        } else if (needValidateEmail) {
          history.replace('/chat/validation/email');
        } else {
          history.replace('/chat/waiting');
        }
      } else if (requireChatLogin) {
        history.replace('/chat/validation/login');
      } else {
        openChatBeginStage(chatBeginMode);
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
