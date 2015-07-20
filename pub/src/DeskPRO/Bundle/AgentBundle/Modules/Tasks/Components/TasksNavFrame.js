import React from "react";
import { connect } from 'redux/react';

import * as TaskActions from "../Actions/TaskListActions";
import TaskNavGroups from "../Components/TaskNavGroups";
import TaskNavProjects from "../Components/TaskNavProjects";
import TaskNavPeople from "../Components/TaskNavPeople";
import TaskNavLabels from "../Components/TaskNavLabels";
import TaskCreateHover from "../Components/TaskCreateHover";
import ComponentRootWrapper from "../Components/ComponentRootWrapper";

@connect(state => ({
    taskList: state.taskList,
    projectList: state.projectList,
    agentList:state.agentList,
    labelList:state.labelList,
    user: state.user
}))
export default class TasksNavFrame extends React.Component {
    constructor(props) {
        super(props);

        const { dispatch } = this.props;

        dispatch(TaskActions.loadTasks());
        dispatch(TaskActions.loadMyTasks());
        dispatch(TaskActions.loadProjects());
        dispatch(TaskActions.loadTeamTasks());
        dispatch(TaskActions.loadDepartmentTasks());
        dispatch(TaskActions.loadDelegatedTasks());
        dispatch(TaskActions.loadUnassignedTasks());
        dispatch(TaskActions.loadLabels());
        dispatch(TaskActions.loadAgents());
    }

    render() {
        const { taskList, projectList, agentList, labelList } = this.props;

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

                        <TaskNavPeople agentList={agentList} />

                        <TaskNavLabels labelList={labelList} />

                    </div>
                </aside>
            </div>
        </section>);
    }
}
