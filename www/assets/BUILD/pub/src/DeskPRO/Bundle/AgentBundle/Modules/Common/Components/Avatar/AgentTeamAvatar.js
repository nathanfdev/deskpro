import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { Avatar } from './Avatar';
import { chooseColor } from './colors';

export class AgentTeamAvatar extends React.Component {

  static propTypes = {
    agentTeam: PropTypes.object.isRequired,
    size:      PropTypes.oneOfType(PropTypes.number, PropTypes.string),
    classes:   PropTypes.array
  };

  static defaultProps = {
    classes: []
  };

  getAgentTeamFallbackText() {
    const agentTeam = this.props.agentTeam || Immutable.fromJS({});
    const name = agentTeam.get('name');
    const text = (name && name.length ? name[0] : '');

    return text || '?';
  }

  render() {
    const { size, classes } = this.props;
    const agentTeam = this.props.agentTeam || Immutable.fromJS({});
    const avatar = agentTeam.get('avatar') || Immutable.fromJS({});

    const props = {
      size,
      color:      chooseColor(agentTeam.get('id')),
      url:        avatar.get('url'),
      urlPattern: avatar.get('url_pattern'),
      text:       this.getAgentTeamFallbackText(),
      classes
    };

    return (
      <Avatar {...props} />
    );
  }
}
