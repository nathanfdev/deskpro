import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/crmNavActions';
import { agentTeamNamesSelector }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { userGroupNamesSelector }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/userGroupsSelectors';
import { Nav } from './Nav';

@connect(state => Object.assign({},
  state.CrmNav,
  {teamNames: agentTeamNamesSelector(state)},
  {groupNames: userGroupNamesSelector(state)},
  {dpWindow: state.Application.dpWindow}
))
export class NavContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    const { dispatch } = this.props;

    dispatch(actions.loadUsersTotalCount());
    dispatch(actions.loadGroupsCounts());
    dispatch(actions.loadOrganizationsTotalCount());
    dispatch(actions.loadAgentsTotalCount());
    dispatch(actions.loadTeamsCounts());
    dispatch(actions.loadPersonLabels());
    dispatch(actions.loadOrganizationLabels());
  }

  render() {
    const {labels, users, organizations, agents, groupNames, teamNames, dpWindow, dispatch} = this.props;

    return (
      <Nav
          labels={labels}
          users={users}
          organizations={organizations}
          agents={agents}
          groupNames={groupNames}
          teamNames={teamNames}
          dispatch={dispatch}
          dpWindow={dpWindow}
      />
    );
  }
}
