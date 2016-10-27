import React from 'react';
import { TabButton, Tab } from 'DeskPRO/Component/Tab/Tab';
import TransferListButton from '../TransferListButton';
import TransferSearch from '../TransferSearch';
import Queues from './Queues';
import Dialpad from './Dialpad';
import QueuesContainer from '../../Common/QueuesContainer';
import AgentListContainer from '../AgentList/AgentListContainer';
import TransferStatus from '../TransferStatus';

class TransferList extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      tabName:        'agents',
      selectedTarget: null,
      type:           null,
      target:         null
    };
  }

  onChangeTab = (tabName) => {
    this.setState({ tabName });
  };

  onChangeTarget = (selectedTarget) => {
    this.setState({ selectedTarget });
  };

  onWarmTransfer = () => {
    setTimeout(() => this.setState({
      target: this.state.selectedTarget,
      type:   'warm'
    }), 1);
  };

  onColdTransfer = () => {
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
    const { tabName, selectedTarget } = this.state;

    return (
      <div>
        <TransferSearch />

        <div className="tab-menu">
          <TabButton
            tabName="agents"
            title="Agents"
            iconClass="fa-user"
            onClick={this.onChangeTab}
            active={tabName === 'agents'}
          />
          <TabButton
            tabName="queues"
            title="Queues"
            iconClass="fa-tasks"
            onClick={this.onChangeTab}
            active={tabName === 'queues'}
          />
          <TabButton
            tabName="dialpad"
            title="Dialpad"
            iconClass="fa-th"
            onClick={this.onChangeTab}
            active={tabName === 'dialpad'}
          />
        </div>

        <Tab active={tabName === 'agents'}>
          <AgentListContainer
            target={selectedTarget}
            onClick={this.onChangeTarget}
          />
        </Tab>
        <Tab active={tabName === 'queues'}>
          <QueuesContainer>
            <Queues
              target={selectedTarget}
              onClick={this.onChangeTarget}
            />
          </QueuesContainer>
        </Tab>
        <Tab active={tabName === 'dialpad'}>
          <Dialpad />
        </Tab>

        <div className="voice-ticket-list-buttons">
          <TransferListButton
            title="Warm transfer"
            icon="call"
            help="Places caller on hold"
            disabled={!selectedTarget}
            onClick={this.onWarmTransfer}
          />
          <TransferListButton
            title="Cold transfer"
            icon="share"
            disabled={!selectedTarget}
            onClick={this.onColdTransfer}
          />
        </div>
      </div>
    );
  }

  render() {
    const { target, type } = this.state;

    if (target) {
      return (
        <div className="voice-ticket-transfer-list">
          <TransferStatus
            title="Transferring..."
            cancelLabel="Cancel transfer"
            type={type}
            target={target}
            onCancel={this.onCancel}
          />
        </div>
      );
    }

    return (
      <div className="voice-ticket-transfer-list">
        {this.renderList()}
      </div>
    );
  }
}

export default TransferList;
