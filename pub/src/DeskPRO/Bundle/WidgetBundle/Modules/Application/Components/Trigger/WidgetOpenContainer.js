import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { openWidget } from '../../Actions/dpWindowActions';
import { onlineAgentsCountSelector } from '../../Selectors/agent';
import { chatModeSelector } from '../../Selectors/dpWindow';
import { chatIdSelector } from '../../../Chat/Selectors/chat';
import history from '../../../../Services/history';

@connect(state => ({
  chatMode: chatModeSelector(state),
  chatId: chatIdSelector(state),
  agentsCounts: onlineAgentsCountSelector(state)
}))
export class WidgetOpenContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.node,
    chatId: PropTypes.number,
    chatMode: PropTypes.string,
    agentsCounts: PropTypes.number
  };

  onClick = () => {
    const { chatId, chatMode, dispatch } = this.props;

    if (chatId) {
      history.replace('/chat/active');
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
