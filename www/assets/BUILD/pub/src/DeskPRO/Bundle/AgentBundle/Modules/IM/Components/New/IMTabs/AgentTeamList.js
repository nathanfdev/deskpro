import PropTypes from 'prop-types';
import AbstractList from './AbstractList';
import AvatarHelper from './AvatarHelper';

class AgentTeamList extends AbstractList {

  static propTypes = {
    me:    PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired
  };

  getAvatar(item) { // eslint-disable-line class-methods-use-this
    return AvatarHelper.renderAgentTeamAvatar(item, item.get('name'));
  }

  getItems() {
    return this.props.teams.map(team => this.getItem(team, 'team', 'name'));
  }
}

export default AgentTeamList;
