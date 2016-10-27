import React from 'react';
import TransferListButton from '../TransferListButton';
import TransferSearch from '../TransferSearch';
import AgentListContainer from '../AgentList/AgentListContainer';
import TransferStatus from '../TransferStatus';

class AddList extends React.Component {

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
    setTimeout(() => this.setState({
      target: this.state.selectedTarget,
      type:   'warm'
    }), 1);
  };

  onColdAdd = () => {
    setTimeout(() => this.setState({
      target: this.state.selectedTarget,
      type:   'cold'
    }), 1);
  };

  onCancel = (event) => {
    event.preventDefault();
    setTimeout(() => this.setState({
      target: null,
      type:   null
    }), 1);
  };

  renderList() {
    const { selectedTarget } = this.state;

    return (
      <div>
        <TransferSearch />

        <div className="online-agents-header">
          <i className="fa fa-user" />
          Agents online
        </div>

        <AgentListContainer
          target={selectedTarget}
          onClick={this.onChangeTarget}
        />

        <div className="voice-ticket-list-buttons">
          <TransferListButton
            title="Warm add"
            icon="call"
            help="Places caller on hold"
            disabled={!selectedTarget}
            onClick={this.onWarmAdd}
          />
          <TransferListButton
            title="Cold add"
            icon="share"
            disabled={!selectedTarget}
            onClick={this.onColdAdd}
          />
        </div>
      </div>
    );
  }

  render() {
    const { target, type } = this.state;

    if (target) {
      return (
        <div className="voice-ticket-add-list">
          <TransferStatus
            title="Adding..."
            cancelLabel="Cancel add"
            type={type}
            target={target}
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
