import PropTypes from 'prop-types';
import React from 'react';
import Avatar from '../../Common/Avatar';

class CallToAgent extends React.Component {

  static propTypes = {
    agent: PropTypes.object
  };

  render() {
    const { agent } = this.props;

    return (
      <div className="call-to">
        <Avatar person={agent} size={60} />
        <div className="call-to-name">
          {agent && agent.get('name')}
        </div>
      </div>
    );
  }
}

class CallToQueue extends React.Component {

  render() {
    return (
      <div className="call-to">
        <div className="call-to-name">
          <i className="fa fa-tasks" />
          IT Support
        </div>
        <div className="agents-available">
          3 agents available
        </div>
        <div className="users-in-queue">
          2 user in queue (average wait 30s)
        </div>
      </div>
    );
  }
}

class CallTarget extends React.Component {

  static propTypes = {
    target: PropTypes.object
  };

  render() {
    const { target } = this.props;
    const type = target && target.type;

    if (type === 'queue') {
      return <CallToQueue target={target} />;
    } else if (type === 'agent') {
      return <CallToAgent agent={target.agent} />;
    }

    return null;
  }
}

export default CallTarget;
