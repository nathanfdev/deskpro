import React from "react";
import { connect } from 'redux/react';
import * as FeedbackActions from "../Actions/FeedbackListActions";

export default class FeedbackListFrame extends React.Component {
    render() {
        return (
            <section className="fee-list-frame dp-list-frame">
                <div className="ticket-list">
                    <div className="tickets-control-bar">Feedback List Frame</div>
                </div>
            </section>
        );
    }
}