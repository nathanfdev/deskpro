import PropTypes from 'prop-types';
import React from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { ListItemContainer } from '../ListItemContainer';

export class Agents extends React.Component {

  static propTypes = {
    agents:         PropTypes.object.isRequired,
    agentsCountMap: PropTypes.object.isRequired
  };

  render() {
    const { agents, agentsCountMap = [] } = this.props;
    return (
      <Section>
        <SectionHeader>Agents</SectionHeader>
        <ul>
          {agents.entrySeq().map(([index, agent]) =>
            <ListItemContainer
              key={index}
              urlHash={`agent-${agent.get('id')}-${agent.get('name')}`}
              listOptions={{ assigned_agent: [agent.get('id')] }}
            >
              <ListItem count={agentsCountMap[agent.get('id')] || 0}>
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
