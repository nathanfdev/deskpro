import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { colorLuminance } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import { Avatar } from './Avatar';

export class PersonAvatar extends React.Component {

  static propTypes = {
    person:      PropTypes.object.isRequired,
    size:        PropTypes.number,
    className:   PropTypes.string,
    color:       PropTypes.string,
    borderColor: PropTypes.string,
    title:       PropTypes.string,
    tooltipId:   PropTypes.string
  };

  static defaultProps = {
    className:   '',
    color:       '#CDD2D4',
    borderColor: colorLuminance('#CDD2D4')
  };

  getPersonFallbackText() {
    const person   = this.props.person || Immutable.fromJS({});
    const first    = person.get('first_name');
    const last     = person.get('last_name');
    const initials = (first && first.length ? first[0] : '') + (last && last.length ? last[0] : '');

    return initials || '?';
  }

  render() {
    const { size, person, className, color, borderColor, title, tooltipId } = this.props;
    const avatar = person && person.get('avatar') ? person.get('avatar') : Immutable.fromJS({});

    const props = {
      size,
      tooltipId,
      color,
      borderColor,
      urlPattern: avatar.get('url_pattern'),
      gravatar:   avatar.get('base_gravatar_url'),
      text:       this.getPersonFallbackText(),
      title,
      className
    };

    return <Avatar {...props} />;
  }
}

export default PersonAvatar;
