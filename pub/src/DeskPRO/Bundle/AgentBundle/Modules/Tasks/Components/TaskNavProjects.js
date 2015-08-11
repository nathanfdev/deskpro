import React from "react";
import ProjectCreateHover from "../Components/ProjectCreateHover";
import ComponentRootWrapper from "DeskPRO/Component/ComponentRootWrapper";
import { connect } from 'react-redux';

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

    createProject(model) {
        if (typeof model.projectId !== 'undefined') {
            this.props.dispatch(TaskActions.editProject({
                projectId : model.projectId,
                title : model.title,
                departments : model.departments,
                teams: model.teams,
                people : model.members
            }))
        } else {
            this.props.dispatch(TaskActions.createProject({
                title : model.title,
                departments : model.departments,
                teams : model.teams,
                people : model.members
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
                        />
                    </ComponentRootWrapper>
                </div>
                <div className="list-sidebar-title">Projects <a href="#" onClick={this.toggleWindow.bind(this)}><i className="fa fa-plus"/></a></div>
                <ul>{projectList.projectList ? projectList.projectList.map(function(object) {
                    return <li key={object.id}>
                        <div className="list-counter-bucket">
                            <a href="#" onClick={_this.toggleWindow.bind(_this, object)}><i className="fa fa-cog" /></a>
                            <a className="list-counter" href="#">
                                {object.remaining}
                            </a>
                        </div>
                        <a href="#" className="item" onmouseover="toggleCountBucket(this);" onClick={_this.props.switchTaskList.bind(_this, 'tasks?project=' + object.id)}><i
                            className="fa fa-book"/> {object.title} </a>
                    </li>;
                }) : ''}

                </ul>
            </section>);
    }
}
