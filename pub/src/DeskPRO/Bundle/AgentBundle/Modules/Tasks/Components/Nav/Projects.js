import React from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class Projects extends React.Component {

  render() {
    return (
      <Section>
        <SectionHeader>Projects</SectionHeader>
        <ul>
          <ListItem count={0}>
            <div part="label"><i className="fa fa-book"/>Example Project</div>
          </ListItem>
        </ul>
      </Section>
    );
  }
}
