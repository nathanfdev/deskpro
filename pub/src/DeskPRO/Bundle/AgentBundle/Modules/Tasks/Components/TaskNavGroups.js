import React from "react";

import * as TaskActions from "../Actions/TaskListActions";

export default class TasksNavGroups extends React.Component {
    render() {
        const {taskList} = this.props;

        return (<section className="sidebar-list tasks-nav-groups">
                    <div className="list-sidebar-title">Tasks</div>
                    <ul>
                        <li>
                            <div className="list-counter-bucket">
                                <a className="list-counter" href="#"
                                   onclick="showFilterOptions(this); return false;">{taskList.myTaskCount}</a>
                            </div>
                            <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks?assigned=me', 1)} onmouseover="toggleCountBucket(this);">My Tasks</a>
                        </li>

                        <li>
                            <div className="list-counter-bucket">
                                <a href="#" className="list-counter"
                                   onclick="showFilterOptions(this); return false;">{taskList.teamTaskCount}</a>
                            </div>
                            <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks?assigned_team=me', 1)}>My Team Tasks</a>
                        </li>

                        <li>
                            <div className="list-counter-bucket">
                                <a href="#" className="list-counter"
                                   onclick="showFilterOptions(this); return false;">{taskList.deptTaskCount}</a>
                            </div>
                            <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks?assigned_department=me', 1)}>My Department Tasks</a>
                        </li>

                        <li>
                            <div className="list-counter-bucket">
                                <a href="#" className="list-counter"
                                   onclick="showFilterOptions(this); return false;">{taskList.delegatedTaskCount}</a>
                            </div>
                            <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks?assigned=not_me&creator=me', 1)}>Delegated Tasks</a>
                        </li>

                        <li>
                            <div className="list-counter-bucket">
                                <a href="#" className="list-counter"
                                   onclick="showFilterOptions(this); return false;">{taskList.unassignedTaskCount}</a>
                            </div>
                            <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks?assigned=null&assigned_team=null&assigned_department=null', 1)}>Unassigned Tasks</a>
                        </li>

                        <li>
                            <div className="list-counter-bucket">
                                <a href="#" className="list-counter"
                                   onclick="showFilterOptions(this); return false;">{taskList.taskCount}</a>
                            </div>
                            <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks', 1)}>All Tasks</a>
                        </li>
                    </ul>
                </section>);
    }
}
