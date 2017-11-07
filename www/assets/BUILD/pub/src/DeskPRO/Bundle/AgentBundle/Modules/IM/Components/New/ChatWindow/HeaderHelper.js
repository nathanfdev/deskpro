import PropTypes from 'prop-types';
import React from 'react';
import Isvg from 'react-inlinesvg';

export default class HeaderHelper {
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
      agent ? <GroupAdd onClick={() => { openGroupDrawer([agent.get('id')]); }} /> : null
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
    return this.props.agents.getIn([agentId, 'name'])
      ? this.props.agents.getIn([agentId, 'name'])
      : this.props.people.getIn([agentId, 'name']);
  }

  getDepartmentHeader(chat) {
    return this.props.departments.getIn([chat.getIn(['departments', 0]), 'title']);
  }

  getAgentTeamHeader(chat) {
    return this.props.teams.getIn([chat.getIn(['agent_teams', 0]), 'name']);
  }

  getEmptyHeader() {
    const { current } = this.props;
    let header;

    switch (current.get('chat_type')) {
      case 'agent':
        header = this.getAgentHeaderText(current);
        break;
      case 'department':
        header = `the ${this.getDepartmentHeader(current)} Department`;
        break;
      case 'team':
        header = `Team ${this.getAgentTeamHeader(current)}`;
        break;
      case 'group':
        header = `the ${current.get('name')} Group`;
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

function GroupAdd(props) {
  return (
    <span className="button group add" onClick={props.onClick}>
      <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/im/group-icon.svg`} />
    </span>
  );
}

GroupAdd.propTypes = {
  onClick: PropTypes.func.isRequired
};
