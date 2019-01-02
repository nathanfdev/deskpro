import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { Input, Select } from 'DeskPRO/Component/Semantic/ReactForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';

class QueueList extends React.Component {

  static propTypes = {
    queues:              PropTypes.object,
    agents:              PropTypes.object,
    agentTeams:          PropTypes.object,
    addQueue:            PropTypes.func,
    editQueue:           PropTypes.func,
    defaultQueue:        PropTypes.number,
    maxChatsCount:       PropTypes.object,
    changeDefaultQueue:  PropTypes.func,
    changeMaxChatsCount: PropTypes.func
  };

  renderEmpty() {
    return (
      <div className="page">
        <QueueHeader />

        You currently have no queues.
        <br /><br />

        <button className="ui primary button" onClick={this.props.addQueue}>
          Add queue
        </button>
      </div>
    );
  }

  renderTable() {
    const { queues, agents, agentTeams, defaultQueue, maxChatsCount } = this.props;
    const { addQueue, editQueue, changeDefaultQueue, changeMaxChatsCount } = this.props;
    const defaultChoices = queues.map(queue => ({
      value: queue.get('id'),
      label: queue.get('name')
    })).toArray();

    return (
      <div className="page user-chat">
        <button className="ui right floated basic button" onClick={addQueue}>
          <i className="icon plus" />
          Add queue
        </button>

        <QueueHeader />

        <div className="user-chat-default-queue">
          The default queue is
          <Select
            choices={defaultChoices}
            value={defaultQueue}
            onChange={changeDefaultQueue}
            clearable={false}
          />
        </div>
        <div className="user-chat-max-chats">
          <span>Agents can handle a maximum of</span>
          <Input
            type="number"
            value={maxChatsCount}
            onChange={changeMaxChatsCount}
          />
          <span>simultaneous chats.</span>
        </div>

        <div className="admin-list-table">
          <div className="row header">
            <div className="column queue-name">Name</div>
            <div className="column agents">Agents</div>
          </div>
          {queues.toArray().map(queue =>
            <QueueRow
              queue={queue}
              agents={agents}
              agentTeams={agentTeams}
              editQueue={editQueue}
            />
          )}
        </div>
      </div>
    );
  }

  render() {
    const { queues } = this.props;
    return queues && queues.size ? this.renderTable() : this.renderEmpty();
  }
}

class QueueHeader extends React.Component {

  render() {
    return (
      <SectionHeader
        title="Chat Queues"
        description="Add and manage chat queues that define how new user chats get assigned to your agents."
        dividing
      />
    );
  }
}

class QueueRow extends React.Component {

  static propTypes = {
    queue:     PropTypes.object,
    agents:    PropTypes.object,
    editQueue: PropTypes.func
  };

  onEditQueue = (event) => {
    event.preventDefault();

    const { queue, editQueue } = this.props;
    editQueue(queue);
  };

  render() {
    const { queue, agents } = this.props;
    const targets = queue.get('targets') ? queue.get('targets').toOrderedMap()
      .sort((a, b) => a.get('sort') - b.get('sort'))
      .map((target) => {
        const type = target.get('type');
        const id = target.get('target');
        if (type === 'agent') {
          return agents.get(id);
        }

        return null;
      }) : Immutable.fromJS([]);

    const displayTargets = targets.slice(0, 5);
    const popupTargets = targets.slice(5);

    return (
      <div className="row" key={queue.get('id')}>
        <div className="info">
          <div className="column queue-name">{queue.get('name')}</div>
          {queue.get('is_all_agents')
            ? <div className="column press-options">
              <span className="press-option">
                All Agents
              </span>
            </div>
            : <div className="column agents">
              {displayTargets.toArray().map((target, index) =>
                <div className="avatar">
                  <PersonAvatar key={index} person={target} size={24} />
                </div>
              )}
              {popupTargets.size > 0 &&
              <span className="more-button-wrapper">
                <PopUp
                  positionMy="left top"
                  positionAt="left bottom"
                  zIndex={99999}
                  autoClose
                  content={(
                    <div className="voice-popup-avatars">
                      {popupTargets.toArray().map((target, index) =>
                        <div className="avatar">
                          <PersonAvatar key={index} person={target} size={24} />
                        </div>
                      )}
                    </div>
                  )}
                >
                  <a className="more-button">+ {popupTargets.size} more</a>
                </PopUp>
              </span>}
            </div>}
          <div className="column options-button">
            <a onClick={this.onEditQueue}>
              <i className="fas fa-cog" />
            </a>
          </div>
          <div style={{ clear: 'both' }} />
        </div>
      </div>
    );
  }
}

export default QueueList;
