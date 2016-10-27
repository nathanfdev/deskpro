import React, { PropTypes } from 'react';
import { Toggle } from 'DeskPRO/Component/Semantic/Form';
import Immutable from 'immutable';
import ScrollArea from 'react-scrollbar-versioned';
import Avatar from '../../Common/Avatar';

class Queues extends React.Component {

  static propTypes = {
    agents:   PropTypes.object,
    queues:   PropTypes.object,
    value:    PropTypes.array,
    onChange: PropTypes.func
  };

  onToggleQueue = (queue) => {
    const { value = [], onChange } = this.props;
    const queueId = queue.get('id');

    const newValue = [...value];
    if (newValue.indexOf(queueId) === -1) {
      newValue.push(queueId);
    } else {
      newValue.splice(newValue.indexOf(queueId), 1);
    }

    onChange(newValue);
  };

  render() {
    const { agents, value = [], queues = Immutable.fromJS({}) } = this.props;

    return (
      <ScrollArea className="voice-queue-list">
        {queues.map((queue, index) =>
          <QueueItem
            key={index}
            agents={agents}
            queue={queue}
            active={value.indexOf(queue.get('id')) !== -1}
            onChange={this.onToggleQueue}
          />
        )}
      </ScrollArea>
    );
  }
}

class QueueItem extends React.Component {

  static propTypes = {
    agents:   PropTypes.object,
    queue:    PropTypes.object,
    active:   PropTypes.bool,
    onChange: PropTypes.func
  };

  onClick = () => {
    const { queue, onChange } = this.props;
    onChange(queue);
  };

  render() {
    const { agents, queue, active } = this.props;
    const queueAgentIds = queue.get('agents') || Immutable.fromJS([]);
    const queueAgents = agents.filter(agent => queueAgentIds.contains(agent.get('id')));

    return (
      <div className="queue-item">
        <Toggle
          className="small"
          active={active}
          onChange={this.onClick}
        />

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
