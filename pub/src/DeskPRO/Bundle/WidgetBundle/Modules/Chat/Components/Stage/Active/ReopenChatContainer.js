import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { reopenChat } from '../../../Actions/chatActions';
import {
  dateEndedSelector,
  chatIdSelector,
  isEndedSelector,
  lockedPollingSelector,
  canReopenSelector
} from '../../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state),
  locked: lockedPollingSelector(state),
  dateEnded: dateEndedSelector(state),
  isEnded: isEndedSelector(state),
  canReopen: canReopenSelector(state)
}))
export class ReopenChatContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    chatId: PropTypes.number,
    locked: PropTypes.bool,
    isEnded: PropTypes.bool,
    dateEnded: PropTypes.string,
    canReopen: PropTypes.bool,
    children: PropTypes.any
  };

  onReopen = () => {
    const { chatId, canReopen, locked, dispatch } = this.props;
    if (!canReopen || locked) {
      return;
    }

    dispatch(reopenChat(chatId));
  };

  render() {
    const { children, canReopen, isEnded, locked } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      locked,
      isEnded,
      canReopen,
      onReopen: this.onReopen
    });
  }
}
