import React from "react";

import * as TaskActions from "../Actions/TaskListActions";

export default class TasksNavLabels extends React.Component {
    render() {
        const {labelList} = this.props;

        return (<section className="sidebar-list sidebar-list-labels tasks-nav-labels">
            <div className="list-sidebar-title">
                Labels
            </div>

            <div className="sidebar-label-list sidebar-list">
                <ul>{labelList.labelCharacters ? labelList.labelCharacters.map(function(object) {
                    return (<li key={object}>
                        <span className="labelCharacter">{object}</span>
                        {labelList.labelList[object] ? labelList.labelList[object].map(function(label) {
                            return <a href="#" className="item-label" key={label.id}>{label.label}</a>
                        }): ''}
                    </li>)

                }) : ''}</ul>
            </div>
        </section>);
    }
}