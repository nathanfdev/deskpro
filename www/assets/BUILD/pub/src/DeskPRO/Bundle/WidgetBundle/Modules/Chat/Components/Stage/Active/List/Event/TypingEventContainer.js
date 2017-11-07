import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import moment from 'moment';
import { TypingEvent } from './TypingEvent';
import { agentNameSelector, agentAvatarSelector, agentTypingDateSelector } from '../../../../../Selectors/chat';

@connect(state => ({
  agentName:       agentNameSelector(state),
  agentAvatar:     agentAvatarSelector(state),
  agentTypingDate: agentTypingDateSelector(state)
}))
export class TypingEventContainer extends React.Component {

  static propTypes = {
    agentTypingDate: PropTypes.string,
    onUpdate:        PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      displayChild:    false,
      agentTypingDate: null
    };
  }

  componentDidMount() {
    this.checkLastTypingDate();
  }

  componentWillReceiveProps(newProps) {
    this.checkLastTypingDate(newProps);
  }

  shouldComponentUpdate(props, state) {
    return state.displayChild !== this.state.displayChild;
  }

  componentDidUpdate() {
    if (this.props.onUpdate) {
      this.props.onUpdate();
    }
  }

  componentWillUnmount() {
    clearTimeout(this.timeout);
  }

  onHide = () => {
    this.setState({
      displayChild: false
    });
  };

  checkLastTypingDate(props) {
    const agentTypingDate = props ? props.agentTypingDate : this.props.agentTypingDate;
    if (!agentTypingDate) {
      if (this.state.displayChild) {
        this.setState({
          displayChild:    false,
          agentTypingDate: null
        });
      }

      return;
    }

    const ended = moment(agentTypingDate).format('X');
    const now = moment().format('X');
    const delay = (ended - now) + 10; // displays in 10 seconds

    if (delay > 0 && (!this.state.displayChild || agentTypingDate !== this.state.agentTypingDate)) {
      this.setState({
        displayChild: true,
        agentTypingDate
      });

      this.timeout = setTimeout(this.onHide, delay * 1000);
    }
  }

  render() {
    return this.state.displayChild ? <TypingEvent {...this.props} /> : null;
  }
}
export default TypingEventContainer;
