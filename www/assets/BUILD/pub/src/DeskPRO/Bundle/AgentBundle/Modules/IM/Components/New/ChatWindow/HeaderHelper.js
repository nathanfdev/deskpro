import React from 'react';

export default class HeaderHelper
{
  constructor(props) {
    this.props = props;
  }

  setProps(props) {
    this.props = props;
  }

  getAgentHeader(chat) {
    const { agents, openGroupDrawer } = this.props;
    const agentId = this.getAgentId(chat);

    const agent =  agents.get(agentId);
    return [
      this.getAgentHeaderText(chat),
      <i className="icon group add" onClick={() => { openGroupDrawer([agent.get('id')]); }} />
    ];
  }

  getAgentId(chat) {
    let agentId;

    for (const id of chat.get('agents')) {
      if (id !== this.props.me.get('id')) {
        agentId = id;
        break;
      }
    }

    return agentId;
  }

  getAgentHeaderText(chat) {
    const agentId = this.getAgentId(chat);
    return this.props.agents.getIn([agentId, 'name']);
  }

  getDepartmentHeader(chat) {
    return this.props.departments.getIn([chat.getIn(['departments', 0]), 'title']);
  }

  getAgentTeamHeader(chat) {
    return this.props.teams.getIn([chat.getIn(['agent_teams', 0]), 'name']);
  }

  getHeaderText(textMode = false) {
    const { current } = this.props;
    let header;
    switch (current.get('chat_type')) {
      case 'agent':
        header = textMode ? this.getAgentHeaderText(current) : this.getAgentHeader(current);
        break;
      case 'department':
        header = this.getDepartmentHeader(current);
        break;
      case 'team':
        header = this.getAgentTeamHeader(current);
        break;
      case 'group':
        header = current.get('name');
        break;
      case 'everyone':
        header = 'Everyone';
        break;
      default:
        header = 'some im';
        break;
    }

    return header;
  }
}
