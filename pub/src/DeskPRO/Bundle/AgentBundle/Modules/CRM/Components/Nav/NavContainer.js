import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/crmNavActions'
import { Nav } from './Nav';

@connect(state => ({
  labels: {
    people: ['Apple', 'Avocado', 'Banana', 'Pear', 'Orange', 'Blueberry', 'Blackberry'],
    organizations: ['Apple', 'Samsung', 'Sony', 'HTC', 'Vertu', 'Nokia', 'Siemens', 'Blackberry'],
  },
  groups: {
    total: 230,
    items: [
      {count: 42, group: 1},
      {count: 188, group: 2},
    ],
  },
  organizations: {
    total: 31
  },
  teams: {
    total: 11,
    items: [
      {count: 3, group: 1},
      {count: 8, group: 2},
    ],
  },
  groupNames: {
    1: 'Group A',
    2: 'Group B',
  },
  teamNames: {
    1: 'Team One',
    2: 'Team Two',
  }
}))
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
  }

  render() {
    const {labels, groups, organizations, teams, groupNames, teamNames} = this.props;

    return (
      <Nav
          labels={labels}
          groups={groups}
          organizations={organizations}
          teams={teams}
          groupNames={groupNames}
          teamNames={teamNames}
      />
    );
  }
}
