import { PropTypes } from 'react';
import { AbstractList } from './AbstractList';
import { AvatarHelper } from './AvatarHelper';

export class AgentTeamList extends AbstractList {

  static propTypes = {
    me:    PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired
  };

  getAvatar = AvatarHelper.renderAgentTeamAvatar;

  getItems() {
    return this.props.teams.map(team => this.getItem(team, 'team', 'name'));
  }
}
