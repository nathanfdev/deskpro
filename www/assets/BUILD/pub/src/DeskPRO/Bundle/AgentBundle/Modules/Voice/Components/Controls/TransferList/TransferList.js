import PropTypes from 'prop-types';
import React from 'react';
import { TabButton, Tab } from 'DeskPRO/Component/Tab/Tab';
import TransferListButton from '../TransferListButton';
import TransferSearch from '../TransferSearch';
// import Queues from './Queues';
// import Dialpad from './Dialpad';
// import QueuesContainer from '../../Common/QueuesContainer';
import AgentList from '../AgentList';
import TransferStatus from '../TransferStatus';

class TransferList extends React.Component {

  static propTypes = {
    transferTarget:     PropTypes.object,
    transferTargetType: PropTypes.string,
    onlineAgents:       PropTypes.object,
    participants:       PropTypes.array,
    connection:         PropTypes.object,
    onTransferCall:     PropTypes.func,
    onCancelInvite:     PropTypes.func
  };

  static defaultProps = {
    onTransferCall: () => {},
    onCancelInvite: () => {}
  };

  constructor(props) {
    super(props);
    this.state = {
      tabName:        'agents',
      selectedTarget: null
    };
  }

  onChangeTab = (tabName) => {
    this.setState({ tabName });
  };

  onChangeTarget = (selectedTarget) => {
    this.setState({ selectedTarget });
  };

  onWarmTransfer = () => {
    const { onTransferCall } = this.props;
    const { selectedTarget } = this.state;
    this.setState({
      selectedTarget: null
    });

    setTimeout(() => onTransferCall(selectedTarget, 'warm'), 1);
  };

  onColdTransfer = () => {
    const { onTransferCall } = this.props;
    const { selectedTarget } = this.state;
    this.setState({
      selectedTarget: null
    });

    setTimeout(() => onTransferCall(selectedTarget, 'cold'), 1);
  };

  onCancel = (target, type) => {
    const { onCancelInvite } = this.props;

    setTimeout(() => onCancelInvite(target, type), 1);
  };

  renderList() {
    const { onlineAgents, participants } = this.props;
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
          {/* <TabButton
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
          /> */}
        </div>

        <Tab active={tabName === 'agents'}>
          <AgentList
            agents={onlineAgents}
            participants={participants}
            target={selectedTarget}
            onClick={this.onChangeTarget}
          />
        </Tab>
        {/*
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
        </Tab> */}

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
    const { connection, transferTarget, transferTargetType } = this.props;

    if (transferTarget) {
      return (
        <div className="voice-ticket-transfer-list">
          <TransferStatus
            connection={connection}
            title="Transferring..."
            cancelLabel="Cancel transfer"
            type={transferTargetType}
            target={transferTarget}
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
