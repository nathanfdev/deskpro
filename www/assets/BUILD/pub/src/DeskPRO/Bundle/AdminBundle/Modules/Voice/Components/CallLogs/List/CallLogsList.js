import PropTypes from 'prop-types';
import React from 'react';
import ReactPaginate from 'react-paginate';
import moment from 'moment';
import Immutable from 'immutable';
import { BlobPlayButton } from 'DeskPRO/Component/AudioWidget/PlayButton';
import { DeleteButton } from 'DeskPRO/Component/AudioWidget/DeleteButton';
import { Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import Modal from 'DeskPRO/Component/Semantic/Modal';
import { Button } from '@deskpro/react-components';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import PersonName from '../../../../Common/Components/PersonName';
import CallStatus from '../Common/CallStatus';
import CallDuration from '../Common/CallDuration';
import { openTicket, openPerson } from '../../../../../Services/history';


class CallLogsList extends React.Component {

  static propTypes = {
    calls:               PropTypes.object,
    numbers:             PropTypes.object,
    pageCount:           PropTypes.number,
    liveUpdates:         PropTypes.bool,
    onPageChange:        PropTypes.func,
    onOpenCallLog:       PropTypes.func,
    openDialpad:         PropTypes.func,
    onToggleLiveUpdates: PropTypes.func,
    onDeleteRecordClick: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      deleteConfirmation: false
    };
  }

  onConfirmDeleteClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onDeleteRecordClick(this.state.deleteConfirmation);
    this.setState({ deleteConfirmation: false });
  };

  onRejectDeleteClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.setState({ deleteConfirmation: false });
  };

  onDeleteClick = (callId) => {
    this.setState({ deleteConfirmation: callId });
  };

  render() {
    const { calls, numbers, pageCount, liveUpdates } = this.props;
    const { onPageChange, onOpenCallLog, openDialpad, onToggleLiveUpdates } = this.props;
    const { deleteConfirmation } = this.state;

    return ([
      <Modal
        isOpen={deleteConfirmation > 0}
        title="Confirm record deletion"
        contentStyles={{ top: '25%', left: '37%', bottom: 'auto', height: '150px', width: '30%' }}
        className="voice-delete-callrecord-confirmation"
      >
        <h2>Do you really want to delete this record? This cannot be undone.</h2>
        <div>
          <span style={{ float: 'left' }}>
            <Button size="large" type="secondary" onClick={this.onRejectDeleteClick}>Decline</Button>
          </span>
          <span style={{ float: 'right' }}>
            <Button size="large" type="cta" onClick={this.onConfirmDeleteClick}>Confirm</Button>
          </span>
        </div>
      </Modal>,
      <div className="page">
        <SectionHeader title="Call logs" dividing />
        <Checkbox label="Live updates" value={liveUpdates} onChange={onToggleLiveUpdates} />
        <table className="table">
          <colgroup>
            <col width="1%" />
            <col width="10%" />
            <col width="10%" />
            <col width="20%" />
            <col width="10%" />
            <col width="10%" />
            <col width="5%" />
            <col width="5%" />
            <col width="5%" />
            <col width="5%" />
            <col width="1%" />
            <col width="6%" />
          </colgroup>
          <thead>
            <tr>
              <th>ID</th>
              <th>Date</th>
              <th>User</th>
              <th>Callee(s)</th>
              <th>From Number</th>
              <th>To Number</th>
              <th>Ticket</th>
              <th>Type</th>
              <th>Duration</th>
              <th>Cost</th>
              <th>Status</th>
              <th>Record</th>
            </tr>
          </thead>
          <tbody>
            {calls.toArray().map((call, index) => {
              const ticketId = call.get('ticket');
              const externalNumber = call.get('external_number');
              const isInbound = call.get('type') === 'inbound';
              const number = numbers.get(call.get('number')) || Immutable.fromJS({});
              const onOpen = (event) => {
                event.preventDefault();
                onOpenCallLog(call.get('id'));
              };

              const recordings = call.get('recordings');
              const recordingsEnabled = recordings.filter(recording => recording.get('blob'));
              const agentVoicemail = call.getIn(['agent_voicemail', 'blob']);

              return (
                <tr key={`call_log_${index}`}>
                  <td className="alt dp-id-col">
                    <a onClick={onOpen}>
                      <em className="dp-id">{call.get('id')}</em>
                    </a>
                  </td>
                  <td className="overflow-ellipsis">
                    {moment(call.get('date_created')).format('L LT')}
                  </td>
                  <td className="overflow-ellipsis">
                    <a onClick={() => openPerson(call.get('person'))}>
                      <PersonName id={call.get('person')} />
                    </a>
                  </td>
                  <td className="overflow-ellipsis">
                    {call.get('participants').toArray().map((participant, pindex) =>
                      <a key={pindex} className="list-item" onClick={() => openPerson(participant.get('person'))}>
                        <PersonName id={participant.get('person')} />
                      </a>
                    )}
                  </td>
                  <td>
                    {isInbound
                      ? <button onClick={() => openDialpad(externalNumber)}>
                        {externalNumber}
                      </button>
                      : <span x-ms-format-detection="none">{number.get('number')}</span>
                    }
                  </td>
                  <td>
                    {isInbound
                      ? <span x-ms-format-detection="none">{number.get('number')}</span>
                      : <button onClick={() => openDialpad(externalNumber)}>
                        {externalNumber}
                      </button>
                    }
                  </td>
                  <td className="overflow-ellipsis">
                    {ticketId
                      ? <span>
                        <i className="fa fa-envelope" />
                        &nbsp;
                        <a onClick={() => openTicket(ticketId)}>
                          {ticketId}
                        </a>
                      </span>
                      : '-'}
                  </td>
                  <td className="overflow-ellipsis">
                    {call.get('type')}
                  </td>
                  <td className="overflow-ellipsis">
                    <CallDuration call={call}  />
                  </td>
                  <td className="overflow-ellipsis">
                    {call.get('cost') ? `${call.get('cost')} ${call.get('cost_currency') !== null ? call.get('cost_currency') : ''}` : '-'}
                  </td>
                  <CallStatus call={call} />
                  <td>
                    {!recordingsEnabled.size && !agentVoicemail && '-'}
                    {recordingsEnabled.size > 0 && recordingsEnabled.map(recording => <BlobPlayButton key={`call_log_record_play_${index}`} iconOnly value={recording.get('blob')} />)}
                    {agentVoicemail && <BlobPlayButton key={`call_log_record_play_${index}`} iconOnly value={agentVoicemail} />}
                    {recordingsEnabled.size > 0 ? <DeleteButton key={`call_log_record_delete_${index}`}  iconOnly onClick={() => this.onDeleteClick(call.get('id'))} /> : null}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>

        <ReactPaginate
          previousLabel="previous"
          nextLabel="next"
          breakLabel={<a href="">...</a>}
          breakClassName="break-me"
          pageCount={pageCount}
          marginPagesDisplayed={2}
          pageRangeDisplayed={5}
          onPageChange={onPageChange}
          containerClassName="pagination"
          subContainerClassName="pages pagination"
          activeClassName="active"
        />
      </div>]);
  }
}

export default CallLogsList;
