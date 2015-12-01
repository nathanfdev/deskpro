import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { dateEndedSelector, isEndedSelector } from '../../../Selectors/chat';
import { reopenChat } from '../../../Actions/chatActions';
import moment from 'moment';

@connect(state => ({
  dateEnded: dateEndedSelector(state),
  isEnded: isEndedSelector(state)
}))
export class ReopenChatContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
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
    this.props.dispatch(reopenChat());
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
    const delay = dateEnded - now + 10000;

    if (delay > 0) {
      this.timeout = setTimeout(this.onDisableReopen, delay);
    } else {
      this.onDisableReopen();
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
