import React from "react";

import * as TaskActions from "../Actions/TaskListActions";

export default class TasksNavGroups extends React.Component {
    render() {
        const {taskList} = this.props;
        return (<section className="tasks-nav-groups">
                    <div className="list-sidebar-title">Tasks <a href="#" className="title-down"><i
                        className="fa fa-caret-down"/></a></div>
                    <ul>
                        <li>
                            <div className="list-counter-bucket">
                                <a className="list-counter" href="#"
                                   onclick="showFilterOptions(this); return false;">12</a>
                            </div>
                            <a href="#" className="item" onmouseover="toggleCountBucket(this);">My Tasks</a>
                        </li>

                        <li>
                            <div className="list-counter-bucket">
                                <a href="#" className="list-counter"
                                   onclick="showFilterOptions(this); return false;">34</a>
                            </div>
                            <a href="#" className="item">My Team Tasks</a>
                        </li>

                        <li>
                            <div className="list-counter-bucket">
                                <a href="#" className="list-counter"
                                   onclick="showFilterOptions(this); return false;">9</a>
                            </div>
                            <a href="#" className="item">Delegated Tasks</a>
                        </li>

                        <li>
                            <div className="list-counter-bucket">
                                <a href="#" className="list-counter"
                                   onclick="showFilterOptions(this); return false;">132</a>
                            </div>
                            <a href="#" className="item">Unassigned Tasks</a>
                        </li>

                        <li>
                            <div className="list-counter-bucket">
                                <a href="#" className="list-counter"
                                   onclick="showFilterOptions(this); return false;">{taskList.taskCount}</a>
                            </div>
                            <a href="#" className="item">All Tasks</a>
                        </li>
                    </ul>
                </section>);
    }
}