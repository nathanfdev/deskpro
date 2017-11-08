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
    accounts:              PropTypes.object,
    agents:                PropTypes.object,
    settings:              PropTypes.object,
    onToggleAll:           PropTypes.func,
    onToggleEnabled:       PropTypes.func,
    onToggleOutboundCalls: PropTypes.func,
    onGoToAccounts:        PropTypes.func,
    onSaveSettings:        PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      saving: false
    };
  }

  onToggleAll = (event) => {
    event.preventDefault();

    const { onToggleAll } = this.props;
    const { saving } = this.state;
    if (saving) {
      return;
    }

    this.setState({
      saving: true
    });

    const promise = onToggleAll();
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
    const { agents, settings, onToggleEnabled, onToggleOutboundCalls, onSaveSettings } = this.props;
    const { saving } = this.state;

    return (
      <div className="page">
        <AgentVoiceHeader />
        <AgentSettingsForm
          settings={settings}
          onSubmit={onSaveSettings}
        />
        <div className="voice-agents-table">
          <table>
            <tbody>
              <tr>
                <td />
                <td className="voice-table-mass-action">
                  <span className="voice-table-mass-action-button" onClick={this.onToggleAll}>
                    Toggle all
                  </span>
                </td>
                <td />
              </tr>
            </tbody>
            <tbody>
              {agents.map((agent, index) =>
                <AgentVoiceToggle
                  key={index}
                  agent={agent}
                  onToggleEnabled={onToggleEnabled}
                  onToggleOutboundCalls={onToggleOutboundCalls}
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
    agent:                 PropTypes.object,
    disabled:              PropTypes.bool,
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
    const { agent, onToggleEnabled, disabled } = this.props;
    const { saving } = this.state;
    if (saving || disabled) {
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
    const { agent, onToggleOutboundCalls, disabled } = this.props;
    const { saving } = this.state;
    if (saving || disabled) {
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
    const { agent, disabled } = this.props;
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
            disabled={saving || disabled}
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
              disabled={saving || disabled}
            />}
        </td>
      </tr>
    );
  }
}

export default AgentsVoiceToggle;
