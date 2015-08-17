import React from "react";

import * as FeedbackActions from "../Actions/FeedbackListActions";

export default class FeedbackNavPending extends React.Component {
    render() {
        const {feedbackToValidate} = this.props;

        return (<section className="sidebar-list feedback-nav-groups">
            <div className="list-sidebar-title">Pending</div>
            /*<ul>
                <li>
                    <div className="list-counter-bucket">
                        <a className="list-counter" href="#"
                           onclick="showFilterOptions(this); return false;">{taskList.myTaskCount}</a>
                    </div>
                    <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks?assigned=me')} onmouseover="toggleCountBucket(this);">My Tasks</a>
                </li>

                <li>
                    <div className="list-counter-bucket">
                        <a href="#" className="list-counter"
                           onclick="showFilterOptions(this); return false;">{taskList.teamTaskCount}</a>
                    </div>
                    <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks?assigned_team=me')}>My Team Tasks</a>
                </li>

                <li>
                    <div className="list-counter-bucket">
                        <a href="#" className="list-counter"
                           onclick="showFilterOptions(this); return false;">{taskList.deptTaskCount}</a>
                    </div>
                    <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks?assigned_department=me')}>My Department Tasks</a>
                </li>

                <li>
                    <div className="list-counter-bucket">
                        <a href="#" className="list-counter"
                           onclick="showFilterOptions(this); return false;">{taskList.delegatedTaskCount}</a>
                    </div>
                    <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks?assigned=not_me&creator=me')}>Delegated Tasks</a>
                </li>

                <li>
                    <div className="list-counter-bucket">
                        <a href="#" className="list-counter"
                           onclick="showFilterOptions(this); return false;">{taskList.unassignedTaskCount}</a>
                    </div>
                    <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks?assigned=null&assigned_team=null&assigned_department=null')}>Unassigned Tasks</a>
                </li>

                <li>
                    <div className="list-counter-bucket">
                        <a href="#" className="list-counter"
                           onclick="showFilterOptions(this); return false;">{taskList.taskCount}</a>
                    </div>
                    <a href="#" className="item" onClick={this.props.switchTaskList.bind(this, 'tasks')}>All Tasks</a>
                </li>
            </ul>*/
        </section>);
    }
}
