import PropTypes from 'prop-types';
import classNames from 'classnames';
import AbstractList from './AbstractList';
import AvatarHelper from './AvatarHelper';
import { getDepartmentAgents } from '../../../../Application/Actions/departmentActions';

class DepartmentList extends AbstractList {

  static propTypes = {
    departments: PropTypes.object.isRequired
  };

  getAvatar(item) { // eslint-disable-line class-methods-use-this
    return AvatarHelper.renderDepartmentAvatar(item, item.get('title'));
  }

  getItems() {
    return this.props.departments.toArray().map(department => this.getItem(department, 'department', 'title'));
  }

  getAgents(container) {
    return getDepartmentAgents(container).map(
      (agentId) => {
        if (agentId === this.props.me.get('id')) {
          return null;
        }

        const className = ['ui avatar image im'];
        const agent = this.props.agents.get(agentId);
        if (!agent) {
          return null;
        }
        if (!agent.get('online')) {
          className.push('offline');
        }

        return AvatarHelper.renderAgentAvatar(agent, 20, classNames(className), agent.get('name'));
      }
    );
  }
}

export default DepartmentList;
