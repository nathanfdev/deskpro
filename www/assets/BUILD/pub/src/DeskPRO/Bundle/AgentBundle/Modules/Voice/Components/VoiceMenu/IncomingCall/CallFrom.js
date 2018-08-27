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

  constructor(props) {
    super(props);
    this.state = {
      selectedPerson: null
    };
  }

  selectPerson = (selectedPerson) => {
    this.setState({ selectedPerson });
  };

  render() {
    const { incomingCall, people } = this.props;
    let { selectedPerson } = this.state;
    const possibleCallerPeople = [];

    let person;
    let number;

    if (incomingCall && incomingCall.get('task')) {
      // twilio reservation props
      number = incomingCall.get('number');

      if (incomingCall.get('related_people_ids')) {
        incomingCall.get('related_people_ids').forEach((id) => {
          possibleCallerPeople.push(people.get(id));
        });
      } else if (incomingCall.get('caller_person_id')) {
        person = people.get(incomingCall.get('caller_person_id'));
        possibleCallerPeople.push(person);
      }
    } else {
      // participant invite props
      number = incomingCall.get('number');

      if (incomingCall.get('caller_person_id')) {
        person = people.get(incomingCall.get('caller_person_id'));
        possibleCallerPeople.push(person);
      }
    }

    if (!selectedPerson && possibleCallerPeople.length) {
      selectedPerson = possibleCallerPeople[possibleCallerPeople.length - 1];
    }

    return (
      <div className="call-from">
        <div className="call-from-number">
          {number || 'Unknown number'}
        </div>
        <div className="call-from-avatars">
          {possibleCallerPeople.map((possiblePerson, key) =>
            <a
              onClick={() => this.selectPerson(possiblePerson)}
              className={classNames('call-from-avatar', { selected: possiblePerson === selectedPerson })}
              style={{ marginLeft: -possibleCallerPeople.length * 30 / 4 + 30 * key }}
            >
              <Avatar key={key} person={possiblePerson} size={60} />
            </a>
          )}
        </div>
        {selectedPerson &&
          <div className="call-from-name">
            {selectedPerson.get('name')}
          </div>}
        <div className="call-waiting-time">
          waiting <Timer format="waiting_time" />
        </div>
        {person &&
          <div className="call-from-email">
            {selectedPerson.get('primary_email')}
          </div>}
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
