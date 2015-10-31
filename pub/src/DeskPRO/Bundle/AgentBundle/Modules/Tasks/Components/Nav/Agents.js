import React from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import Immutable from 'immutable';

export class Agents extends React.Component {

  render() {
    return (
      <Section>
        <SectionHeader>Agents</SectionHeader>
        <ul>
          <ListItem count={0}>
            <div part="label">
              <PersonAvatar person={Immutable.fromJS({})} size={16} /> Person Name
            </div>
          </ListItem>
        </ul>
      </Section>
    );
  }
}
