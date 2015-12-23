import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { PersonAvatar } from 'DeskPRO/Component/Avatar/PersonAvatar';
import { ListItemContainer } from '../ListItemContainer';

export class Agents extends React.Component {

  static propTypes = {
    agents: PropTypes.object.isRequired,
    agentsCount: PropTypes.object.isRequired
  };

  render() {
    const { agents, agentsCount = [] } = this.props;
    const countMap = [];
    agentsCount.forEach(agentCount => {
      countMap[agentCount.get('agent_id')] = parseInt(agentCount.get('tasks_count'), 10);
    });

    return (
      <Section>
        <SectionHeader>Agents</SectionHeader>
        <ul>
          {agents.map((agent, index) =>
            <ListItemContainer key={index}
                               urlHash={`agent-${agent.get('id')}-${agent.get('name')}`}
                               listOptions={{assigned_agent: [agent.get('id')]}}>

              <ListItem count={countMap[agent.get('id')] || 0}>
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
