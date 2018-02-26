import PropTypes from 'prop-types';
import React from 'react';
import ScrollArea from 'react-scrollbar';
import classNames from 'classnames';
import Avatar from '../Common/Avatar';

class AgentList extends React.Component {

  static propTypes = {
    target:       PropTypes.object,
    agents:       PropTypes.object,
    participants: PropTypes.array,
    onClick:      PropTypes.func
  };

  static defaultProps = {
    onClick:      () => {},
    participants: []
  };

  onSelect = (agent) => {
    this.props.onClick(agent);
  };

  render() {
    const { agents, target, participants } = this.props;

    return (
      <ScrollArea className="voice-agent-list">
        {agents.map((agent, index) =>
          <Agent
            key={index}
            agent={agent}
            active={agent === target}
            participant={participants.indexOf(agent.get('id')) !== -1}
            onClick={this.onSelect}
          />
        )}
      </ScrollArea>
    );
  }
}

class Agent extends React.Component {

  static propTypes = {
    agent:       PropTypes.object,
    active:      PropTypes.bool,
    participant: PropTypes.bool,
    onClick:     PropTypes.func
  };

  onClick = (event) => {
    event.preventDefault();

    const { agent, participant, onClick } = this.props;

    // already in conference, skipping
    if (participant) {
      return;
    }

    onClick(agent);
  };

  render() {
    const { agent, active, participant } = this.props;

    return (
      <div
        className={classNames('voice-agent-list-item', { active, participant })}
        onClick={this.onClick}
      >
        <Avatar person={agent} size={24} />
        <span className="agent-name">
          {agent.get('name')}
          {participant && <span className="agent-participant">(participant)</span>}
        </span>
      </div>
    );
  }
}

export default AgentList;
