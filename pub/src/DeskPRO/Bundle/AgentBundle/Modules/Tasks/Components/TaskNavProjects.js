import React from "react";
import ProjectCreateHover from "../Components/ProjectCreateHover";
import ComponentRootWrapper from "../../Application/Components/ComponentRootWrapper";

import * as TaskActions from "../Actions/TaskListActions";

export default class TasksNavProjects extends React.Component {
    constructor(props) {
        super(props);
        this.toggleWindow = this.toggleWindow.bind(this);
        this.state = {
            newProject: false
        }
    }

    toggleWindow() {
        this.setState({
            newProject: !this.state.newProject
        });
    }

    render() {
        const {projectList, agentList, teamList, departmentList} = this.props;

        return (<section className="sidebar-list tasks-nav-projects">
                <div>
                    <ComponentRootWrapper open={this.state.newProject}><ProjectCreateHover agentList={agentList} teamList={teamList} departmentList={departmentList} /></ComponentRootWrapper>
                </div>
                <div className="list-sidebar-title">Projects <a href="#" onClick={this.toggleWindow}><i className="fa fa-plus"/></a></div>
                <ul>{projectList.projectList ? projectList.projectList.map(function(object) {
                    return <li key={object.id}>
                        <div className="list-counter-bucket">
                            <a className="list-counter" href="#"
                               onclick="showFilterOptions(this); return false;">{object.tasks.length}</a>
                        </div>
                        <a href="#" className="item" onmouseover="toggleCountBucket(this);"><i
                            className="fa fa-book"/> {object.title}</a>
                    </li>;
                }) : ''}

                </ul>
            </section>);
    }
}
