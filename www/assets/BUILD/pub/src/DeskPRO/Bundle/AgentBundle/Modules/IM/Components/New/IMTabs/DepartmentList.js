import { PropTypes } from 'react';
import { AbstractList } from './AbstractList';
import { AvatarHelper } from './AvatarHelper';

export class DepartmentList extends AbstractList {

  static propTypes = {
    departments: PropTypes.object.isRequired
  };

  getAvatar = AvatarHelper.renderDepartmentAvatar;

  getItems() {
    return this.props.departments.map(department => this.getItem(department, 'department', 'title'));
  }
}
