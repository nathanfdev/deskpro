import React from 'react';
import TaskNavItemPeople from '../Components/TaskNavItemPeople';

export default class TasksNavPeople extends React.Component {
  render() {
    const {agentList} = this.props;
    const _this = this;

    return (<section className="sidebar-list tasks-nav-people">
        <div className="list-sidebar-title">Agents</div>
        <ul>{agentList.agentList ? agentList.agentList.map((object) => {
            return (<TaskNavItemPeople key={object.id} agent={object}
                      filterTasks={_this.props.filterTasks.bind(_this)} />);
          }) : ''}
        </ul>
    </section>);
  }
}
