import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { dateEndedSelector, chatIdSelector, isEndedSelector, lockedPollingSelector } from '../../../Selectors/chat';
import { reopenChat } from '../../../Actions/chatActions';
import { windowResize } from '../../../../Application/Actions/dpWindowActions';
import moment from 'moment';

@connect(state => ({
  chatId: chatIdSelector(state),
  locked: lockedPollingSelector(state),
  dateEnded: dateEndedSelector(state),
  isEnded: isEndedSelector(state)
}))
export class ReopenChatContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    chatId: PropTypes.number,
    locked: PropTypes.bool,
    isEnded: PropTypes.bool,
    dateEnded: PropTypes.string,
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
    if (this.state.displayChild) {
      this.setState({
        displayChild: false
      });

      this.props.dispatch(windowResize());
    }
  };

  onReopen = () => {
    const { chatId, locked, dispatch } = this.props;
    if (locked) {
      return;
    }

    dispatch(reopenChat(chatId));
  };

  checkDateEnded() {
    const { dispatch, dateEnded } = this.props;
    if (!dateEnded) {
      clearTimeout(this.timeout);

      if (!this.state.displayChild) {
        this.setState({
          displayChild: true
        });

        dispatch(windowResize());
      }

      return;
    }

    const ended = moment(dateEnded).format('X');
    const now = moment().format('X');
    const delay = ended - now + 120; // can reopen in 2 minutes

    if (this.state.displayChild) {
      if (delay > 0) {
        this.timeout = setTimeout(this.onDisableReopen, delay * 1000);
      } else {
        this.onDisableReopen();
      }
    }
  }

  render() {
    const { children, isEnded, locked } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      locked,
      isEnded,
      canReopen: this.state.displayChild,
      onReopen: this.onReopen
    });
  }
}
