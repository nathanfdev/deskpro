import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { Avatar } from './Avatar';
import { chooseColor } from './colors';

export class DepartmentAvatar extends React.Component {

  static propTypes = {
    department: PropTypes.object.isRequired,
    size: PropTypes.any
  };

  getDepartmentFallbackText() {
    const department = this.props.department || Immutable.fromJS({});
    const name = department.get('title');
    const text = (name && name.length ? name[0] : '');

    return text ? text : '?';
  }

  render() {
    const { size } = this.props;
    const department = this.props.department || Immutable.fromJS({});
    const avatar = department.get('avatar') || Immutable.fromJS({});

    const props = {
      size,
      color: chooseColor(department.get('id')),
      url: avatar.get('url'),
      urlPattern: avatar.get('url_pattern'),
      fallbackText: this.getDepartmentFallbackText()
    };

    return (
      <Avatar {...props} />
    );
  }
}
