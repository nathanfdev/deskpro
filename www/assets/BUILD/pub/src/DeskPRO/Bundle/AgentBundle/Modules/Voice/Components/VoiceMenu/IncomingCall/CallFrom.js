import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
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

    let person;
    let number;

    if (incomingCall && incomingCall.task) {
      // twilio reservation props
      const attributes = incomingCall && incomingCall.task ? incomingCall.task.attributes : {};
      number = attributes.from;

      if (attributes.deskpro_person_id) {
        person = people.get(attributes.deskpro_person_id);
      }
    } else {
      // participant invite props
      number = incomingCall.get('number');

      if (incomingCall.get('caller_person_id')) {
        person = people.get(incomingCall.get('caller_person_id'));
      }
    }

    return (
      <div className="call-from">
        <div className="call-from-number">
          {number || 'Unknown number'}
        </div>
        <Avatar person={person} size={60} />
        {person &&
          <div className="call-from-name">
            {person.get('name')}
          </div>}
        <div className="call-waiting-time">
          waiting <Timer format="waiting_time" />
        </div>
        {person &&
          <div className="call-from-email">
            {person.get('primary_email')}
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
