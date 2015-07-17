import React from "react";

import * as TaskActions from "../Actions/TaskListActions";

export default class TasksNavPeople extends React.Component {
    render() {
        const {agentList} = this.props;

        console.log(agentList);

        return (<section className="tasks-nav-people">
            <div className="list-sidebar-title">People <a href="#" className="title-down"><i
                className="fa fa-caret-down"/></a></div>
                <ul>{agentList.agentList ? agentList.agentList.map(function(object) {
                    return <li key={object.id}>
                        <div className="list-counter-bucket">
                            <a className="list-counter" href="#"
                               onclick="showFilterOptions(this); return false;">{object.assigned_tasks.length}</a>
                        </div>
                        <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                                        <span className="list-icon"><span
                                            styles={{backgroundImage: 'url(./img/avatar6.png)'}} className="avatar"/></span>
                            {object.name}
                        </a>
                    </li>;
                }) : ''}
                </ul>
                </section>);
    }
}