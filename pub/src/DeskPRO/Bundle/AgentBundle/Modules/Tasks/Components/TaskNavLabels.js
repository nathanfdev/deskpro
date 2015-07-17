import React from "react";

import * as TaskActions from "../Actions/TaskListActions";

export default class TasksNavLabels extends React.Component {
    render() {
        const {labelList} = this.props;

        return (<section className="sidebar-list sidebar-list-labels tasks-nav-labels">
            <div className="list-sidebar-title">
                Labels
            </div>

            <div className="sidebar-label-list">{labelList.labelList ? labelList.labelList.map(function(object) {
                return <a href="#" className="item-label" key={object.id}>{object.label}</a>;
            }) : ''}
            </div>
        </section>);
    }
}