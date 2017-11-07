import PropTypes from 'prop-types';
import React from 'react';
import * as actions from '../../Actions/chatsActions';
import { AgentTeamAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';

export class TeamsListItem extends React.Component {
  static propTypes = {
    team:     PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  startChat = (id, type) => {
    this.props.dispatch(actions.startChat(id, type));
  };

  render() {
    return (
      <li>
        <a href="#"
          onClick={this.startChat.bind(null, this.props.team.get('id'), 'team')}
        >
          <AgentTeamAvatar agentTeam={this.props.team} size={22} />
          <span className="agent">{this.props.team.get('name')}</span>
        </a>
      </li>
    );
  }
}

