import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { AssignForm } from './AssignForm';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  me: meSelector(state),
  agents: agentsSelector(state),
  agentTeams: agentTeamsSelector(state),
  departments: allDepartmentsSelector(state)
}))
export class AssignFormContainer extends React.Component {

  static propTypes = {
    onCloseForm: PropTypes.func.isRequired
  };

  render() {
    return (
      <AssignForm {...this.props} />
    );
  }
}
