import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';
import Immutable from 'immutable';
import { FormattedMessage } from 'react-intl';
import MediaControls from 'DeskPRO/Component/MediaControls';
import Duration from 'DeskPRO/Component/Duration';
import { faPhone } from '@fortawesome/free-solid-svg-icons';
import { Icon } from '@deskpro/react-components';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import PersonName from '../../../../Common/Components/PersonName';
import CallStatus from '../Common/CallStatus';
import CallDuration from '../Common/CallDuration';
import { openTicket, openPerson, openTarget } from '../../../../../Services/history';

class CallLogView extends React.Component {

  static propTypes = {
    call:         PropTypes.object,
    numbers:      PropTypes.object,
    people:       PropTypes.object,
    onReturnBack: PropTypes.func,
    openDialpad:  PropTypes.func
  };

  openDialpad = (event, number) => {
    event.preventDefault();
    event.stopPropagation();

    this.props.openDialpad(number);
  };

  render() {
    const { onReturnBack, call, numbers, people } = this.props;
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
                <a onClick={() => openPerson(call.get('person'))}>
                  <PersonName id={call.get('person')} />
                </a>
              </td>
            </tr>
            <tr>
              <th>Callee(s)</th>
              <td>
                {call.get('participants').toArray().map((participant, index) =>
                  <a key={index} className="list-item" onClick={() => openPerson(participant.get('person'))}>
                    <PersonName id={participant.get('person')} />
                  </a>
                )}
              </td>
            </tr>
            <tr>
              <th>From Number</th>
              <td>
                {isInbound
                  ? <button onClick={event => this.openDialpad(event, fromNumber)}>
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
                  : <button onClick={event => this.openDialpad(event, toNumber)}>
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
                <a onClick={() => openTicket(call.get('ticket'))}>
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

                      let target = 'Unknown';
                      if (log.getIn(['details', 'target'])) {
                        target = (
                          <a onClick={() => openTarget(log.getIn(['details', 'target']))}>
                            {log.getIn(['details', 'target', 'name'])}
                          </a>
                        );
                      }

                      const time = log.get('action_type') === 'call.recording_deleted'
                        ? moment(log.get('date_created')).format('YYYY-MM-DD H:mm:ss')
                        : <Duration value={duration} />;

                      return (
                        <tr key={index}>
                          <td width="100">
                            [{time}]
                          </td>
                          <td>
                            <FormattedMessage
                              id={`agent.voice.${log.get('action_type').replace(/\.+/, '_')}`}
                              values={{
                                number: (
                                  <a
                                    onClick={event => this.openDialpad(event, call.get('external_number'))}
                                    href={`tel:${call.get('external_number')}`}
                                  >
                                    {call.get('external_number')} <Icon name={faPhone} />
                                  </a>
                                ),
                                person: (
                                  <a data-route={`person:/agent/people/${person.get('id')}`}>
                                    {person.get('name')} {person.get('primary_email') ? `( ${person.get('primary_email')} )` : ''}
                                  </a>
                                ),
                                to_number:        number.get('nickname') || number.get('number'),
                                key:              log.getIn(['details', 'Digits']) || '',
                                target,
                                forwarded_number: log.getIn(['details', 'forwarded_number']) || ''
                              }}
                            />
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
