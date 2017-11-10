import React from 'react';
import PropTypes from 'prop-types';
import { Select, Label, Radio } from '@deskpro/react-components';
import newid from '@deskpro/react-components/lib/utils/newid';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import Editor from './Editor';


class Action extends React.Component {
  static propTypes = {
    action:       PropTypes.object,
    agents:       PropTypes.object.isRequired,
    agentTeams:   PropTypes.object.isRequired,
    macros:       PropTypes.object.isRequired,
    removeAction: PropTypes.func,
    updateAction: PropTypes.func,
  };

  constructor(props) {
    super(props);

    this.state = {
      replyModalOpen: false
    };

    this.types = [
      { value: 'agent', label: 'Assign Agent' },
      { value: 'agent_team', label: 'Assign Team' },
      { value: 'reply', label: agentPhrases.get('agent.tickets.add_reply_action') },
      { value: 'note', label: agentPhrases.get('agent.tickets.add_note_action') },
      { value: 'hold', label: 'Hold' },
      { value: 'status', label: agentPhrases.get('agent.general.status') },
      { value: 'run_macro', label: 'Run macro' },
    ];

    this.typeId = newid('type');
  }

  onSelectChange = (data, name) => {
    this.updateData({ [name]: data.value });
  };

  onRadioChange = (checked, value, name) => {
    this.updateData({ [name]: value });
  };

  onEditorChange = (content) => {
    this.updateData({
      reply_text: content,
      is_note:    this.props.action.type === 'note' ? 1 : 0
    });
  };

  getEditModal = (mode) => {
    if (['reply', 'note'].indexOf(this.props.action.type) === -1) {
      return null;
    }
    return (
      <Editor
        mode={mode}
        ref={(c) => { this.editor = c; }}
        value={this.props.action.options.reply_text}
        closeModal={this.closeEditReply}
        onChange={this.onEditorChange}
      />
    );
  };

  closeEditReply = () => {
    this.setState({
      replyModalOpen: false,
    });
  };

  editReply = () => {
    this.setState({
      replyModalOpen: true,
    });
  };

  updateType = (type) => {
    this.props.updateAction({ type: type.value, options: {} });
    if (this.editor) {
      this.editor.redactor.setCode('');
    }
  };

  updateData = (data) => {
    const action = this.props.action;
    action.options = data;
    this.props.updateAction(action);
  };

  detailsMethod = () => {
    const string = this.props.action.type;
    const method = `render${string[0].toUpperCase()}${string.substring(1).replace(/_\w/g, m => m[1].toUpperCase())}`;
    if (typeof this[method] === 'function') {
      return method;
    }
    return 'renderMissingMethod';
  };

  renderMissingMethod = () => <div>Missing method</div>;

  renderAgent = () => {
    const { action } = this.props;
    const agents = [
      { value: -1, label: 'Me' },
      { value: 0, label: 'Unassigned' },
    ].concat(this.props.agents.toArray().map(agent => (
      { value: agent.get('id'), label: agent.get('name') }
    )));
    return (
      <div>
        <Label>Agent</Label>
        <Select
          name="agent"
          options={agents}
          clearable={false}
          searchable={false}
          value={action.options.agent}
          onChange={this.onSelectChange}
        />
      </div>
    );
  };

  renderAgentTeam = () => {
    const { action } = this.props;
    const agentTeams = [
      { value: -1, label: 'My Team' },
      { value: 0, label: 'None' },
    ].concat(this.props.agentTeams.toArray().map(team => (
      { value: team.get('id'), label: team.get('name') }
    )));
    return (
      <div>
        <Label>Team</Label>
        <Select
          name="agent_team"
          options={agentTeams}
          clearable={false}
          searchable={false}
          value={action.options.agent_team}
          onChange={this.onSelectChange}
        />
      </div>
    );
  };

  renderReply = () => {};

  renderNote = () => {};

  renderHold = () => {
    const { action } = this.props;
    return (
      <div>
        <span>Team</span><br />
        <Radio
          name="is_hold"
          checked={action.options.is_hold === 0}
          onChange={this.onRadioChange}
          value={0}
        >
          {agentPhrases.get('agent.tickets.unhold_btn')}
        </Radio>
        <Radio
          name="is_hold"
          checked={action.options.is_hold === 1}
          onChange={this.onRadioChange}
          value={1}
        >
          {agentPhrases.get('agent.tickets.hold_btn')}
        </Radio>
      </div>
    );
  };

  renderStatus = () => {
    const { action } = this.props;
    const options = [
      { value: 'awaiting_agent', label: agentPhrases.get('agent.tickets.status_awaiting_agent') },
      { value: 'awaiting_user', label: agentPhrases.get('agent.tickets.status_awaiting_user') },
      { value: 'resolved', label: agentPhrases.get('agent.tickets.status_resolved') },
    ];
    return (
      <div>
        <Label>Status</Label>
        <Select
          name="status"
          options={options}
          clearable={false}
          searchable={false}
          value={action.options.status}
          onChange={this.onSelectChange}
        />
      </div>
    );
  };

  renderRunMacro = () => {
    const { action } = this.props;
    const macros = this.props.macros.toArray().map(macro => (
      { value: macro.get('id'), label: macro.get('title') }
    ));
    return (
      <div>
        <Label>Team</Label>
        <Select
          name="macroId"
          options={macros}
          clearable={false}
          searchable={false}
          value={action.options.macroId}
          onChange={this.onSelectChange}
        />
      </div>
    );
  };

  render() {
    const { action } = this.props;
    return (
      <div className="action">
        <div className="type">
          <Label htmlFor={this.typeId}>Type</Label>
          <Select
            id={this.typeId}
            options={this.types}
            clearable={false}
            searchable={false}
            value={action.type}
            onChange={this.updateType}
          />
        </div>
        <div className="details">
          {this[this.detailsMethod()]()}
        </div>
        {this.getEditModal()}
        <i className="close-cross" title="Remove" onClick={this.props.removeAction} />
      </div>
    );
  }
}
export default Action;
