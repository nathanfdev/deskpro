import React from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class Groups extends React.Component {

  render() {
    return (
      <Section>
        <SectionHeader>Tasks</SectionHeader>
        <ul>
          <ListItem count={0} label="My Tasks" />
          <ListItem count={0} label="My Team Tasks" />
          <ListItem count={0} label="My Department Tasks" />
          <ListItem count={0} label="Delegated Tasks" />
          <ListItem count={0} label="Unassigned Tasks" />
          <ListItem count={0} label="All Tasks" />
        </ul>
      </Section>
    );
  }
}
