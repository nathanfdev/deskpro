import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { Avatar } from './Avatar';

export class PersonAvatar extends React.Component {

  static propTypes = {
    person: PropTypes.object.isRequired,
    size: PropTypes.number
  };

  getPersonFallbackText() {
    const person = this.props.person || Immutable.fromJS({});
    const first = person.get('first_name');
    const last = person.get('last_name');
    const initials = (first && first.length ? first[0] : '') + (last && last.length ? last[0] : '');

    return initials ? initials : '?';
  }

  render() {
    const { size, person } = this.props;
    const avatar = person.get('avatar') || Immutable.fromJS({});

    const props = {
      size,
      color: '#CDD2D4',
      urlPattern: avatar.get('url_pattern'),
      gravatar: avatar.get('base_gravatar_url'),
      text: this.getPersonFallbackText()
    };

    return (
      <Avatar {...props} />
    );
  }
}
