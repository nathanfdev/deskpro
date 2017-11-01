import PropTypes from 'prop-types';
import React from 'react';
import ScrollArea from 'react-scrollbar';
import Immutable from 'immutable';
import classNames from 'classnames';
import Avatar from '../../Common/Avatar';

class Queues extends React.Component {

  static propTypes = {
    target:  PropTypes.object,
    agents:  PropTypes.object,
    queues:  PropTypes.object,
    onClick: PropTypes.func
  };

  onSelect = (queue) => {
    this.props.onClick(queue);
  };

  render() {
    const { queues, agents, target } = this.props;

    return (
      <ScrollArea className="voice-queue-list">
        {queues.map((queue, index) =>
          <QueueItem
            key={index}
            agents={agents}
            queue={queue}
            active={queue === target}
            onClick={this.onSelect}
          />
        )}
      </ScrollArea>
    );
  }
}

class QueueItem extends React.Component {

  static propTypes = {
    agents:  PropTypes.object,
    queue:   PropTypes.object,
    active:  PropTypes.bool,
    onClick: PropTypes.func
  };

  onClick = () => {
    const { queue, onClick } = this.props;
    onClick(queue);
  };

  render() {
    const { agents, queue, active } = this.props;
    const queueAgentIds = queue.get('agents') || Immutable.fromJS([]);
    const queueAgents = agents.filter(agent => queueAgentIds.contains(agent.get('id')));

    return (
      <div
        className={classNames('queue-item', { active })}
        onClick={this.onClick}
      >
        <div className="queue-name">
          {queue.get('name')}
        </div>
        <div className="queue-agents">
          {queueAgents.size} agents
          {queueAgents.map((agent, index) => <Avatar key={index} person={agent} size={20} />)}
        </div>
        <div className="queue-users">
          3 users in queue (average wait 2m)
        </div>
      </div>
    );
  }
}

export default Queues;
