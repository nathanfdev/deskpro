import PropTypes from 'prop-types';
import React from 'react';
import TransferListButton from '../TransferListButton';
import AgentList from '../AgentList';
import TransferStatus from '../TransferStatus';

class AddList extends React.Component {

  static propTypes = {
    target:           PropTypes.object,
    onlineAgents:     PropTypes.object,
    forwardingAgents: PropTypes.object,
    busyAgents:       PropTypes.array,
    participants:     PropTypes.array,
    connection:       PropTypes.object,
    warmAddAgent:     PropTypes.func,
    cancelInvite:     PropTypes.func,
    inviteError:      PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedTarget: null,
      targets:        null
    };
  }

  selectAgentTarget = (target) => {
    this.setState({ selectedTarget: { type: 'agent', target } });
  };

  warmAddAgent = () => {
    const { warmAddAgent } = this.props;
    const { selectedTarget } = this.state;
    this.setState({
      selectedTarget: null
    });

    setTimeout(() => warmAddAgent(selectedTarget.target, 'warm'), 1);
  };

  cancel = () => {
    setTimeout(() => this.props.cancelInvite(), 1);
  };

  renderList() {
    const { inviteError, onlineAgents, forwardingAgents, busyAgents, participants } = this.props;
    const { selectedTarget } = this.state;

    return (
      <div>
        {inviteError && <div className="error-message">{inviteError}</div>}

        <div className="online-agents-header">
          <i className="fas fa-user" />
          Agents online
        </div>

        <AgentList
          onlineAgents={onlineAgents}
          forwardingAgents={forwardingAgents}
          busyAgents={busyAgents}
          participants={participants}
          target={selectedTarget}
          onClick={this.selectAgentTarget}
        />

        <div className="voice-ticket-list-buttons">
          <TransferListButton
            title="Warm Add"
            icon="call"
            help="Places caller on hold"
            disabled={!selectedTarget}
            onClick={this.warmAddAgent}
          />
        </div>
      </div>
    );
  }

  render() {
    const { connection, target } = this.props;

    if (target) {
      return (
        <div className="voice-ticket-add-list">
          <TransferStatus
            connection={connection}
            title="Adding..."
            cancelLabel="Cancel add"
            target={target}
            cancel={this.cancel}
          />
        </div>
      );
    }

    return (
      <div className="voice-ticket-add-list">
        {this.renderList()}
      </div>
    );
  }
}

export default AddList;
