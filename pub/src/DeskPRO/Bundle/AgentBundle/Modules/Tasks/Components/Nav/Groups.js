import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
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
          <ListItemContainer label="My Tasks">
            <ListItem count={groups.get('myTasksCount')}
                      label="My Tasks" />
          </ListItemContainer>

          <ListItemContainer label="My Team Tasks">
            <ListItem count={groups.get('teamTasksCount')}
                      label="My Team Tasks" />
          </ListItemContainer>

          <ListItemContainer label="My Department Tasks">
            <ListItem count={groups.get('deptTasksCount')}
                      label="My Department Tasks" />
          </ListItemContainer>

          <ListItemContainer label="Delegated Tasks">
            <ListItem count={groups.get('delegatedTasksCount')}
                      label="Delegated Tasks" />
          </ListItemContainer>

          <ListItemContainer label="Unassigned Tasks">
            <ListItem count={groups.get('unassignedTasksCount')}
                      label="Unassigned Tasks" />
          </ListItemContainer>

          <ListItemContainer label="All Tasks">
            <ListItem count={groups.get('allTasksCount')}
                      label="All Tasks" />
          </ListItemContainer>
        </ul>
      </Section>
    );
  }
}
