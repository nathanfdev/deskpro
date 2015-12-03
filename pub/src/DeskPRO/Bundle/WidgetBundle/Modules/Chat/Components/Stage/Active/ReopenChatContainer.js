import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { dateEndedSelector, chatIdSelector, isEndedSelector } from '../../../Selectors/chat';
import { reopenChat } from '../../../Actions/chatActions';
import moment from 'moment';

@connect(state => ({
  chatId: chatIdSelector(state),
  dateEnded: dateEndedSelector(state),
  isEnded: isEndedSelector(state)
}))
export class ReopenChatContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    chatId: PropTypes.number,
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
    this.setState({
      displayChild: false
    });
  };

  onReopen = () => {
    const { chatId, dispatch } = this.props;
    dispatch(reopenChat(chatId));
  };

  checkDateEnded() {
    const { dateEnded } = this.props;
    if (!dateEnded) {
      clearTimeout(this.timeout);

      if (!this.state.displayChild) {
        this.setState({
          displayChild: true
        });
      }

      return;
    }

    const now = moment().format('X');
    const delay = moment().format('X') - now + 120000;

    if (this.state.displayChild) {
      if (delay > 0) {
        this.timeout = setTimeout(this.onDisableReopen, delay);
      } else {
        this.onDisableReopen();
      }
    }
  }

  render() {
    const { children } = this.props;
    const childProps = children.props;

    return this.state.displayChild
      ? React.cloneElement(children, {
        ...childProps,

        isEnded: this.props.isEnded,
        onReopen: this.onReopen
      })
      : null;
  }
}
