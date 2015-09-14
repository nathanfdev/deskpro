import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/crmNavActions'
import { Nav } from './Nav';

@connect(state => state.CrmNav)
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
      this.props.dispatch(actions.loadGroups());
      this.props.dispatch(actions.loadTeams());
  }

  render() {
    const {labels, users, organizations, agents, groupNames, teamNames, dp_window} = this.props;

    return (
      <Nav
          labels={labels}
          users={users}
          organizations={organizations}
          agents={agents}
          groupNames={groupNames}
          teamNames={teamNames}
          dispatch={this.props.dispatch.bind(this)}
          dp_window={dp_window}
      />
    );
  }
}
