import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { TypingEvent } from './TypingEvent';
import { agentNameSelector, agentAvatarSelector, agentTypingDateSelector } from '../../../../../Selectors/chat';
import moment from 'moment';

@connect(state => ({
  agentName: agentNameSelector(state),
  agentAvatar: agentAvatarSelector(state),
  agentTypingDate: agentTypingDateSelector(state)
}))
export class TypingEventContainer extends React.Component {

  static propTypes = {
    agentTypingDate: PropTypes.string
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
    const { agentTypingDate } = this.props;

    if (!agentTypingDate) {
      if (this.state.displayChild) {
        this.setState({
          displayChild: false,
          agentTypingDate: null
        });
      }

      return;
    }

    const ended = moment(agentTypingDate).format('X');
    const now = moment().format('X');
    const delay = ended - now + 10; // displays in 10 seconds

    if (delay > 0 && (!this.state.displayChild || agentTypingDate !== this.state.agentTypingDate)) {
      this.setState({
        displayChild: true,
        agentTypingDate: agentTypingDate
      });

      this.timeout = setTimeout(this.onHide, delay * 1000);
    }
  }

  render() {
    return this.state.displayChild ? <TypingEvent {...this.props} /> : null;
  }
}
