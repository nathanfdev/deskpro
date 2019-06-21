import PropTypes from 'prop-types';
import React from 'react';
import ScrollArea from 'react-scrollbar';
import Immutable from 'immutable';
import classNames from 'classnames';
import Avatar from '../../Common/Avatar';

class Queues extends React.Component {

  static propTypes = {
    target:       PropTypes.object,
    me:           PropTypes.object,
    agents:       PropTypes.object,
    onlineAgents: PropTypes.object,
    queues:       PropTypes.object,
    onClick:      PropTypes.func
  };

  selectItem = (queue) => {
    this.props.onClick(queue);
  };

  render() {
    const { me, queues, agents, onlineAgents, target } = this.props;

    return (
      <ScrollArea className="voice-queue-list">
        {queues.toArray().map((queue, index) =>
          <QueueItem
            key={index}
            me={me}
            agents={agents}
            onlineAgents={onlineAgents}
            queue={queue}
            active={target && queue === target.target}
            onClick={this.selectItem}
          />
        )}
      </ScrollArea>
    );
  }
}

class QueueItem extends React.Component {

  static propTypes = {
    me:           PropTypes.object,
    agents:       PropTypes.object,
    onlineAgents: PropTypes.object,
    queue:        PropTypes.object,
    active:       PropTypes.bool,
    onClick:      PropTypes.func
  };

  onClick = () => {
    const { queue, onClick } = this.props;
    onClick(queue);
  };

  render() {
    const { me, agents, onlineAgents, queue, active } = this.props;
    const queueAgentIds = queue.get('agents').map(voiceAgent => voiceAgent.get('agent')) || Immutable.fromJS([]);
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
          {queueAgents.toArray().map((agent, index) =>
            <Avatar
              key={index}
              person={agent}
              online={onlineAgents.contains(agent) || agent === me}
              size={20}
              withOnlineStatus
            />
          )}
        </div>
        {/* <div className="queue-users">*/}
        {/* 3 users in queue (average wait 2m)*/}
        {/* </div>*/}
      </div>
    );
  }
}

export default Queues;
