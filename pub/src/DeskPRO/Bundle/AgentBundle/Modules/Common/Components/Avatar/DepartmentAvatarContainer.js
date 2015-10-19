import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import uuid from 'node-uuid';
import * as actions from '../../RecordStores/Actions/avatarActions';
import * as selectors from '../../RecordStores/Selectors/avatarSelectors';
import { Avatar } from './Avatar';
import { chooseColor } from './colors';

@connect(state => ({
  avatars: selectors.departmentAvatarsStateSelector(state).get('records')
}))
export class DepartmentAvatarContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    department: PropTypes.object.isRequired,
    size: PropTypes.any,
    avatars: PropTypes.object.isRequired
  };

  render() {
    const { size, avatars } = this.props;
    const department = this.props.department || Immutable.fromJS({});
    const avatar = avatars.get(String(department.get('id'))) || Immutable.fromJS({});

    const props = {
      size,
      url: avatar.get('url'),
      urlPattern: avatar.get('url_pattern'),
      isFallback: avatar.get('is_fallback'),
      fallbackText: this.getDepartmentFallbackText(),
      color: chooseColor(department.get('id'))
    };

    return (
      <Avatar {...props} />
    );
  }

  componentDidMount() {
    const { department, dispatch } = this.props;

    if (department && department.get('id')) {
      this.id = 'department-' + department.get('id');
      dispatch(actions.loadDepartmentAvatars(this.id, [department.get('id')]));
    }
  }

  componentWillUnmount() {
    this.props.dispatch(actions.releaseDepartmentAvatarsRequest(this.id));
  }

  getDepartmentFallbackText() {
    const department = this.props.department || Immutable.fromJS({});
    const name = department.get('title');
    const text = (name && name.length ? name[0] : '');

    return text ? text : '?';
  }
}
