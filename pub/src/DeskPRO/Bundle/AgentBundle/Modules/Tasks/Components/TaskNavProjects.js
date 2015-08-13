import React from "react";
import ProjectCreateHover from "../Components/ProjectCreateHover";
import TaskNavItemProject from "../Components/TaskNavItemProject";
import ComponentRootWrapper from "DeskPRO/Component/ComponentRootWrapper";
import { connect } from 'redux/react';

import * as TaskActions from "../Actions/TaskListActions";

@connect(state => ({
    failedProject: state.failedProject
}))
export default class TasksNavProjects extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            showWindow: false,
            projectData: {}
        }
    }

    toggleWindow(project = {}) {
        this.setState({
            projectData: {}
        });

        if (this.state.showWindow === false) {
            this.setState({
                projectData: project
            });
        }

        this.setState({
            showWindow: !this.state.showWindow
        });
    }

    closeWindow() {
        this.setState({
            projectData: {},
            showWindow: false
        });
    }

    createProject(model) {
        if (typeof model.projectId !== 'undefined') {
            this.props.dispatch(TaskActions.editProject({
                projectId : model.projectId,
                title : model.title,
                departments : model.departments,
                teams: model.teams,
                agents : model.members
            }))
        } else {
            this.props.dispatch(TaskActions.createProject({
                title : model.title,
                departments : model.departments,
                teams : model.teams,
                agents : model.members
            }));
        }
    }

    render() {
        const {projectList, agentList, teamList, departmentList, createdProject} = this.props;
        // Workaround to bind toggleWindow to every edit link
        let _this = this;

        return (<section className="sidebar-list tasks-nav-projects">
                <div>
                    <ComponentRootWrapper open={this.state.showWindow}>
                        <ProjectCreateHover
                            createProject={this.createProject.bind(this)}
                            createdProject={createdProject}
                            agentList={agentList}
                            teamList={teamList}
                            departmentList={departmentList}
                            projectData={this.state.projectData}
                            closeWindow={this.closeWindow.bind(this)}
                        />
                    </ComponentRootWrapper>
                </div>
                <div className="list-sidebar-title">Projects <a href="#" onClick={this.toggleWindow.bind(this)}><i className="fa fa-plus"/></a></div>
                <ul>{projectList.projectList ? projectList.projectList.map(function(object) {
                    return <TaskNavItemProject key={object.id} project={object} switchTaskList={_this.props.switchTaskList.bind(this)} toggleWindow={_this.toggleWindow.bind(_this)} dispatch={_this.props.dispatch.bind(_this)} />;
                }) : ''}

                </ul>
            </section>);
    }
}
