import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { Avatar } from './Avatar';
import { chooseColor, darkerColor } from './colors';

export class DepartmentAvatar extends React.Component {

  static propTypes = {
    department: PropTypes.object.isRequired,
    size:       PropTypes.number,
    className:  PropTypes.string,
    title:      PropTypes.string,
    tooltipId:  PropTypes.string
  };

  static defaultProps = {
    className: ''
  };

  getDepartmentFallbackText() {
    const department = this.props.department || Immutable.fromJS({});
    const name       = department.get('title');
    const text       = (name && name.length ? name.substr(0, 2) : '');

    return text || '?';
  }

  render() {
    const { size, className, title, tooltipId } = this.props;
    const department = this.props.department || Immutable.fromJS({});
    const avatar     = department.get('avatar') || Immutable.fromJS({});

    const props = {
      size,
      tooltipId,
      color:       chooseColor(department.get('id')),
      borderColor: darkerColor(department.get('id')),
      url:         avatar.get('url'),
      urlPattern:  avatar.get('url_pattern'),
      text:        this.getDepartmentFallbackText(),
      title,
      className
    };

    return (
      <Avatar {...props} />
    );
  }
}

export default DepartmentAvatar;
