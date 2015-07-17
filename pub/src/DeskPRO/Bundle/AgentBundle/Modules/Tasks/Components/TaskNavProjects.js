import React from "react";

import * as TaskActions from "../Actions/TaskListActions";

export default class TasksNavProjects extends React.Component {
    render() {
        const {projectList} = this.props;

        return (<section className="sidebar-list tasks-nav-projects">
                <div className="list-sidebar-title">Projects</div>
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
