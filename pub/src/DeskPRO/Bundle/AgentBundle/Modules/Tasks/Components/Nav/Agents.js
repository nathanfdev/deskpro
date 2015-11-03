import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import Immutable from 'immutable';

export class Agents extends React.Component {

  static propTypes = {
    agents: PropTypes.object.isRequired
  };

  render() {
    return (
      <Section>
        <SectionHeader>Agents</SectionHeader>
        <ul>
          {this.props.agents.map((agent, index) =>
            <ListItem count={0} key={index}>
              <div part="label">
                <PersonAvatar person={agent} size={16} /> {agent.get('name')}
              </div>
            </ListItem>
          )}
        </ul>
      </Section>
    );
  }
}
