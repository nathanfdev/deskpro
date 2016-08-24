import React, { PropTypes } from 'react';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';

class AgentList extends React.Component {

  static propTypes = {
    agents: PropTypes.object.isRequired
  };

  static getItem(agent) {
    return (
      <ListElement classes={['im', 'agent']} image={agent.get('gravatar_url')}>
        <div className="header agent">{agent.get('name')}</div>
      </ListElement>
    );
  }

  getItems() {
    return this.props.agents.map(agent => AgentList.getItem(agent));
  }

  render() {
    return (
      <List classes={['im', 'middle', 'aligned', 'selection']}>
        {this.getItems()}
      </List>);
  }
}

export default AgentList;
