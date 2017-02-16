import React, { PropTypes } from 'react';
import moment from 'moment';
import Immutable from 'immutable';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import MediaControls from 'DeskPRO/Component/MediaControls';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import PersonName from '../../../../Common/Components/PersonName';
import CallStatus from '../Common/CallStatus';
import CallDuration from '../Common/CallDuration';

class CallLogView extends React.Component {

  static propTypes = {
    call:         PropTypes.object,
    numbers:      PropTypes.object,
    people:       PropTypes.object,
    onReturnBack: PropTypes.func
  };

  render() {
    const { onReturnBack, call, numbers, people } = this.props;

    return (
      <div className="page">
        <BackButton onClick={onReturnBack} />
        <SectionHeader title="Call log" dividing />

        <table className="table">
          <tbody>
            <tr>
              <th width="140">ID</th>
              <td>{call.get('id')}</td>
            </tr>
            <tr>
              <th>Date</th>
              <td>{moment(call.get('date_created')).format('L LT')}</td>
            </tr>
            <tr>
              <th>Status</th>
              <CallStatus call={call} showDateEnded />
            </tr>
            <tr>
              <th>Caller</th>
              <td>
                <PersonName id={call.get('person')} />
              </td>
            </tr>
            <tr>
              <th>Callee(s)</th>
              <td>
                {call.get('participants').map((participant, index) =>
                  <PersonName id={participant.get('person')} className="list-item" key={index} />
                )}
              </td>
            </tr>
            <tr>
              <th>Number</th>
              <td>
                {numbers.getIn([call.get('number'), 'number'])}
              </td>
            </tr>
            <tr>
              <th>Type</th>
              <td>
                {call.get('type')}
              </td>
            </tr>
            <tr>
              <th>Duration</th>
              <td>
                <CallDuration call={call} />
              </td>
            </tr>
            <tr>
              <th>Ticket</th>
              <td>
                <i className="fa fa-envelope" />
                &nbsp;
                <a href={`../agent/#t:${call.get('ticket')}`} target="_blank" rel="noopener noreferrer">
                  {call.get('ticket')}
                </a>
              </td>
            </tr>
            <tr>
              <th>Twilio data</th>
              <td>
                <table>
                  <tbody>
                    {call.get('data').map((value, key) =>
                      <tr key={key}>
                        <th width="140">{key}</th>
                        <td>{value}</td>
                      </tr>
                    )}
                  </tbody>

                </table>
              </td>
            </tr>
            <tr>
              <th>Call log</th>
              <td>
                <table>
                  <tbody>
                    {call.get('phone_call_logs').map((log, index) => {
                      const person = people.get(log.get('person')) || Immutable.fromJS({});

                      return (
                        <tr key={index}>
                          <td width="80">[{moment(log.get('date_created')).format('hh:mm:ss')}]</td>
                          <td>
                            {agentPhrases.get(`agent.voice.${log.get('action_type').replace(/\.+/, '_')}`, {
                              '{number}':       call.get('external_number'),
                              '{person_name}':  person.get('first_name') || '',
                              '{person_email}': person.get('primary_email') || ''
                            })}
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </td>
            </tr>
            <tr>
              <th>Recording</th>
              <td>{call.get('recording') ? <MediaControls recording={call.get('recording')} /> : '-'}</td>
            </tr>
          </tbody>
        </table>
      </div>
    );
  }
}

export default CallLogView;
