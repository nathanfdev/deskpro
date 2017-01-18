import React, { PropTypes } from 'react';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import { Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import { Toggle } from 'DeskPRO/Component/Semantic/Form/index';
import SectionHeader from '../../../Common/Components/SectionHeader';

class AgentVoiceHeader extends React.Component {

  render() {
    return (
      <SectionHeader
        title="Agents Voice"
        description="Select agents who can accept or make phone calls."
        dividing
      />
    );
  }
}

class AgentsVoiceToggle extends React.Component {

  static propTypes = {
    accounts:              PropTypes.object,
    agents:                PropTypes.object,
    onToggleEnabled:       PropTypes.func,
    onToggleOutboundCalls: PropTypes.func,
    onGoToAccounts:        PropTypes.func
  };

  renderNoAccount() {
    const { onGoToAccounts } = this.props;

    return (
      <div className="page">
        <AgentVoiceHeader />

        You currently have no accounts.
        <br /><br />

        <button className="ui primary button" onClick={onGoToAccounts}>
          Open general settings
        </button>
      </div>
    );
  }

  renderList() {
    const { agents, onToggleEnabled, onToggleOutboundCalls } = this.props;

    return (
      <div className="page">
        <AgentVoiceHeader />
        <div className="voice-agents-table">
          <table>
            <tbody>
              {agents.map((agent, index) =>
                <AgentVoiceToggle
                  key={index}
                  agent={agent}
                  onToggleEnabled={onToggleEnabled}
                  onToggleOutboundCalls={onToggleOutboundCalls}
                />
              )}
            </tbody>
          </table>
        </div>
      </div>
    );
  }

  render() {
    const { accounts } = this.props;

    return accounts && accounts.size > 0 ? this.renderList() : this.renderNoAccount();
  }
}

class AgentVoiceToggle extends React.Component {

  static propTypes = {
    agent:                 PropTypes.object,
    onToggleEnabled:       PropTypes.func,
    onToggleOutboundCalls: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      saving: false
    };
  }

  onToggleEnabled = () => {
    const { agent, onToggleEnabled } = this.props;
    const { saving } = this.state;
    if (saving) {
      return;
    }

    this.setState({
      saving: true
    });

    const promise = onToggleEnabled(agent);
    promise.success(() => {
      this.setState({
        saving: false
      });
    });
    promise.error(() => {
      this.setState({
        saving: false
      });
    });
  };

  onToggleOutboundCalls = () => {
    const { agent, onToggleOutboundCalls } = this.props;
    const { saving } = this.state;
    if (saving) {
      return;
    }

    this.setState({
      saving: true
    });

    const promise = onToggleOutboundCalls(agent);
    promise.success(() => {
      this.setState({
        saving: false
      });
    });
    promise.error(() => {
      this.setState({
        saving: false
      });
    });
  };

  render() {
    const { agent } = this.props;
    const { saving } = this.state;
    const voiceEnabled = agent.getIn(['agent_data', 'is_voice_enabled']);
    const outboundCallEnabled = agent.getIn(['agent_data', 'outbound_calls_enabled']);

    return (
      <tr>
        <td className="agent">
          <PersonAvatar person={agent} size={36} />
          <span className="agent-name">
            {agent.get('name')}
          </span>
        </td>
        <td>
          <Toggle
            disabled={saving}
            active={voiceEnabled}
            onChange={this.onToggleEnabled}
          />
        </td>
        <td>
          {voiceEnabled &&
            <Checkbox
              label="Allow outbound calls"
              value={outboundCallEnabled}
              onChange={this.onToggleOutboundCalls}
              disabled={saving}
            />}
        </td>
      </tr>
    );
  }
}

export default AgentsVoiceToggle;
