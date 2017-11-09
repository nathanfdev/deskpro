import React from 'react';
import PropTypes from 'prop-types';
import { Select, Label, Button, Radio } from '@deskpro/react-components';
import newid from '@deskpro/react-components/lib/utils/newid';
import EditModal from './EditModal';


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
      { value: 'team', label: 'Assign Team' },
      { value: 'reply', label: 'Add reply' },
      { value: 'note', label: 'Add note' },
      { value: 'hold', label: 'Hold' },
      { value: 'status', label: 'Status' },
      { value: 'macro', label: 'Run macro' },
    ];

    this.typeId = newid('type');
  }

  onSelectChange = (data, name) => {
    this.updateData(data.value, name);
  };

  onRadioChange = (checked, value, name) => {
    this.updateData(value, name);
  };

  getEditModal = (mode) => {
    if (!this.state.replyModalOpen) {
      return null;
    }
    return (
      <EditModal
        mode={mode}
        closeModal={this.closeEditReply}
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
  };

  updateData = (data, name) => {
    const action = this.props.action;
    action.options[name] = data;
    this.props.updateAction(action);
  };

  detailsMethod = () => {
    const string = this.props.action.type;
    const method = `render${string[0].toUpperCase()}${string.substring(1)}`;
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

  renderTeam = () => {
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

  renderReply = () => (
    <div>
      <span key="preview" className="preview">Reply: </span><br />
      <Button size="m" onClick={this.editReply}>Edit Reply</Button>
      {this.getEditModal('reply')}
    </div>
  );

  renderNote = () => (
    <div>
      <span key="preview" className="preview">Note: </span><br />
      <Button size="m" onClick={this.editReply}>Edit Note</Button>
      {this.getEditModal('note')}
    </div>
  );

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
          Unhold ticket
        </Radio>
        <Radio
          name="is_hold"
          checked={action.options.is_hold === 1}
          onChange={this.onRadioChange}
          value={1}
        >
          Put ticket on hold
        </Radio>
      </div>
    );
  };

  renderStatus = () => {
    const { action } = this.props;
    const options = [
      { value: 'awaiting_agent', label: 'agent.tickets.status_awaiting_agent' },
      { value: 'awaiting_user', label: 'agent.tickets.status_awaiting_user' },
      { value: 'resolved', label: 'agent.tickets.status_resolved' },
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

  renderMacro = () => {
    const { action } = this.props;
    const macros = this.props.macros.toArray().map(macro => (
      { value: macro.get('id'), label: macro.get('title') }
    ));
    return (
      <div>
        <Label>Team</Label>
        <Select
          name="macro"
          options={macros}
          clearable={false}
          searchable={false}
          value={action.options.macro}
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
        <i className="close-cross" title="Remove" onClick={this.props.removeAction} />
      </div>
    );
  }
}
export default Action;
