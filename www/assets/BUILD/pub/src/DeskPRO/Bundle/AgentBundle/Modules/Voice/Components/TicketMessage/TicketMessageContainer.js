import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { loadBatch, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import TicketMessage from './TicketMessage';
import { openDialpad } from '../../Actions/clientActions';
import { loadNumbers } from '../../Actions/numberActions';
import { allPhoneCallsSelector } from '../../Selectors/phoneCalls';
import { connectionsSelector } from '../../Selectors/client';
import { outboundCallsEnabledSelector } from '../../Selectors/agents';
import { allNumbersSelector } from '../../Selectors/numbers';

@connect(state => ({
  people:               collectionSelectorFactory('Person', 'all')(state),
  phoneCalls:           allPhoneCallsSelector(state),
  connections:          connectionsSelector(state),
  numbers:              allNumbersSelector(state),
  outboundCallsEnabled: outboundCallsEnabledSelector(state)
}))
class TicketMessageContainer extends React.Component {

  static propTypes = {
    dispatch:    PropTypes.func,
    data:        PropTypes.object,
    people:      PropTypes.object,
    connections: PropTypes.object,
    phoneCalls:  PropTypes.object
  };

  componentDidMount() {
    this.loadParticipants();
  }

  componentDidUpdate() {
    this.loadParticipants();
  }

  onCall = () => {
    const phoneCall = this.getPhoneCall();
    this.props.dispatch(openDialpad(phoneCall.get('external_number')));
  };

  onOpenSettings = () => {
    console.log('onOpenSettings');
  };

  getPhoneCall() {
    const { phoneCalls, data } = this.props;
    const message = data.data;
    const phoneCallId = message.attributes[0].phone_call;

    // get from record store
    // for active calls
    if (phoneCalls.get(phoneCallId)) {
      return phoneCalls.get(phoneCallId);
    }

    // get from message data attribute from template
    return Immutable.fromJS(data.linked.voice_phone_call[phoneCallId]);
  }

  getConnection() {
    const { connections } = this.props;
    const phoneCall = this.getPhoneCall();

    return connections.filter(connection => parseInt(connection.message.CallId, 10) === phoneCall.get('id')).first();
  }

  loadParticipants() {
    const { people, dispatch } = this.props;
    const phoneCall = this.getPhoneCall();
    const loadIds = [];

    phoneCall.get('participants').forEach((participant) => {
      const personId = participant.get('person');
      if (personId && !people.get(personId)) {
        loadIds.push(personId);
      }
    });

    if (loadIds) {
      dispatch(loadBatch('Person', loadIds, 'all'));
    }

    dispatch(loadNumbers());
  }

  render() {
    const { data } = this.props;

    return (
      <TicketMessage
        {...this.props}
        message={data.data}
        phoneCall={this.getPhoneCall()}
        connection={this.getConnection()}
        onCall={this.onCall}
        onOpenSettings={this.onOpenSettings}
      />
    );
  }
}

export default TicketMessageContainer;
