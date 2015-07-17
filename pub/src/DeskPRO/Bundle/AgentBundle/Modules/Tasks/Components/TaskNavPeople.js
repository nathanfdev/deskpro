import React from "react";

import * as TaskActions from "../Actions/TaskListActions";

export default class TasksNavPeople extends React.Component {
    render() {
        const {agentList} = this.props;

        return (<section className="sidebar-list tasks-nav-people">
                <div className="list-sidebar-title">People</div>
                <ul>{agentList.agentList ? agentList.agentList.map(function(object) {
                    return <li key={object.id}>
                        <div className="list-counter-bucket">
                            <a className="list-counter" href="#"
                               onclick="showFilterOptions(this); return false;">{object.assigned_tasks.length}</a>
                        </div>
                        <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                                        <span className="list-icon"><span
                                            style={{backgroundImage: 'url(' + object.picture_blob.download_url + ')'}} className="avatar"/></span>
                            {object.name}
                        </a>
                    </li>;
                }) : ''}
                </ul>
            </section>);
    }
}