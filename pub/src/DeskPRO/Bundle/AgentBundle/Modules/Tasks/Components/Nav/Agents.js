import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { ListItemContainer } from './ListItemContainer';

export class Agents extends React.Component {

  static propTypes = {
    onApplyListParams: PropTypes.func.isRequired,
    agents: PropTypes.object.isRequired
  };

  render() {
    return (
      <Section>
        <SectionHeader>Agents</SectionHeader>
        <ul>
          {this.props.agents.map((agent, index) =>
            <ListItemContainer key={index}
                               urlHash={`agent-${agent.get('id')}-${agent.get('name')}`}
                               listOptions={{agents: [agent.get('id')]}}>

              <ListItem count={0}>
                <div part="label">
                  <PersonAvatar person={agent} size={16} /> {agent.get('name')}
                </div>
              </ListItem>
            </ListItemContainer>
          )}
        </ul>
      </Section>
    );
  }
}
