import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

const groups = [
  { countKey: 'myTasksCount', filter: {agents: ['me']}, label: 'My Tasks' },
  { countKey: 'teamTasksCount', filter: {teams: ['me']}, label: 'My Team Tasks' },
  { countKey: 'deptTasksCount', filter: {departments: ['me']}, label: 'My Department Tasks' },
  { countKey: 'delegatedTasksCount', filter: {agents: ['not_me'], creator: 'me'}, label: 'My Delegated Tasks' },
  { countKey: 'unassignedTasksCount', filter: {agents: ['null'], teams: ['null'], departments: ['null']}, label: 'Unassigned Tasks' },
  { countKey: 'allTasksCount', filter: {done: 'all'}, label: 'All Tasks' }
];

export class Groups extends React.Component {

  static propTypes = {
    groupsState: PropTypes.object.isRequired
  };

  render() {
    const { groupsState } = this.props;

    return (
      <Section>
        <SectionHeader>Tasks</SectionHeader>
        <ul>
          {groups.map(group =>
            <ListItemContainer urlHash={group.label}
                               listOptions={group.filter}>

              <ListItem count={groupsState.get(group.countKey)}
                        label={group.label} />
            </ListItemContainer>
          )}
        </ul>
      </Section>
    );
  }
}
