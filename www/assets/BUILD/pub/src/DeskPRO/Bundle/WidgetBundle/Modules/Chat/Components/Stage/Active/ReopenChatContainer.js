import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { reopenChat } from '../../../Actions/chatActions';
import {
  dateEndedSelector,
  chatIdSelector,
  isEndedSelector,
  lockedPollingSelector,
  canReopenSelector,
  lostConnectionSelector
} from '../../../Selectors/chat';

@connect(state => ({
  chatId:         chatIdSelector(state),
  locked:         lockedPollingSelector(state),
  dateEnded:      dateEndedSelector(state),
  isEnded:        isEndedSelector(state),
  canReopen:      canReopenSelector(state),
  lostConnection: lostConnectionSelector(state)
}))
export class ReopenChatContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    chatId:         PropTypes.string,
    locked:         PropTypes.bool,
    isEnded:        PropTypes.bool,
    canReopen:      PropTypes.bool,
    children:       PropTypes.node,
    lostConnection: PropTypes.bool
  };

  onReopen = () => {
    const { chatId, canReopen, locked, dispatch } = this.props;
    if (!canReopen || locked) {
      return;
    }

    dispatch(reopenChat(chatId));
  };

  render() {
    const { children, canReopen, isEnded, locked, lostConnection } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      locked,
      isEnded,
      canReopen,
      lostConnection,
      onReopen: this.onReopen
    });
  }
}
export default ReopenChatContainer;
