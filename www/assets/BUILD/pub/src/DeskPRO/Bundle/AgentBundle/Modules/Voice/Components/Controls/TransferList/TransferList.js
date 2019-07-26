import PropTypes from 'prop-types';
import React from 'react';
import { TabButton, Tab } from 'DeskPRO/Component/Tab/Tab';
import TransferListButton from '../TransferListButton';
import Queues from './Queues';
import AgentList from '../AgentList';
import TransferStatus from '../TransferStatus';
import AutoAttendants from './AutoAttendants';

const hasAgentsTab = (props) => {
  const { me, onlineAgentIds, forwardingAgentIds } = props;
  return ((onlineAgentIds && onlineAgentIds.filter(onlineId => onlineId !== me.get('id')).size > 0)
    || (forwardingAgentIds && forwardingAgentIds.filter(onlineId => onlineId !== me.get('id')).size > 0));
};
const hasQueuesTab = props => props.queues && props.queues.size > 0;
const hasAutoAttendantTab = props => props.autoAttendants && props.autoAttendants.size > 0;

const getDefaultTabName = (props, currentTab) => {
  const availableTabs = [];
  if (hasAgentsTab(props)) {
    availableTabs.push('agents');
  }
  if (hasQueuesTab(props)) {
    availableTabs.push('queues');
  }
  if (hasAutoAttendantTab(props)) {
    availableTabs.push('auto_attendants');
  }

  if (availableTabs.indexOf(currentTab) === -1) {
    return availableTabs[0];
  }

  return currentTab;
};

class TransferList extends React.Component {

  static propTypes = {
    me:                          PropTypes.object,
    phoneCall:                   PropTypes.object,
    target:                      PropTypes.object,
    transferDisabled:            PropTypes.bool,
    onlineAgentIds:              PropTypes.object,
    forwardingAgentIds:          PropTypes.object,
    agents:                      PropTypes.object,
    busyAgentIds:                PropTypes.array,
    queues:                      PropTypes.object,
    autoAttendants:              PropTypes.object,
    warmTransferToAgent:         PropTypes.func,
    coldTransferToAgent:         PropTypes.func,
    coldTransferToQueue:         PropTypes.func,
    coldTransferToAutoAttendant: PropTypes.func,
    cancelInvite:                PropTypes.func,
    closeMenu:                   PropTypes.func,
    inviteError:                 PropTypes.string
  };

  static defaultProps = {
    closeMenu: () => {},
  };

  constructor(props) {
    super(props);
    this.state = {
      tabName:        getDefaultTabName(props, 'agents'),
      selectedTarget: null
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({ tabName: getDefaultTabName(nextProps, this.state.tabName) });
  }

  changeTab = (tabName) => {
    this.setState({ tabName });
  };

  selectAgentTarget = (target) => {
    this.setState({ selectedTarget: { type: 'agent', target } });
  };

  selectQueueTarget = (target) => {
    this.setState({ selectedTarget: { type: 'queue', target } });
  };

  selectAutoAttendantTarget = (target) => {
    this.setState({ selectedTarget: { type: 'auto_attendant', target } });
  };

  warmTransfer = () => {
    const { warmTransferToAgent } = this.props;
    const { selectedTarget } = this.state;
    this.setState({
      selectedTarget: null
    });

    if (selectedTarget && selectedTarget.type === 'agent') {
      setTimeout(() => warmTransferToAgent(selectedTarget.target), 1);
    }
  };

  coldTransfer = () => {
    const { coldTransferToAgent, coldTransferToQueue, coldTransferToAutoAttendant, closeMenu } = this.props;
    const { selectedTarget } = this.state;
    this.setState({
      selectedTarget: null
    });

    if (selectedTarget) {
      if (selectedTarget.type === 'agent') {
        setTimeout(() => coldTransferToAgent(selectedTarget.target), 1);
      } else if (selectedTarget.type === 'queue') {
        setTimeout(() => coldTransferToQueue(selectedTarget.target), 1);
      } else if (selectedTarget.type === 'auto_attendant') {
        setTimeout(() => coldTransferToAutoAttendant(selectedTarget.target), 1);
      }

      closeMenu();
    }
  };

  cancel = () => {
    setTimeout(() => this.props.cancelInvite(), 1);
  };

  renderList() {
    const { me, agents, busyAgentIds, queues, autoAttendants, onlineAgentIds, forwardingAgentIds, phoneCall } = this.props;
    const { inviteError, transferDisabled } = this.props;
    const { tabName, selectedTarget } = this.state;

    return (
      <div>
        {inviteError && <div className="error-message">{inviteError}</div>}
        <div className="tab-menu">
          {hasAgentsTab(this.props) &&
          <TabButton
            tabName="agents"
            title="Agents online"
            iconClass="fa-user"
            onClick={this.changeTab}
            active={tabName === 'agents'}
          />}
          {hasQueuesTab(this.props) &&
          <TabButton
            tabName="queues"
            title="Queues"
            iconClass="fa-tasks"
            onClick={this.changeTab}
            active={tabName === 'queues'}
          />}
          {hasAutoAttendantTab(this.props) &&
          <TabButton
            tabName="auto_attendants"
            title="Auto-attendants"
            iconClass="fa-tasks"
            onClick={this.changeTab}
            active={tabName === 'auto_attendants'}
          />}
        </div>

        <Tab active={tabName === 'agents'}>
          <AgentList
            agents={agents}
            onlineAgentIds={onlineAgentIds}
            forwardingAgentIds={forwardingAgentIds}
            busyAgentIds={busyAgentIds}
            phoneCall={phoneCall}
            target={selectedTarget}
            onClick={this.selectAgentTarget}
            transferDisabled={transferDisabled}
          />
        </Tab>
        {hasQueuesTab &&
        <Tab active={tabName === 'queues'}>
          <Queues
            me={me}
            agents={agents}
            onlineAgentIds={onlineAgentIds}
            forwardingAgentIds={forwardingAgentIds}
            busyAgentIds={busyAgentIds}
            queues={queues}
            target={selectedTarget}
            onClick={this.selectQueueTarget}
          />
        </Tab>}
        {hasAutoAttendantTab &&
        <Tab active={tabName === 'auto_attendants'}>
          <AutoAttendants
            autoAttendants={autoAttendants}
            target={selectedTarget}
            onClick={this.selectAutoAttendantTarget}
          />
        </Tab>}

        <div className="voice-ticket-list-buttons">
          <TransferListButton
            title="Warm transfer"
            icon="call"
            help="Places caller on hold"
            disabled={!selectedTarget || tabName === 'queues' || tabName === 'auto_attendants' || transferDisabled}
            onClick={this.warmTransfer}
          />
          <TransferListButton
            title="Cold transfer"
            icon="share"
            disabled={!selectedTarget || transferDisabled}
            onClick={this.coldTransfer}
          />
        </div>
      </div>
    );
  }

  render() {
    const { phoneCall, target } = this.props;

    if (target) {
      return (
        <div className="voice-ticket-transfer-list">
          <TransferStatus
            phoneCall={phoneCall}
            title="Transferring..."
            cancelLabel="Cancel transfer"
            target={target}
            cancel={this.cancel}
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
