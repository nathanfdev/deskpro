import React, { PropTypes } from 'react';
import { Toggle } from 'DeskPRO/Component/Semantic/Form';
import Immutable from 'immutable';
import ScrollArea from 'react-scrollbar';
import Avatar from '../../Common/Avatar';

class Queues extends React.Component {

  static propTypes = {
    agents:   PropTypes.object,
    queues:   PropTypes.object,
    me:       PropTypes.object,
    onChange: PropTypes.func,
    saving:   PropTypes.bool
  };

  onToggleQueue = (queue) => {
    const { me, onChange } = this.props;
    const agentId = me.get('id');

    let agents = queue.get('agents') || Immutable.fromJS([]);
    if (agents.contains(agentId)) {
      agents = agents.splice(agents.indexOf(agentId), 1);
    } else {
      agents = agents.push(agentId);
    }

    onChange(queue, agents.toJS());
  };

  render() {
    const { agents, me, queues = Immutable.fromJS({}), saving } = this.props;

    if (!queues.size) {
      return (
        <div className="voice-queue-list">
          <div className="empty-message">
            No queues exist.
          </div>
        </div>
      );
    }

    return (
      <ScrollArea className="voice-queue-list">
        {queues.map((queue, index) =>
          <QueueItem
            key={index}
            agents={agents}
            queue={queue}
            active={queue.get('agents').contains(me.get('id'))}
            onChange={this.onToggleQueue}
            saving={saving}
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
    onChange: PropTypes.func,
    saving:   PropTypes.bool
  };

  onClick = () => {
    const { queue, onChange } = this.props;
    onChange(queue);
  };

  render() {
    const { agents, queue, active, saving } = this.props;
    const queueAgentIds = queue.get('agents') || Immutable.fromJS([]);
    const queueAgents = agents.filter(agent => queueAgentIds.contains(agent.get('id')));

    return (
      <div className="queue-item">
        <Toggle
          disabled={saving}
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
        {/* <div className="queue-users">
          3 users in queue (average wait 2m)
        </div> */}
      </div>
    );
  }
}

export default Queues;
