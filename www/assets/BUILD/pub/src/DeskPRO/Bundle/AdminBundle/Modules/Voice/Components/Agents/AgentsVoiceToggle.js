import PropTypes from 'prop-types';
import React from 'react';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import { Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import { Toggle } from 'DeskPRO/Component/Semantic/Form/index';
import SectionHeader from '../../../Common/Components/SectionHeader';
import AgentSettingsForm from './AgentSettingsForm';

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
    accounts:            PropTypes.object,
    agents:              PropTypes.object,
    settings:            PropTypes.object,
    toggleAll:           PropTypes.func,
    toggleEnabled:       PropTypes.func,
    toggleOutboundCalls: PropTypes.func,
    toggleUseForwarding: PropTypes.func,
    goToAccounts:        PropTypes.func,
    saveSettings:        PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      saving: false
    };
  }

  toggleAll = (event) => {
    event.preventDefault();

    const { toggleAll } = this.props;
    const { saving } = this.state;
    if (saving) {
      return;
    }

    this.setState({
      saving: true
    });

    const promise = toggleAll();
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

  renderNoAccount() {
    const { goToAccounts } = this.props;

    return (
      <div className="page">
        <AgentVoiceHeader />

        You currently have no accounts.
        <br /><br />

        <button className="ui primary button" onClick={goToAccounts}>
          Open general settings
        </button>
      </div>
    );
  }

  renderList() {
    const { agents, settings, toggleEnabled, toggleOutboundCalls, toggleUseForwarding, saveSettings } = this.props;
    const { saving } = this.state;

    return (
      <div className="page">
        <AgentVoiceHeader />
        <AgentSettingsForm
          settings={settings}
          onSubmit={saveSettings}
        />
        <div className="voice-agents-table">
          <table>
            <tbody>
              <tr>
                <td />
                <td className="voice-table-mass-action">
                  <span className="voice-table-mass-action-button" onClick={this.toggleAll}>
                    Toggle all
                  </span>
                </td>
                <td />
              </tr>
            </tbody>
            <tbody>
              {agents.toArray().map((agent, index) =>
                <AgentVoiceToggle
                  key={index}
                  agent={agent}
                  toggleEnabled={toggleEnabled}
                  toggleOutboundCalls={toggleOutboundCalls}
                  toggleUseForwarding={toggleUseForwarding}
                  disabled={saving}
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
    agent:               PropTypes.object,
    disabled:            PropTypes.bool,
    toggleEnabled:       PropTypes.func,
    toggleOutboundCalls: PropTypes.func,
    toggleUseForwarding: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      saving: false
    };
  }

  toggleEnabled = () => {
    const { agent, toggleEnabled, disabled } = this.props;
    const { saving } = this.state;
    if (saving || disabled) {
      return;
    }

    this.setState({
      saving: true
    });

    const promise = toggleEnabled(agent);
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

  toggleOutboundCalls = () => {
    const { agent, toggleOutboundCalls, disabled } = this.props;
    const { saving } = this.state;
    if (saving || disabled) {
      return;
    }

    this.setState({
      saving: true
    });

    const promise = toggleOutboundCalls(agent);
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

  toggleUseForwarding = () => {
    const { agent, toggleUseForwarding, disabled } = this.props;
    const { saving } = this.state;
    if (saving || disabled) {
      return;
    }

    this.setState({
      saving: true
    });

    const promise = toggleUseForwarding(agent);
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
    const { agent, disabled } = this.props;
    const { saving } = this.state;
    const voiceEnabled = agent.getIn(['agent_data', 'is_voice_enabled']);
    const outboundCallEnabled = agent.getIn(['agent_data', 'outbound_calls_enabled']);
    const canUseForwarding = agent.getIn(['agent_data', 'can_use_forwarding']);

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
            disabled={saving || disabled}
            active={voiceEnabled}
            onChange={this.toggleEnabled}
          />
        </td>
        <td>
          {voiceEnabled &&
            <Checkbox
              label="Allow outbound calls"
              value={outboundCallEnabled}
              onChange={this.toggleOutboundCalls}
              disabled={saving || disabled}
            />}
        </td>
        <td>
          {voiceEnabled &&
          <Checkbox
            label="Allow use forwarding"
            value={canUseForwarding}
            onChange={this.toggleUseForwarding}
            disabled={saving || disabled}
          />}
        </td>
      </tr>
    );
  }
}

export default AgentsVoiceToggle;
