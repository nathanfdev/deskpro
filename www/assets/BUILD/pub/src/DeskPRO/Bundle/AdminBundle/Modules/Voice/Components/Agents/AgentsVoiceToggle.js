import React, { PropTypes } from 'react';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import { Toggle } from 'DeskPRO/Component/Semantic/Form/index';
import SectionHeader from '../../../Common/Components/SectionHeader';

class AgentsVoiceToggle extends React.Component {

  static propTypes = {
    accounts:       PropTypes.object,
    agents:         PropTypes.object,
    onToggle:       PropTypes.func,
    onGoToAccounts: PropTypes.func
  };

  renderNoAccount() {
    const { onGoToAccounts } = this.props;

    return (
      <div className="page">
        <SectionHeader title="Agents Voice" dividing />

        You currently have no accounts.
        <br /><br />

        <button className="ui primary button" onClick={onGoToAccounts}>
          Open general settings
        </button>
      </div>
    );
  }

  renderList() {
    const { agents, onToggle } = this.props;

    return (
      <div className="page">
        <SectionHeader title="Agents Voice" dividing />
        <div className="voice-agents-table">
          <table>
            <tbody>
              {agents.map((agent, index) =>
                <AgentVoiceToggle
                  key={index}
                  agent={agent}
                  onToggle={onToggle}
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
    agent:    PropTypes.object,
    onToggle: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      saving: false
    };
  }

  onToggle = () => {
    const { agent, onToggle } = this.props;
    this.setState({
      saving: true
    });

    const promise = onToggle(agent);
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
            active={agent.getIn(['agent_data', 'is_voice_enabled'])}
            onChange={this.onToggle}
          />
        </td>
      </tr>
    );
  }
}

export default AgentsVoiceToggle;
