import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import SectionHeader from '../../../../Common/Components/SectionHeader';

class QueueHeader extends React.Component {

  render() {
    return (
      <SectionHeader
        title="Queue Settings"
        description="Add and manage queues that can be targets for numbers and IVRs."
        dividing
      />
    );
  }
}

class QueueRow extends React.Component {

  static propTypes = {
    queue:       PropTypes.object,
    agents:      PropTypes.object,
    onEditQueue: PropTypes.func
  };

  onEditQueue = (event) => {
    event.preventDefault();

    const { queue, onEditQueue } = this.props;
    onEditQueue(queue);
  };

  render() {
    const { queue, agents } = this.props;
    const queueAgentIds = queue.get('agents') ? queue.get('agents').map(voiceAgent => voiceAgent.get('agent')) : Immutable.fromJS([]);
    const queueAgents = agents.filter(agent => queueAgentIds.contains(agent.get('id')));

    const displayQueueAgents = queueAgents.slice(0, 5);
    const popupQueueAgents = queueAgents.slice(5);

    return (
      <div className="row" key={queue.get('id')}>
        <div className="info">
          <div className="column queue-name">{queue.get('name')}</div>
          <div className="column agents">
            {displayQueueAgents.toArray().map((agent, index) =>
              <div className="avatar">
                <PersonAvatar key={index} person={agent} size={24} />
              </div>
            )}
            {popupQueueAgents.size > 0 &&
              <span>
                <PopUp
                  positionMy="left top"
                  positionAt="left bottom"
                  zIndex={99999}
                  autoClose
                  content={(
                    <div className="voice-popup-avatars">
                      {popupQueueAgents.toArray().map((agent, index) =>
                        <div className="avatar">
                          <PersonAvatar key={index} person={agent} size={24} />
                        </div>
                      )}
                    </div>
                  )}
                >
                  <a className="more-button">+ {popupQueueAgents.size} more</a>
                </PopUp>
              </span>
            }
          </div>
          <div className="column options-button">
            <a onClick={this.onEditQueue}>
              <i className="fa fa-gear" />
            </a>
          </div>
          <div style={{ clear: 'both' }} />
        </div>
      </div>
    );
  }
}

class QueueList extends React.Component {

  static propTypes = {
    accounts:       PropTypes.object,
    agents:         PropTypes.object,
    queues:         PropTypes.object,
    onAddQueue:     PropTypes.func,
    onEditQueue:    PropTypes.func,
    onGoToAccounts: PropTypes.func
  };

  renderNoAccount() {
    const { onGoToAccounts } = this.props;

    return (
      <div className="page">
        <QueueHeader />

        You currently have no accounts.
        <br /><br />

        <button className="ui primary button" onClick={onGoToAccounts}>
          Open general settings
        </button>
      </div>
    );
  }

  renderEmpty() {
    return (
      <div className="page">
        <QueueHeader />

        You currently have no queues.
        <br /><br />

        <button className="ui primary button" onClick={this.props.onAddQueue}>
          Add queue
        </button>
      </div>
    );
  }

  renderTable() {
    const { queues, agents, onAddQueue, onEditQueue } = this.props;

    return (
      <div className="page">
        <button className="ui right floated basic button" onClick={onAddQueue}>
          <i className="icon plus" />
          Add queue
        </button>

        <QueueHeader />

        <div className="admin-list-table">
          <div className="row header">
            <div className="column queue-name">Name</div>
            <div className="column agents">Agents</div>
          </div>
          {queues.toArray().map(queue =>
            <QueueRow
              queue={queue}
              agents={agents}
              onEditQueue={onEditQueue}
            />
          )}
        </div>
      </div>
    );
  }

  render() {
    const { queues, accounts } = this.props;

    if (!accounts || !accounts.size) {
      return this.renderNoAccount();
    }

    return queues && queues.size ? this.renderTable() : this.renderEmpty();
  }
}

export default QueueList;
