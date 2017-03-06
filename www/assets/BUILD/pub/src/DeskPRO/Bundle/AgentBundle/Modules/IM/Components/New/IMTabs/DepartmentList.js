import { PropTypes } from 'react';
import AbstractList from './AbstractList';
import AvatarHelper from './AvatarHelper';

class DepartmentList extends AbstractList {

  static propTypes = {
    departments: PropTypes.object.isRequired
  };

  getAvatar(item) { // eslint-disable-line class-methods-use-this
    return AvatarHelper.renderDepartmentAvatar(item, item.get('title'));
  }

  getItems() {
    return this
      .filterList(this.props.departments, 'title')
      .map(department => this.getItem(department, 'department', 'title'));
  }
}

export default DepartmentList;
