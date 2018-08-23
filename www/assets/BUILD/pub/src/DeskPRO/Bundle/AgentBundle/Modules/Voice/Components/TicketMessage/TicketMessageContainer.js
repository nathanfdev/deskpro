import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { loadBatch, collectionSelectorFactory, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import TicketMessage from './TicketMessage';
import { openDialpad } from '../../Actions/clientActions';
import { loadNumbers } from '../../Actions/numberActions';
import { allPhoneCallsSelector } from '../../Selectors/phoneCalls';
import { connectionsSelector } from '../../Selectors/client';
import { outboundCallsEnabledSelector } from '../../Selectors/agents';
import { allNumbersSelector } from '../../Selectors/numbers';
import { closeIframes } from './../../../Application/Actions/bootstrapActions';

@connect(state => ({
  people:               collectionSelectorFactory('Person', 'all')(state),
  phoneCalls:           allPhoneCallsSelector(state),
  connections:          connectionsSelector(state),
  numbers:              allNumbersSelector(state),
  outboundCallsEnabled: outboundCallsEnabledSelector(state),
  me:                   meSelector(state)
}))
class TicketMessageContainer extends React.Component {

  static propTypes = {
    dispatch:    PropTypes.func,
    data:        PropTypes.object,
    people:      PropTypes.object,
    connections: PropTypes.object,
    phoneCalls:  PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      data: props.data
    };
  }

  componentDidMount() {
    this.loadParticipants();

    const { dispatch } = this.props;
    const messageBroker = window.DeskPRO_Window.getMessageBroker();
    messageBroker.addMessageListener('agent.voice.recording_status', ({ data }) => {
      this.setState((state) => {
        state.data.linked.voice_phone_call[data.data.id] = data.data;
        return state;
      });

      dispatch(updateCollection('VoicePhoneCall', Immutable.List([Immutable.fromJS(data.data)])));
    });
  }

  componentDidUpdate() {
    this.loadParticipants();
  }

  onCall = () => {
    const phoneCall = this.getPhoneCall();
    const ticket = this.getTicket();

    this.props.dispatch(openDialpad(phoneCall.get('external_number'), ticket.get('id'), ticket.get('subject')));
  };

  onOpenSettings = () => {
    console.log('onOpenSettings');
  };

  getPhoneCall() {
    const { phoneCalls } = this.props;
    const { data } = this.state;
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

  getTicket() {
    const { data } = this.state;
    const message = data.data;

    return Immutable.fromJS(data.linked.ticket[message.ticket]);
  }

  getConnection() {
    const { connections } = this.props;
    const phoneCall = this.getPhoneCall();

    return connections.filter(connection => parseInt(connection.message.CallId, 10) === phoneCall.get('id')).first();
  }

  openTarget = (target) => {
    const type = target.get('type');
    const id = target.get('id');

    if (type === 'agent') {
      window.DeskPRO_Window.runPageRoute(`person:/agent/people/${id}`);
    } else if (type === 'queue') {
      if (window.DP_FRAME_OVERLAYS.admin) {
        closeIframes();
        window.DP_FRAME_OVERLAYS.admin.open(`/voice_channel/queues/${id}`);
      }
    } else if (type === 'auto_attendant') {
      if (window.DP_FRAME_OVERLAYS.admin) {
        closeIframes();
        window.DP_FRAME_OVERLAYS.admin.open(`/voice_channel/auto_attendants/${id}`);
      }
    }
  };

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
    const { data } = this.state;

    return (
      <TicketMessage
        {...this.props}
        message={data.data}
        ticket={this.getTicket()}
        phoneCall={this.getPhoneCall()}
        connection={this.getConnection()}
        onCall={this.onCall}
        onOpenSettings={this.onOpenSettings}
        openTarget={this.openTarget}
      />
    );
  }
}

export default TicketMessageContainer;
