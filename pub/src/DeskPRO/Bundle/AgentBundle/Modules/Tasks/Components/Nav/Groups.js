import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

const groups = [
  { countKey: 'myTasksCount', filter: {group: 'my'}, label: 'My Tasks' },
  { countKey: 'teamTasksCount', filter: {group: 'team'}, label: 'My Team Tasks' },
  { countKey: 'deptTasksCount', filter: {group: 'department'}, label: 'My Department Tasks' },
  { countKey: 'delegatedTasksCount', filter: {group: 'delegated'}, label: 'My Delegated Tasks' },
  { countKey: 'unassignedTasksCount', filter: {group: 'unassigned'}, label: 'Unassigned Tasks' },
  { countKey: 'allTasksCount', filter: {group: 'all'}, label: 'All Tasks' }
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
            <ListItemContainer label={group.label}
                               listOptions={{navItem: group.filter}}>

              <ListItem count={groupsState.get(group.countKey)}
                        label={group.label} />
            </ListItemContainer>
          )}
        </ul>
      </Section>
    );
  }
}
