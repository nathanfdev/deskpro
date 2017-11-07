import React from 'react';
import PropTypes from 'prop-types';
import { Select, Label, Button } from '@deskpro/react-components';
import newid from '@deskpro/react-components/lib/utils/newid';


export class Action extends React.Component {
  static propTypes = {
    action:       PropTypes.object,
    agents:       PropTypes.object.isRequired,
    agentTeams:   PropTypes.object.isRequired,
    removeAction: PropTypes.func,
    updateAction: PropTypes.func,
  };

  constructor(props) {
    super(props);

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
    this.detailId = newid('detail');
  }

  updateType = (type) => {
    const action = this.props.action;
    action.type = type.value;
    action.data = null;
    this.props.updateAction(action);
  };

  updateData = (data) => {
    const action = this.props.action;
    action.data = data.value;
    this.props.updateAction(action);
  };

  detailsMethod = () => {
    const string = this.props.action.type;
    console.log(this.props.action);
    console.log(string);
    return `render${string[0].toUpperCase()}${string.substring(1)}`;
  };

  renderAgent = () => {
    const { action } = this.props;
    return [
      <Label key="label" htmlFor={this.detailId}>Agent</Label>,
      <Select
        key="select"
        id={this.typeId}
        options={this.props.agents.toArray().map(agent => ({ value: agent.get('id'), label: agent.get('name') }))}
        clearable={false}
        searchable={false}
        value={action.data}
        onChange={this.updateData}
        ref={(c) => { this.type = c; }}
      />
    ];
  };

  renderTeam = () => {
    const { action } = this.props;
    return [
      <Label key="label" htmlFor={this.detailId}>Team</Label>,
      <Select
        key="select"
        id={this.typeId}
        options={this.props.agentTeams.toArray().map(team => ({ value: team.get('id'), label: team.get('name') }))}
        clearable={false}
        searchable={false}
        value={action.data}
        onChange={this.updateData}
        ref={(c) => { this.type = c; }}
      />
    ];
  };

  renderReply = () => [
    <span key="preview" className="preview" />,
    Button
  ];

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
            ref={(c) => { this.type = c; }}
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
