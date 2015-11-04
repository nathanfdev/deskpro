import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

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
          <ListItem count={groups.get('myTasksCount')} label="My Tasks" />
          <ListItem count={groups.get('teamTasksCount')} label="My Team Tasks" />
          <ListItem count={groups.get('deptTasksCount')} label="My Department Tasks" />
          <ListItem count={groups.get('delegatedTasksCount')} label="Delegated Tasks" />
          <ListItem count={groups.get('unassignedTasksCount')} label="Unassigned Tasks" />
          <ListItem count={groups.get('allTasksCount')} label="All Tasks" />
        </ul>
      </Section>
    );
  }
}
