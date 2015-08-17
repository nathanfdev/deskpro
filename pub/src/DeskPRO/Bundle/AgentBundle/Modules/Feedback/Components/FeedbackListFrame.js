import React from "react";
import { connect } from 'redux/react';
import * as FeedbackActions from "../Actions/FeedbackListActions";

export default class FeedbackListFrame extends React.Component {
    constructor(props) {
        super(props);

        const { dispatch } = this.props;
    }

    render() {
        const {taskFrameList} = this.props;
        const _this = this;
        return (
            <section className="fee-list-frame dp-list-frame">
                <div className="ticket-list">
                    <div className="tickets-control-bar">Feedback List Frame</div>
                </div>
            </section>
        );
    }
}