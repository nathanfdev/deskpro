import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import Timer from 'DeskPRO/Component/Timer';
import { voiceAgentsSelector } from '../../../Selectors/agents';
import Avatar from '../../Common/Avatar';

class CallFrom extends React.Component {

  static propTypes = {
    people:       PropTypes.object,
    incomingCall: PropTypes.object
  };

  render() {
    const { incomingCall, people } = this.props;
    const possibleCallerPeople = [];
    let unknownCaller;

    let person;
    let number;

    if (incomingCall && incomingCall.get('task')) {
      // twilio reservation props
      number = incomingCall.get('number');

      if (incomingCall.get('related_people_ids')) {
        incomingCall.get('related_people_ids').forEach((id) => {
          person = people.get(id);
          if (person && (person.get('name') || person.get('primary_email'))) {
            possibleCallerPeople.push(person);
          } else if (person && !person.get('name') && !person.get('primary_email')) {
            unknownCaller = person;
          }
        });
      } else if (incomingCall.get('caller_person_id')) {
        person = people.get(incomingCall.get('caller_person_id'));
        if (person && (person.get('name') || person.get('primary_email'))) {
          possibleCallerPeople.push(person);
        } else if (person && !person.get('name') && !person.get('primary_email')) {
          unknownCaller = person;
        }
      }
    } else {
      // participant invite props
      number = incomingCall.get('number');

      if (incomingCall.get('caller_person_id')) {
        person = people.get(incomingCall.get('caller_person_id'));
        if (person && (person.get('name') || person.get('primary_email'))) {
          possibleCallerPeople.push(person);
        } else if (person && !person.get('name') && !person.get('primary_email')) {
          unknownCaller = person;
        }
      }
    }
    const finalPeople = unknownCaller ? possibleCallerPeople.slice(0, 4) : possibleCallerPeople.slice(0, 5);
    if (unknownCaller) {
      finalPeople.push(unknownCaller);
    }

    return (
      <div className="call-from">
        <div className="call-from-number">
          {number || 'Unknown number'}
        </div>
        <div className={classNames({ 'call-from-avatars': possibleCallerPeople.length > 1 })}>
          {finalPeople.map((possiblePerson, key) =>
            <div className="call-from-avatar">
              <Avatar key={key} person={possiblePerson} size={60} />

              <div className="call-from-details">
                <div className="call-from-name">
                  {possiblePerson.get('name') || 'Unknown Caller'}
                </div>
                <div className="call-from-email">
                  {possiblePerson.get('primary_email')}
                </div>
              </div>
            </div>
          )}
        </div>
        <div className="call-waiting-time">
          waiting <Timer format="waiting_time" />
        </div>
      </div>
    );
  }
}

@connect(state => ({
  agents: voiceAgentsSelector(state)
}))
class CallFromContainer extends React.Component {

  render() {
    return <CallFrom {...this.props} />;
  }
}

export default CallFromContainer;
