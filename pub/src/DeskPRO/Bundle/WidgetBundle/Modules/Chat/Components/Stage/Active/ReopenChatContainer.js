import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { reopenChat, disableChatReopen, enableChatReopen } from '../../../Actions/chatActions';
import moment from 'moment';
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

  constructor(props) {
    super(props);
    this.state = {
      displayChild: true
    };
  }

  componentDidMount() {
    this.checkDateEnded();
  }

  componentDidUpdate() {
    this.checkDateEnded();
  }

  componentWillUnmount() {
    clearTimeout(this.timeout);
  }

  onDisableReopen = () => {
    const { canReopen, dispatch } = this.props;
    if (canReopen) {
      dispatch(disableChatReopen());
    }
  };

  onReopen = () => {
    const { chatId, canReopen, locked, dispatch } = this.props;
    if (!canReopen || locked) {
      return;
    }

    dispatch(reopenChat(chatId));
  };

  checkDateEnded() {
    const { dispatch, canReopen, dateEnded } = this.props;
    if (!dateEnded) {
      clearTimeout(this.timeout);

      if (!canReopen) {
        dispatch(enableChatReopen());
      }

      return;
    }

    const ended = moment(dateEnded).format('X');
    const now = moment().format('X');
    const delay = ended - now + 120; // can reopen in 2 minutes

    if (canReopen) {
      if (delay > 0) {
        this.timeout = setTimeout(this.onDisableReopen, delay * 1000);
      } else {
        this.onDisableReopen();
      }
    }
  }

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
