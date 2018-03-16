import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';
import Immutable from 'immutable';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import MediaControls from 'DeskPRO/Component/MediaControls';
import Duration from 'DeskPRO/Component/Duration';
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
    onReturnBack: PropTypes.func,
    openDialpad:  PropTypes.func
  };

  render() {
    const { onReturnBack, openDialpad, call, numbers, people } = this.props;
    const number = numbers.get(call.get('number')) || Immutable.fromJS({});
    const fromNumber = call.getIn(['data', 'From']);
    const toNumber = call.getIn(['data', 'To']);
    const isInbound = call.get('type') === 'inbound';
    const rawData = call.get('data').toJS();

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
                {call.get('participants').toArray().map((participant, index) =>
                  <PersonName id={participant.get('person')} className="list-item" key={index} />
                )}
              </td>
            </tr>
            <tr>
              <th>From Number</th>
              <td>
                {isInbound
                  ? <button onClick={() => openDialpad(fromNumber)}>
                    {fromNumber}
                  </button>
                  : number.get('number')
                }
              </td>
            </tr>
            <tr>
              <th>To Number</th>
              <td>
                {isInbound
                  ? number.get('number')
                  : <button onClick={() => openDialpad(toNumber)}>
                    {toNumber}
                  </button>
                }
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
                    {Object.keys(rawData).map(key =>
                      <tr key={key}>
                        <th width="140">{key}</th>
                        <td>{rawData[key]}</td>
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
                    {call.get('phone_call_logs').toArray().map((log, index) => {
                      const person = people.get(log.get('person')) || Immutable.fromJS({});
                      const logDate  = moment(log.get('date_created'));
                      const callDate = moment(call.get('date_created'));
                      const duration = logDate.unix() - callDate.unix();

                      return (
                        <tr key={index}>
                          <td width="80">
                            [<Duration value={duration} />]
                          </td>
                          <td>
                            {agentPhrases.get(`agent.voice.${log.get('action_type').replace(/\.+/, '_')}`, {
                              '{number}':       call.get('external_number'),
                              '{to_number}':    number.get('nickname') || number.get('number'),
                              '{person_name}':  person.get('first_name') || '',
                              '{person_email}': person.get('primary_email') || '',
                              '{key}':          log.getIn(['details', 'Digits']) || '',
                              '{target_name}':  log.getIn(['details', 'target_name']) || 'Unknown'
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
