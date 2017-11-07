import React from 'react';
import PropTypes from 'prop-types';
import { Action } from './Action';

export class ActionsBlock extends React.Component {
  static propTypes = {
    actions:    PropTypes.array,
    agents:     PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    onChange:   PropTypes.func,
  };

  static defaultProps = {
    actions: [],
    onChange() {},
  };

  updateAction = (action, index) => {
    const actions = this.props.actions;
    actions[index] = action;
    this.props.onChange(actions);
  };

  addAction = () => {
    const actions = this.props.actions;
    actions.push({
      type: 'agent'
    });
    this.props.onChange(actions);
  };

  removeAction = (index) => {
    const actions = this.props.actions;
    actions.splice(index, 1);
    this.props.onChange(actions);
  };

  renderActions = () => this.props.actions.map((action, index) => (
    <Action
      key={index}
      action={action}
      agents={this.props.agents}
      agentTeams={this.props.agentTeams}
      updateAction={a => this.updateAction(a, index)}
      removeAction={() => this.removeAction(index)}
    />
  ));

  render() {
    return (
      <div>
        <div className="actions">
          { this.renderActions() }
        </div>
        <div className="add_action" onClick={this.addAction}>
          <i className="plus" /> Add action
        </div>
      </div>
    );
  }
}
