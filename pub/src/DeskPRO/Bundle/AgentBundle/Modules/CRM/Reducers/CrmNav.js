import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/crmNavActions';

export default class CrmNav extends Reducer {
  getInitialState() {
    return {
      labels: {
        person:        [/* string */],
        organization: [/* string */],
      },
      users: {
        total: 0,
        groups: [/* {count, group} */],
      },
      organizations: {
        total: 0
      },
      agents: {
        total: 0,
        teams: [/* {count, group} */],
      },
      groupNames: {/* id: name */},
      teamNames:  {/* id: name */}
    };
  }

  registerHandlers() {
    this
        .r(actions.loadUsersTotalCount, this.usersTotalCountLoaded)
        .r(actions.loadGroupsCounts, this.groupsCountsLoaded)
        .r(actions.loadOrganizationsTotalCount, this.organizationsTotalCountLoaded)
        .r(actions.loadAgentsTotalCount, this.agentsTotalCountLoaded)
        .r(actions.loadTeamsCounts, this.teamsCountsLoaded)
        .r(actions.loadPersonLabels, this.personLabelsLoaded)
        .r(actions.loadOrganizationLabels, this.organizationLabelsLoaded)
        .r(actions.loadGroups, this.groupsLoaded)
        .r(actions.loadTeams, this.teamsLoaded)
    ;
  }

  usersTotalCountLoaded(prev, {payload}) {
    const next = {...prev};
    next.users.total = payload;

    return next;
  }

  groupsCountsLoaded(prev, {payload}) {
    const next = {...prev};
    next.users.groups = payload;

    return next;
  }

  organizationsTotalCountLoaded(prev, {payload}) {
    const next = {...prev};
    next.organizations.total = payload;

    return next;
  }

  agentsTotalCountLoaded(prev, {payload}) {
    const next = {...prev};
    next.agents.total = payload;

    return next;
  }

  teamsCountsLoaded(prev, {payload}) {
    const next = {...prev};
    next.agents.teams = payload;

    return next;
  }

  personLabelsLoaded(prev, {payload}) {
    const next = {...prev};
    next.labels.person = payload;

    return next;
  }

  organizationLabelsLoaded(prev, {payload}) {
    const next = {...prev};
    next.labels.organization = payload;

    return next;
  }

  groupsLoaded(prev, {payload}) {
    const next = {...prev};
    next.groupNames = {};
    payload.forEach(group => next.groupNames[group.id] = group.title);

    return next;
  }

  teamsLoaded(prev, {payload}) {
    const next = {...prev};
    next.teamNames = {};
    payload.forEach(team => next.teamNames[team.id] = team.name);

    return next;
  }
}
