import { PropTypes } from 'react';
import AbstractList from './AbstractList';
import AvatarHelper from './AvatarHelper';

class DepartmentList extends AbstractList {

  static propTypes = {
    departments: PropTypes.object.isRequired
  };

  getAvatar = AvatarHelper.renderDepartmentAvatar;

  getItems() {
    return this
      .filterList(this.props.departments, 'title')
      .map(department => this.getItem(department, 'department', 'title'));
  }
}

export default DepartmentList;
