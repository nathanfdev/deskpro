import React from 'react';
import PropTypes from 'prop-types';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import Action from './Action';

class ActionsBlock extends React.Component {
  static propTypes = {
    actions:     PropTypes.array,
    agents:      PropTypes.object.isRequired,
    agentTeams:  PropTypes.object.isRequired,
    macros:      PropTypes.object.isRequired,
    ticketPerms: PropTypes.object,
    onChange:    PropTypes.func,
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
      type:    'agent',
      options: {}
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
      macros={this.props.macros}
      ticketPerms={this.props.ticketPerms}
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
          <i className="plus" /> {agentPhrases.get('agent.general.add_action_term')}
        </div>
      </div>
    );
  }
}
export default ActionsBlock;
