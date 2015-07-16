import React from "react";
import { connect } from 'redux/react';

import * as TaskActions from "../Actions/TaskListActions";
import TaskNavGroups from "../Components/TaskNavGroups";
import TaskNavProjects from "../Components/TaskNavProjects";
import TaskNavPeople from "../Components/TaskNavPeople";
import TaskNavLabels from "../Components/TaskNavLabels";

@connect(state => ({
    taskList: state.taskList,
    projectList: state.projectList,
    user: state.user
}))
export default class TasksNavFrame extends React.Component {
    constructor(props) {
        super(props);

        const { dispatch } = this.props;

        console.log(this.props);

        dispatch(TaskActions.loadTasks());
        dispatch(TaskActions.loadProjects());
    }

    render() {
        const { taskList, projectList } = this.props;

        return (<section className="task-nav-frame">
            <div className="sidebar-wrapper" id="sidebar-wrapper">
                <a className="collapse-button" href="#" onclick="resizePanels('hide_filters');"><i
                    className="fa fa-angle-right"/></a>

                <span className="collapse-controls" onclick="resizePanels('hide_filters');">
                    <span className="disc"/>
                    <span className="disc"/>
                    <i className="fa fa-caret-right"/>
                    <span className="disc"/>
                    <span className="disc"/>
                </span>
                <aside className="sidebar has-tabs" id="sidebar">

                    <div className="sidebar-title">
                        <span className="sidebar-type-icon">
                            <i className="fa fa-check-square-o"/>
                            <span className="help"><i className="fa fa-question"/></span>
                        </span>

                        <h1>Tasks</h1>
                        <hr/>
                        <a href="#" className="slider-control"></a>
                    </div>

                    <div className="sidebar-list sidebar-list-filters">
                        <TaskNavGroups taskList={taskList} />

                        <TaskNavProjects projectList={projectList} />

                        <TaskNavPeople />

                        <TaskNavLabels />

                    </div>
                </aside>
            </div>
        </section>);
    }
}
