import React from 'react';
import Immutable from 'immutable';

export class Groups extends React.Component {

  renderItem(name, count, route) {
    const taskList = Immutable.fromJS({});
    const filterTasks = () => {};

    return (
      <li>
        <div className="list-counter-bucket">
          <a className="list-counter" href="#">{taskList.get(count, 0)}</a>
        </div>
        <a href="#" className="item" onClick={filterTasks.bind(this, route)}>{name}</a>
      </li>
    );
  }

  render() {
    return (
      <section className="sidebar-list tasks-nav-groups">
        <div className="list-sidebar-title">Tasks</div>
        <ul>
          {this.renderItem('My Tasks', 'myTaskCount', {agents: ['me']})}
          {this.renderItem('My Department Tasks', 'teamTaskCount', {teams: ['me']})}
          {this.renderItem('My Department Tasks', 'deptTaskCount', {departments: ['me']})}
          {this.renderItem('Delegated Tasks', 'delegatedTaskCount', {agents: ['not_me'], creator: 'me'})}
          {this.renderItem('Unassigned Tasks', 'unassignedTaskCount', {agents: ['null'], teams: ['null'], departments: ['null']})}
          {this.renderItem('All Tasks', 'taskCount', {done: 'all'})}
        </ul>
      </section>
    );
  }
}
