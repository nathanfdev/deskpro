import React from "react";

import * as FeedbackActions from "../Actions/FeedbackListActions";

export default class FeedbackNavPending extends React.Component {
    render() {
        console.log(this.props);
        const {toValidate, commentsToReview} = this.props;
        return (
            <section className="sidebar-list feedback-nav-groups">
                <div className="list-sidebar-title">Pending</div>
                <ul>
                    <li>
                        <div className="list-counter-bucket">
                            <a className="list-counter" href="#"
                               onclick="showFilterOptions(this); return false;">{toValidate}</a>
                        </div>
                        <a href="#" className="item"
                           onClick={this.props.switchFeedback.bind(this, 'feedback?to_validate=1')}
                           onmouseover="toggleCountBucket(this);">To Validate</a>
                    </li>
                    <li>
                        <div className="list-counter-bucket">
                            <a className="list-counter" href="#"
                               onclick="showFilterOptions(this); return false;">{commentsToReview}</a>
                        </div>
                        <a href="#" className="item"
                           onClick={this.props.switchFeedback.bind(this, 'feedback?to_validate=1')}
                           onmouseover="toggleCountBucket(this);">Comments To Review</a>
                    </li>
                </ul>
            </section>
        );
    }
}
