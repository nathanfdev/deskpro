import React, { PropTypes } from 'react';
import ReactPaginate from 'react-paginate';
import moment from 'moment';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import PersonName from '../../../../Common/Components/PersonName';
import CallStatus from '../Common/CallStatus';
import CallDuration from '../Common/CallDuration';

class CallLogsList extends React.Component {

  static propTypes = {
    calls:         PropTypes.object,
    numbers:       PropTypes.object,
    pageCount:     PropTypes.number,
    onPageChange:  PropTypes.func,
    onOpenCallLog: PropTypes.func
  };

  render() {
    const { calls, numbers, pageCount, onPageChange, onOpenCallLog } = this.props;

    return (
      <div className="page">
        <SectionHeader title="Call logs" dividing />

        <table className="table">
          <colgroup>
            <col width="1%" />
            <col width="10%" />
            <col width="10%" />
            <col width="25%" />
            <col width="10%" />
            <col width="5%" />
            <col width="5%" />
            <col width="5%" />
            <col width="1%" />
          </colgroup>
          <thead>
            <tr>
              <th>ID</th>
              <th>Date</th>
              <th>Caller</th>
              <th>Callee(s)</th>
              <th>Number</th>
              <th>Ticket</th>
              <th>Type</th>
              <th>Duration</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {calls.map((call, index) => {
              const ticketId = call.get('ticket');
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
                    {call.get('participants').map((participant, pindex) =>
                      <PersonName id={participant.get('person')} className="list-item" key={pindex} />
                    )}
                  </td>
                  <td className="overflow-ellipsis">
                    {numbers.getIn([call.get('number'), 'number'])}
                  </td>
                  <td className="overflow-ellipsis">
                    <i className="fa fa-envelope" />
                    &nbsp;
                    <a href={`../agent/#t:${ticketId}`} target="_blank" rel="noopener noreferrer">
                      {ticketId}
                    </a>
                  </td>
                  <td className="overflow-ellipsis">
                    {call.get('type')}
                  </td>
                  <td className="overflow-ellipsis">
                    <CallDuration call={call}  />
                  </td>
                  <CallStatus call={call} />
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
