import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { Avatar } from './Avatar';
import { chooseColor } from './colors';

export class DepartmentAvatar extends React.Component {

  static propTypes = {
    department: PropTypes.object.isRequired,
    size:       PropTypes.number,
    className:  PropTypes.string
  };

  static defaultProps = {
    className: ''
  };

  getDepartmentFallbackText() {
    const department = this.props.department || Immutable.fromJS({});
    const name = department.get('title');
    const text = (name && name.length ? name[0] : '');

    return text || '?';
  }

  render() {
    const { size, className } = this.props;
    const department = this.props.department || Immutable.fromJS({});
    const avatar = department.get('avatar') || Immutable.fromJS({});

    const props = {
      size,
      color:      chooseColor(department.get('id')),
      url:        avatar.get('url'),
      urlPattern: avatar.get('url_pattern'),
      text:       this.getDepartmentFallbackText(),
      className
    };

    return (
      <Avatar {...props} />
    );
  }
}

export default DepartmentAvatar;
