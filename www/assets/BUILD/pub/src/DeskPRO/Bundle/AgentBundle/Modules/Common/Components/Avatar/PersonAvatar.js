import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { Avatar } from './Avatar';

export class PersonAvatar extends React.Component {

  static propTypes = {
    person:    PropTypes.object.isRequired,
    size:      PropTypes.number,
    className: PropTypes.array,
    color:     PropTypes.string
  };

  static defaultProps = {
    className: [],
    color:     '#CDD2D4'
  };

  getPersonFallbackText() {
    const person = this.props.person || Immutable.fromJS({});
    const first = person.get('first_name');
    const last = person.get('last_name');
    const initials = (first && first.length ? first[0] : '') + (last && last.length ? last[0] : '');

    return initials || '?';
  }

  render() {
    const { size, person, className, color } = this.props;
    const avatar = person && person.get('avatar') ? person.get('avatar') : Immutable.fromJS({});

    const props = {
      size,
      color,
      urlPattern: avatar.get('url_pattern'),
      gravatar:   avatar.get('base_gravatar_url'),
      text:       this.getPersonFallbackText(),
      className
    };

    return <Avatar {...props} />;
  }
}

export default PersonAvatar;
