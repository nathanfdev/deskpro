import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { allPeopleSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/people';
import OutgoingCall from './OutgoingCall';
import { hangup } from '../../../Actions/clientActions';
import { connectionsSelector } from '../../../Selectors/client';

@connect(state => ({
  people:      allPeopleSelector(state),
  connections: connectionsSelector(state)
}))
class OutgoingCallContainer extends React.Component {

  static propTypes = {
    connections:  PropTypes.object,
    outgoingCall: PropTypes.object,
    dispatch:     PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      init: false
    };
  }

  componentDidMount() {
    console.debug('Enabling fast poller interval: %d', window.DP_POLLER_INTERVAL_VOICE);
    window.DeskPRO_Window.getMessageChanneler().poller.setInterval(window.DP_POLLER_INTERVAL_VOICE);
    const messageBroker = window.DeskPRO_Window.getMessageBroker();
    messageBroker.addMessageListener('agent.voice.outgoing-call-init', () => {
      this.setState({
        init: true,
      });
    });
  }

  componentWillUnmount() { // eslint-disable-line
    console.debug('Restoring poller interval: %d', window.DP_POLLER_INTERVAL);
    window.DeskPRO_Window.getMessageChanneler().poller.setInterval(window.DP_POLLER_INTERVAL);
  }

  hangup = () => {
    const { connections, outgoingCall, dispatch } = this.props;
    const connection = connections
      .filter(c => c.callId === outgoingCall.getIn(['phoneCall', 'id']))
      .first();

    if (!connection) {
      return;
    }

    dispatch(hangup(connection.callId));
  };

  render() {
    return (
      <OutgoingCall
        {...this.props}
        {...this.state}
        hangup={this.hangup}
      />
    );
  }
}

export default OutgoingCallContainer;
