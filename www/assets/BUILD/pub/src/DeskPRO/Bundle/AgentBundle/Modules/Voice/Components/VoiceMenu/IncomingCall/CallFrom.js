import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Timer from 'DeskPRO/Component/Timer';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import Avatar from '../../Common/Avatar';

class CallFrom extends React.Component {

  static propTypes = {
    reservation: PropTypes.object
  };

  render() {
    const { reservation } = this.props;
    const attributes = reservation && reservation.task ? reservation.task.attributes : {};
    const number = attributes.from;
    const person = null;

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
        {person &&
          <div className="call-from-group">
            ACME Group
          </div>}
      </div>
    );
  }
}

@connect(state => ({
  agents: agentsSelector(state)
}))
class CallFromContainer extends React.Component {

  render() {
    return <CallFrom {...this.props} />;
  }
}

export default CallFromContainer;
