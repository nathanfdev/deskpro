import PropTypes from 'prop-types';
import React from 'react';
import TransferListButton from '../TransferListButton';
import AgentList from '../AgentList';
import TransferStatus from '../TransferStatus';

class AddList extends React.Component {

  static propTypes = {
    addTarget:      PropTypes.object,
    addTargetType:  PropTypes.string,
    onlineAgents:   PropTypes.object,
    participants:   PropTypes.array,
    connection:     PropTypes.object,
    onAddAgent:     PropTypes.func,
    onCancelInvite: PropTypes.func,
    inviteError:    PropTypes.string
  };

  static defaultProps = {
    onAddAgent:     () => {},
    onCancelInvite: () => {}
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedTarget: null,
      targets:        null
    };
  }

  onChangeTarget = (selectedTarget) => {
    this.setState({ selectedTarget });
  };

  onWarmAdd = () => {
    const { onAddAgent } = this.props;
    const { selectedTarget } = this.state;
    this.setState({
      selectedTarget: null
    });

    setTimeout(() => onAddAgent(selectedTarget, 'warm'), 1);
  };

  onCancel = (target, type) => {
    const { onCancelInvite } = this.props;

    setTimeout(() => onCancelInvite(target, type), 1);
  };

  renderList() {
    const { inviteError, onlineAgents, participants } = this.props;
    const { selectedTarget } = this.state;

    return (
      <div>
        {inviteError && <div className="error-message">{inviteError}</div>}
        {/* <TransferSearch /> */}

        <div className="online-agents-header">
          <i className="fa fa-user" />
          Agents online
        </div>

        <AgentList
          agents={onlineAgents}
          participants={participants}
          target={selectedTarget}
          onClick={this.onChangeTarget}
        />

        <div className="voice-ticket-list-buttons">
          <TransferListButton
            title="Warm Add"
            icon="call"
            help="Places caller on hold"
            disabled={!selectedTarget}
            onClick={this.onWarmAdd}
          />
        </div>
      </div>
    );
  }

  render() {
    const { connection, addTarget, addTargetType } = this.props;

    if (addTarget) {
      return (
        <div className="voice-ticket-add-list">
          <TransferStatus
            connection={connection}
            title="Adding..."
            cancelLabel="Cancel add"
            type={addTargetType}
            target={addTarget}
            onCancel={this.onCancel}
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
