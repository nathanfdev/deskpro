import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { TypingEvent } from './TypingEvent';
import { agentNameSelector, agentAvatarSelector, agentLastTypingTimeSelector } from '../../../../../Selectors/chat';
import moment from 'moment';

@connect(state => ({
  agentName: agentNameSelector(state),
  agentAvatar: agentAvatarSelector(state),
  agentLastTypingTime: agentLastTypingTimeSelector(state)
}))
export class TypingEventContainer extends React.Component {

  static propTypes = {
    agentLastTypingTime: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      displayChild: false,
      agentLastTypingTime: null
    };
  }

  componentDidMount() {
    this.checkLastTypingDate();
  }

  componentDidUpdate() {
    this.checkLastTypingDate();
  }

  componentWillUnmount() {
    clearTimeout(this.timeout);
  }

  onHide = () => {
    this.setState({
      displayChild: false
    });
  };

  checkLastTypingDate() {
    const { agentLastTypingTime } = this.props;
    if (!agentLastTypingTime) {
      return;
    }

    const ended = moment(agentLastTypingTime).format('X');
    const now = moment().format('X');
    const delay = ended - now + 10000; // displays in 10 seconds

    if (delay > 0 && (!this.state.displayChild || agentLastTypingTime !== this.state.agentLastTypingTime)) {
      this.setState({
        displayChild: true,
        agentLastTypingTime: agentLastTypingTime
      });

      this.timeout = setTimeout(this.onHide, delay);
    }
  }

  render() {
    return this.state.displayChild ? <TypingEvent {...this.props} /> : null;
  }
}
