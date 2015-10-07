import React from 'react';
import TaskNavItemPeople from '../Components/TaskNavItemPeople';

export default class TasksNavPeople extends React.Component {
  static propTypes = {
    agentList: React.PropTypes.object
  }

  render() {
    const {agentList} = this.props;
    const _this = this;

    const agents = agentList ? agentList.get('agentList', false) : false;

    return (<section className="sidebar-list tasks-nav-people">
      <div className="list-sidebar-title">Agents</div>
      <ul>{agents ? agents.map((object) => {
        return (<TaskNavItemPeople key={object.id} agent={object}
                    filterTasks={_this.props.filterTasks.bind(_this)} />);
      }) : ''}
      </ul>
    </section>);
  }
}
