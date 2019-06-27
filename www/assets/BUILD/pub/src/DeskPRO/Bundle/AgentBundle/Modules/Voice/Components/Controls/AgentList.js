import PropTypes from 'prop-types';
import React from 'react';
import ScrollArea from 'react-scrollbar';
import classNames from 'classnames';
import Avatar from '../Common/Avatar';

class AgentList extends React.Component {

  static propTypes = {
    target:             PropTypes.object,
    agents:             PropTypes.object,
    onlineAgentIds:     PropTypes.object,
    forwardingAgentIds: PropTypes.object,
    busyAgentIds:       PropTypes.array,
    phoneCall:          PropTypes.object,
    onClick:            PropTypes.func,
    transferDisabled:   PropTypes.bool
  };

  static defaultProps = {
    onClick:      () => {},
    participants: []
  };

  onSelect = (agent) => {
    this.props.onClick(agent);
  };

  render() {
    const { agents, onlineAgentIds, forwardingAgentIds, busyAgentIds, target, phoneCall, transferDisabled } = this.props;

    return (
      <ScrollArea className="voice-agent-list">
        {agents.filter(agent =>
            onlineAgentIds.contains(agent.get('id')) || forwardingAgentIds.contains(agent.get('id'))
          )
          .toArray()
          .map((agent, index) =>
            <Agent
              transferDisabled={transferDisabled}
              key={index}
              agent={agent}
              busy={busyAgentIds.contains(agent.get('id'))}
              active={target && agent === target.target || busyAgentIds.contains(agent.get('id'))}
              participant={phoneCall.get('agent_participants').contains(agent.get('id'))}
              onClick={this.onSelect}
              online={onlineAgentIds.contains(agent.get('id'))}
              forwarding={forwardingAgentIds.contains(agent.get('id'))}
            />
        )}
      </ScrollArea>
    );
  }
}

class Agent extends React.Component {

  static propTypes = {
    transferDisabled: PropTypes.bool,
    agent:            PropTypes.object,
    active:           PropTypes.bool,
    online:           PropTypes.bool,
    forwarding:       PropTypes.bool,
    busy:             PropTypes.bool,
    participant:      PropTypes.bool,
    onClick:          PropTypes.func
  };

  onClick = (event) => {
    event.preventDefault();

    const { agent, participant, onClick, transferDisabled } = this.props;

    // already in conference, skipping
    if (participant || transferDisabled) {
      return;
    }

    onClick(agent);
  };

  render() {
    const { agent, active, online, busy, forwarding, participant } = this.props;

    return (
      <div
        className={classNames('voice-agent-list-item', { active, participant })}
        onClick={this.onClick}
      >
        <Avatar
          person={agent}
          size={24}
          online={online}
          forwarding={forwarding}
          withOnlineStatus
        />
        <span className="agent-name">
          {agent.get('name')}
          {participant && <span className="agent-participant">(participant)</span>}
          {!participant && busy && <span className="agent-participant">(busy)</span>}
        </span>
      </div>
    );
  }
}

export default AgentList;
