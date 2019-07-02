import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { allPeopleSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/people';
import MissedCallsList from './MissedCallsList';
import { deleteVoiceMissedAgentCall, markVoiceMissedAgentCallAsListened, createVoicemailTicket } from '../../../Actions/voiceMissedAgentCallActions';
import { allVoiceMissedAgentCallsSelector, isVoiceMissedAgentCallsLoadedSelector } from '../../../Selectors/voicemailRecords';
import { allPhoneCallsSelector } from '../../../Selectors/phoneCalls';
import { openDialpad } from '../../../Actions/clientActions';
import { outboundCallsEnabledSelector } from '../../../Selectors/agents';

@connect(state => ({
  records:              allVoiceMissedAgentCallsSelector(state),
  recordsLoaded:        isVoiceMissedAgentCallsLoadedSelector(state),
  phoneCalls:           allPhoneCallsSelector(state),
  people:               allPeopleSelector(state),
  outboundCallsEnabled: outboundCallsEnabledSelector(state)
}))
class MissedCallsListContainer extends React.Component {

  static propTypes = {
    dispatch:      PropTypes.func,
    recordsLoaded: PropTypes.bool
  };

  callBack = (phoneCall) => {
    const { dispatch } = this.props;
    dispatch(openDialpad(phoneCall.get('external_number')));
  };

  deleteRecording = (record) => {
    const { dispatch } = this.props;
    dispatch(deleteVoiceMissedAgentCall(record.get('id')));
  };

  markListened = (record) => {
    const { dispatch } = this.props;
    dispatch(markVoiceMissedAgentCallAsListened(record.get('id')));
  };

  createTicket = (record) => {
    const { dispatch } = this.props;
    dispatch(createVoicemailTicket(record.get('id')));
  };

  openPerson = (id) => {
    window.DeskPRO_Window.runPageRoute(`person:/agent/people/${id}`);
  };

  render() {
    const { recordsLoaded } = this.props;
    if (!recordsLoaded) {
      return (
        <div style={{ height: '100px', position: 'relative' }}>
          <div className="ui active inverted dimmer">
            <div className="ui mini text loader">Loading</div>
          </div>
        </div>
      );
    }

    return (
      <MissedCallsList
        {...this.props}
        callBack={this.callBack}
        deleteRecording={this.deleteRecording}
        markListened={this.markListened}
        openPerson={this.openPerson}
        createTicket={this.createTicket}
      />
    );
  }
}

export default MissedCallsListContainer;
