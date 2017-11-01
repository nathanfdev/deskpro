import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { Avatar } from './Avatar';
import { chooseColor, darkerColor } from './colors';

export class AgentTeamAvatar extends React.Component {

  static propTypes = {
    agentTeam: PropTypes.object.isRequired,
    size:      PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
    className: PropTypes.string,
    title:     PropTypes.string,
    tooltipId: PropTypes.string
  };

  static defaultProps = {
    className: ''
  };

  getAgentTeamFallbackText() {
    const agentTeam = this.props.agentTeam || Immutable.fromJS({});
    const name      = agentTeam.get('name');
    const text      = (name && name.length ? name.substr(0, 2) : '');

    return text || '?';
  }

  render() {
    const { size, className, title, tooltipId } = this.props;
    const agentTeam = this.props.agentTeam || Immutable.fromJS({});
    const avatar    = agentTeam.get('avatar') || Immutable.fromJS({});

    const props = {
      size,
      tooltipId,
      color:       chooseColor(agentTeam.get('id')),
      borderColor: darkerColor(agentTeam.get('id')),
      url:         avatar.get('url'),
      urlPattern:  avatar.get('url_pattern'),
      text:        this.getAgentTeamFallbackText(),
      title,
      className
    };

    return (
      <Avatar {...props} />
    );
  }
}

export default AgentTeamAvatar;

