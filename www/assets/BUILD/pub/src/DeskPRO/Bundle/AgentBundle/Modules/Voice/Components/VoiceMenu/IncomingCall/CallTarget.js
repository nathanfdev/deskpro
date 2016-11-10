import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import Avatar from '../../Common/Avatar';

@connect(state => ({
  agents: agentsSelector(state)
}))
class CallToAgentContainer extends React.Component {

  render() {
    return <CallToAgent {...this.props} />;
  }
}

class CallToAgent extends React.Component {

  static propTypes = {
    target: PropTypes.object,
    agents: PropTypes.object
  };

  render() {
    const { agents, target } = this.props;
    const agentId = target && target.agent;
    const agent = agents && agentId > 0 && agents.get(agentId);

    return (
      <div  className="call-to">
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
      <div  className="call-to">
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
      return <CallToAgentContainer target={target} />;
    }

    return null;
  }
}

export default CallTarget;
