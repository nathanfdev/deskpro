import PropTypes from 'prop-types';
import React from 'react';
import ReactPaginate from 'react-paginate';
import moment from 'moment';
import Immutable from 'immutable';
import { BlobPlayButton } from 'DeskPRO/Component/AudioWidget/PlayButton';
import { Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import PersonName from '../../../../Common/Components/PersonName';
import CallStatus from '../Common/CallStatus';
import CallDuration from '../Common/CallDuration';
import { replaceRoute } from '../../../../../Services/history';

class CallLogsList extends React.Component {

  static propTypes = {
    calls:               PropTypes.object,
    numbers:             PropTypes.object,
    pageCount:           PropTypes.number,
    liveUpdates:         PropTypes.bool,
    onPageChange:        PropTypes.func,
    onOpenCallLog:       PropTypes.func,
    openDialpad:         PropTypes.func,
    onToggleLiveUpdates: PropTypes.func
  };

  static openTicket(ticketId) {
    if (window.parent || window.parent.DP_FRAME_OVERLAYS || window.parent.DP_FRAME_OVERLAYS.admin) {
      replaceRoute(`/go_to_agent/#agent/tickets/${ticketId}`);
    } else {
      replaceRoute(`/go_to_agent/#agent/go/ticket/${ticketId}`);
    }
  }

  render() {
    const { calls, numbers, pageCount, liveUpdates } = this.props;
    const { onPageChange, onOpenCallLog, openDialpad, onToggleLiveUpdates } = this.props;

    return (
      <div className="page">
        <SectionHeader title="Call logs" dividing />

        <Checkbox label="Live updates" value={liveUpdates} onChange={onToggleLiveUpdates} />
        <table className="table">
          <colgroup>
            <col width="1%" />
            <col width="10%" />
            <col width="10%" />
            <col width="25%" />
            <col width="10%" />
            <col width="10%" />
            <col width="5%" />
            <col width="5%" />
            <col width="5%" />
            <col width="1%" />
            <col width="1%" />
          </colgroup>
          <thead>
            <tr>
              <th>ID</th>
              <th>Date</th>
              <th>Caller</th>
              <th>Callee(s)</th>
              <th>From Number</th>
              <th>To Number</th>
              <th>Ticket</th>
              <th>Type</th>
              <th>Duration</th>
              <th>Status</th>
              <th>Record</th>
            </tr>
          </thead>
          <tbody>
            {calls.toArray().map((call, index) => {
              const ticketId = call.get('ticket');
              const fromNumber = call.getIn(['data', 'From']);
              const toNumber = call.getIn(['data', 'To']);
              const isInbound = call.get('type') === 'inbound';
              const number = numbers.get(call.get('number')) || Immutable.fromJS({});
              const onOpen = (event) => {
                event.preventDefault();
                onOpenCallLog(call.get('id'));
              };

              return (
                <tr key={index}>
                  <td className="alt dp-id-col">
                    <a onClick={onOpen}>
                      <em className="dp-id">{call.get('id')}</em>
                    </a>
                  </td>
                  <td className="overflow-ellipsis">
                    {moment(call.get('date_created')).format('L LT')}
                  </td>
                  <td className="overflow-ellipsis">
                    <PersonName id={call.get('person')} />
                  </td>
                  <td className="overflow-ellipsis">
                    {call.get('participants').toArray().map((participant, pindex) =>
                      <PersonName id={participant.get('person')} className="list-item" key={pindex} />
                    )}
                  </td>
                  <td>
                    {isInbound
                      ? <button onClick={() => openDialpad(fromNumber)}>
                        {fromNumber}
                      </button>
                      : number.get('number')
                    }
                  </td>
                  <td>
                    {isInbound
                      ? number.get('number')
                      : <button onClick={() => openDialpad(toNumber)}>
                        {toNumber}
                      </button>
                    }
                  </td>
                  <td className="overflow-ellipsis">
                    {ticketId
                      ? <span>
                        <i className="fa fa-envelope" />
                        &nbsp;
                        <a onClick={() => CallLogsList.openTicket(ticketId)}>
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
                  <CallStatus call={call} />
                  <td>
                    {call.get('recording') ?
                      <BlobPlayButton iconOnly value={call.get('recording')} /> : '-'}
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
      </div>
    );
  }
}

export default CallLogsList;
