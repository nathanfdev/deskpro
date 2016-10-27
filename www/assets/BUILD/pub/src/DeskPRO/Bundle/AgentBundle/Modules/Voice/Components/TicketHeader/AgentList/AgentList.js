import React, { PropTypes } from 'react';
import ScrollArea from 'react-scrollbar-versioned';
import classNames from 'classnames';
import Avatar from '../../Common/Avatar';

class Agents extends React.Component {

  static propTypes = {
    target:  PropTypes.object,
    agents:  PropTypes.object,
    onClick: PropTypes.func
  };

  static defaultProps = {
    onClick: () => {}
  };

  onSelect = (agent) => {
    this.props.onClick(agent);
  };

  render() {
    const { agents, target } = this.props;

    return (
      <ScrollArea className="voice-agent-list">
        {agents.map((agent, index) =>
          <Agent
            key={index}
            agent={agent}
            active={agent === target}
            onClick={this.onSelect}
          />
        )}
      </ScrollArea>
    );
  }
}

class Agent extends React.Component {

  static propTypes = {
    agent:   PropTypes.object,
    active:  PropTypes.bool,
    onClick: PropTypes.func
  };

  onClick = (event) => {
    event.preventDefault();

    const { agent, onClick } = this.props;
    onClick(agent);
  };

  render() {
    const { agent, active } = this.props;

    return (
      <div
        className={classNames('voice-agent-list-item', { active })}
        onClick={this.onClick}
      >
        <Avatar person={agent} size={24} />
        <span className="agent-name">
          {agent.get('name')}
        </span>
      </div>
    );
  }
}

export default Agents;
