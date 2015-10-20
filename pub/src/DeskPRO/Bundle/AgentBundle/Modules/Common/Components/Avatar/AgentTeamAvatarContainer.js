import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import uuid from 'node-uuid';
import * as actions from '../../RecordStores/Actions/avatarActions';
import * as selectors from '../../RecordStores/Selectors/avatarSelectors';
import { Avatar } from './Avatar';
import { chooseColor } from './colors';

@connect(state => ({
  avatars: selectors.agentTeamAvatarsStateSelector(state).get('records')
}))
export class AgentTeamAvatarContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    agentTeam: PropTypes.object.isRequired,
    size: PropTypes.any,
    avatars: PropTypes.object.isRequired
  };

  render() {
    const { size, avatars } = this.props;
    const agentTeam = this.props.agentTeam || Immutable.fromJS({});
    const avatar = avatars.get(String(agentTeam.get('id'))) || Immutable.fromJS({});

    const props = {
      size,
      url: avatar.get('url'),
      urlPattern: avatar.get('url_pattern'),
      isFallback: avatar.get('is_fallback'),
      fallbackText: this.getAgentTeamFallbackText(),
      color: chooseColor(agentTeam.get('id'))
    };

    return (
      <Avatar {...props} />
    );
  }

  componentDidMount() {
    const { agentTeam, dispatch } = this.props;

    if (agentTeam && agentTeam.get('id')) {
      this.id = 'agent-team-' + agentTeam.get('id');
      dispatch(actions.loadAgentTeamAvatars(this.id, [agentTeam.get('id')]));
    }
  }

  componentWillUnmount() {
    this.props.dispatch(actions.releaseAgentTeamAvatarsRequest(this.id));
  }

  getAgentTeamFallbackText() {
    const agentTeam = this.props.agentTeam || Immutable.fromJS({});
    const name = agentTeam.get('name');
    const text = (name && name.length ? name[0] : '');

    return text ? text : '?';
  }
}
