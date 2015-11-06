import React, { PropTypes } from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { ListItemContainer } from './ListItemContainer';
import * as TasksActions from '../../Actions/tasksActions';

export class Agents extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    agents: PropTypes.object.isRequired
  };

  onSelectAgent = filter => {
    this.props.dispatch(TasksActions.applyListParams(filter));
  };

  renderItem(agent, index) {
    const filter = {agent: agent.get('id')};

    return (
      <ListItemContainer key={index}
                         label={`agent-${agent.get('id')}-${agent.get('name')}`}
                         onClick={this.onSelectAgent.bind(this, filter)}
                         count={0}>

        <div part="label">
          <PersonAvatar person={agent} size={16} /> {agent.get('name')}
        </div>
      </ListItemContainer>
    );
  }

  render() {
    return (
      <Section>
        <SectionHeader>Agents</SectionHeader>
        <ul>
          {this.props.agents.map((agent, index) => this.renderItem(agent, index))}
        </ul>
      </Section>
    );
  }
}
