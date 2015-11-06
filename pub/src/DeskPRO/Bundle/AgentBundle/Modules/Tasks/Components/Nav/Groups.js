import React, { PropTypes } from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class Groups extends React.Component {

  static propTypes = {
    groups: PropTypes.object.isRequired
  };

  render() {
    const { groups } = this.props;

    return (
      <Section>
        <SectionHeader>Tasks</SectionHeader>
        <ul>
          <ListItemContainer count={groups.get('myTasksCount')}
                             label="My Tasks"
                             listOptions={{navItem: {custom_category: 1}}} />
          <ListItemContainer count={groups.get('teamTasksCount')}
                             label="My Team Tasks"
                             listOptions={{navItem: {custom_category: 2}}} />
          <ListItemContainer count={groups.get('deptTasksCount')}
                             label="My Department Tasks"
                             listOptions={{navItem: {custom_category: 3}}} />
          <ListItemContainer count={groups.get('delegatedTasksCount')}
                             label="Delegated Tasks"
                             listOptions={{navItem: {custom_category: 4}}} />
          <ListItemContainer count={groups.get('unassignedTasksCount')}
                             label="Unassigned Tasks"
                             listOptions={{navItem: {custom_category: 5}}} />
          <ListItemContainer count={groups.get('allTasksCount')}
                             label="All Tasks"
                             listOptions={{navItem: {custom_category: 6}}} />
        </ul>
      </Section>
    );
  }
}
