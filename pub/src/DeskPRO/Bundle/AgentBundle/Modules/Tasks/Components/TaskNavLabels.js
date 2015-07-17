import React from "react";

import * as TaskActions from "../Actions/TaskListActions";

export default class TasksNavLabels extends React.Component {
    render() {
        const {labelList} = this.props;

        return (<section className="sidebar-list tasks-nav-labels">
            <div className="list-sidebar-title">
                Labels
            </div>

            <ul>{labelList.labelList ? labelList.labelList.map(function(object) {
                return <li key={object.id}>
                    <a href="#" className="item"><i className="fa fa-tag"/> {object.label}</a>
                </li>;
            }) : ''}
            </ul>
                </section>);
    }
}