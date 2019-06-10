import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { allPeopleSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/people';
import VoicemailList from './VoicemailList';
import { deleteVoicemailRecord, markVoicemalRecordAsListened, createVoicemailTicket } from '../../../Actions/voicemailRecordActions';
import { allVoicemailRecordsSelector, isVoicemailRecordsLoadedSelector } from '../../../Selectors/voicemailRecords';
import { allPhoneCallsSelector } from '../../../Selectors/phoneCalls';
import { openDialpad } from '../../../Actions/clientActions';
import { outboundCallsEnabledSelector } from '../../../Selectors/agents';

@connect(state => ({
  records:              allVoicemailRecordsSelector(state),
  recordsLoaded:        isVoicemailRecordsLoadedSelector(state),
  phoneCalls:           allPhoneCallsSelector(state),
  people:               allPeopleSelector(state),
  outboundCallsEnabled: outboundCallsEnabledSelector(state)
}))
class VoicemailListContainer extends React.Component {

  static propTypes = {
    dispatch:      PropTypes.func,
    recordsLoaded: PropTypes.bool
  };

  onCallback = (phoneCall) => {
    const { dispatch } = this.props;
    dispatch(openDialpad(phoneCall.get('external_number')));
  };

  onDelete = (record) => {
    const { dispatch } = this.props;
    dispatch(deleteVoicemailRecord(record.get('id')));
  };

  onMarkListened = (record) => {
    const { dispatch } = this.props;
    dispatch(markVoicemalRecordAsListened(record.get('id')));
  };

  onCreateTicket = (record) => {
    const { dispatch } = this.props;
    dispatch(createVoicemailTicket(record.get('id')));
  };

  onOpenPerson = (id) => {
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
      <VoicemailList
        {...this.props}
        onCallback={this.onCallback}
        onDelete={this.onDelete}
        onMarkListened={this.onMarkListened}
        onOpenPerson={this.onOpenPerson}
        onCreateTicket={this.onCreateTicket}
      />
    );
  }
}

export default VoicemailListContainer;
