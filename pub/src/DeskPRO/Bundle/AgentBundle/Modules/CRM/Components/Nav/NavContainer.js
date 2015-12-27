import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/crmNavActions';
import { agentTeamNamesSelector }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { userGroupNamesSelector }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/userGroupsSelectors';
import { Nav } from './Nav';

@connect(state => {
  return {
    loaded: state.CRM.nav.getIn(['async', 'done']),
    teamNames: agentTeamNamesSelector(state),
    groupNames: userGroupNamesSelector(state),
    dpWindow: state.Application.dpWindow,
    users: state.CRM.nav.get('users'),
    organizations: state.CRM.nav.get('organizations'),
    agents: state.CRM.nav.get('agents'),
    labels: state.CRM.nav.get('labels')
  };
})
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    loaded: PropTypes.bool.isRequired,
    dpWindow: PropTypes.object.isRequired,
    users: PropTypes.object.isRequired,
    organizations: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    const { dispatch } = this.props;

    dispatch(actions.initialLoad());
    /*
     dispatch(actions.loadUsersTotalCount());
     dispatch(actions.loadGroupsCounts());
     dispatch(actions.loadOrganizationsTotalCount());
     dispatch(actions.loadAgentsTotalCount());
     dispatch(actions.loadTeamsCounts());
     dispatch(actions.loadPersonLabels());
     dispatch(actions.loadOrganizationLabels());
     */
  }

  render() {
    const {loaded, labels, users, organizations, agents, groupNames, teamNames, dpWindow, dispatch} = this.props;

    return (
      <Nav loaded={loaded}
           labels={labels}
           users={users}
           organizations={organizations}
           agents={agents}
           groupNames={groupNames}
           teamNames={teamNames}
           dispatch={dispatch}
           dpWindow={dpWindow}/>
    );
  }
}
