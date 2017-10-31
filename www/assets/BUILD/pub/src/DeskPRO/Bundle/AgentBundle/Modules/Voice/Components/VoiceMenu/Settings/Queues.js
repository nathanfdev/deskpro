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

  render() {
    const { agents, me, queues = Immutable.fromJS({}), saving, onChange } = this.props;

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
        {queues.map((queue, index) => {
          const voiceAgent = queue.get('agents').filter(agent => agent.get('agent') === me.get('id')).first();

          return (
            <QueueItem
              key={index}
              agents={agents}
              queue={queue}
              active={voiceAgent ? voiceAgent.get('is_enabled') : false}
              onChange={onChange}
              saving={saving}
            />
          );
        })}
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
    const { queue, active, onChange } = this.props;
    onChange(queue, !active);
  };

  render() {
    const { agents, queue, active, saving } = this.props;
    const queueAgentIds = queue.get('agents').map(voiceAgent => voiceAgent.get('agent')) || Immutable.fromJS([]);
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
