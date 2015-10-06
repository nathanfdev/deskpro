import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/crmNavActions';
import { loadAllAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions';
import { loadAllUserGroups } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/userGroupsActions';
import { agentTeamNamesSelector }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { userGroupNamesSelector }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/userGroupsSelectors';
import { Nav } from './Nav';

@connect(state => Object.assign({},
  state.CrmNav,
  {teamNames: agentTeamNamesSelector(state)},
  {groupNames: userGroupNamesSelector(state)}
))
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
      this.props.dispatch(actions.loadUsersTotalCount());
      this.props.dispatch(actions.loadGroupsCounts());
      this.props.dispatch(actions.loadOrganizationsTotalCount());
      this.props.dispatch(actions.loadAgentsTotalCount());
      this.props.dispatch(actions.loadTeamsCounts());
      this.props.dispatch(actions.loadPersonLabels());
      this.props.dispatch(actions.loadOrganizationLabels());
      this.props.dispatch(loadAllUserGroups());
      this.props.dispatch(loadAllAgentTeams());
  }

  render() {
    const {labels, users, organizations, agents, groupNames, teamNames, dpWindow} = this.props;

    return (
      <Nav
          labels={labels}
          users={users}
          organizations={organizations}
          agents={agents}
          groupNames={groupNames}
          teamNames={teamNames}
          dispatch={this.props.dispatch.bind(this)}
          dpWindow={dpWindow}
      />
    );
  }
}
