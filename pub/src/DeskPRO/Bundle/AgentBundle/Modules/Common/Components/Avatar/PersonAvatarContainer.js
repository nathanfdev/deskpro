import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import uuid from 'node-uuid';
import * as actions from '../../RecordStores/Actions/avatarActions';
import * as selectors from '../../RecordStores/Selectors/avatarSelectors';
import { Avatar } from './Avatar';

@connect(state => ({
  avatars: selectors.personAvatarsStateSelector(state).get('records')
}))
export class PersonAvatarContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    person: PropTypes.object.isRequired,
    size: PropTypes.any,
    avatars: PropTypes.object.isRequired
  };

  render() {
    this.id = uuid();

    const { size, person, avatars } = this.props;
    const avatar = person && avatars.get(String(person.get('id'))) || Immutable.fromJS({});

    const props = {
      size,
      url: avatar.get('url'),
      urlPattern: avatar.get('url_pattern'),
      gravatar: avatar.get('gravatar'),
      isFallback: avatar.get('is_fallback'),
      fallbackText: this.getPersonFallbackText(),
      color: '#CDD2D4'
    };

    return (
      <Avatar {...props} />
    );
  }

  componentDidMount() {
    const { person, dispatch } = this.props;

    if (person && person.get('id')) {
      dispatch(actions.loadPersonAvatars(this.id, [person.get('id')]));
    }
  }

  componentWillUnmount() {
    this.props.dispatch(actions.releasePersonAvatarsRequest(this.id));
  }

  getPersonFallbackText() {
    const person = this.props.person || Immutable.fromJS({});
    const first = person.get('first_name');
    const last = person.get('last_name');
    const initials = (first && first.length ? first[0] : '') + (last && last.length ? last[0] : '');

    return initials ? initials : '?';
  }
}
