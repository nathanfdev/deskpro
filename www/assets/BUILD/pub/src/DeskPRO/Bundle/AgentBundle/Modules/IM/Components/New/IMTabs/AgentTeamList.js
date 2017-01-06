import { PropTypes } from 'react';
import AbstractList from './AbstractList';
import AvatarHelper from './AvatarHelper';

class AgentTeamList extends AbstractList {

  static propTypes = {
    me:    PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired
  };

  getAvatar = AvatarHelper.renderAgentTeamAvatar;

  getItems() {
    return this.filterList(this.props.teams, 'name').map(team => this.getItem(team, 'team', 'name'));
  }
}

export default AgentTeamList;
