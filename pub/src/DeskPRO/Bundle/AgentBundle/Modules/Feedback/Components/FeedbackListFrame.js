import React from "react";
import { connect } from 'redux/react';
import * as FeedbackActions from "../Actions/FeedbackListActions";

export default class TasksListFrame extends React.Component {

    render() {
        console.log(this.props);
        const {taskFrameList} = this.props;
        const _this = this;
        return (
            <section className="task-list-frame dp-list-frame">
                <div className="ticket-list">
                    <div className="tickets-control-bar">Feedback List Frame</div>
                </div>
            </section>
        );
    }
}