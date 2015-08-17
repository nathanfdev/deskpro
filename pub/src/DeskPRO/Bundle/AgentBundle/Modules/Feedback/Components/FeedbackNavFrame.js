import React from "react";
import { connect } from 'redux/react';
import $ from "jquery";

import * as FeedbackListActions from "../Actions/FeedbackListActions";
import FeedbackNavPending from "../Components/FeedbackNavPending.js";

@connect(state => ({
    user: state.user,
    dp_window: state.dp_window,
    feedbackToValidate: state.feedbackToValidate
}))
export default
class FeedbackNavFrame extends React.Component {
    constructor(props) {
        super(props);
        const { dispatch } = this.props;
        this.props.dispatch(FeedbackListActions.feedbackToValidate());
    }


    switchTaskList(identifier, event) {
        this.props.dispatch(TaskActions.loadTaskList(identifier));
        $('.sidebar-list a.item, .sidebar-list a.item-label').removeClass('active');
        $(event.target).closest('a').addClass('active');
    }

    render() {
        const { feedbackToValidate } = this.props;

        return (
            <section className="task-nav-frame dp-nav-frame">
                <div className="sidebar-wrapper" id="sidebar-wrapper">
                    <a className="collapse-button" href="#" onclick="resizePanels('hide_filters');"><i
                        className="fa fa-angle-right"/></a>

          <span className="collapse-controls" onclick="resizePanels('hide_filters');">
            <span className="disc"/>
            <span className="disc"/>
            <i className="fa fa-caret-right"/>
            <span className="disc"/>
            <span className="disc"/>
          </span>
                    <aside className="sidebar has-tabs" id="sidebar">

                        <div className="sidebar-title">
              <span className="sidebar-type-icon">
                <i className="fa fa-check-square-o"/>
                <span className="help"><i className="fa fa-question"/></span>
              </span>

                            <h1>Feedback</h1>
                            <hr/>
                            <a href="#" className="slider-control"></a>
                        </div>
                        <div className="sidebar-list sidebar-list-filters">
                            <FeedbackNavPending feedbackToValidate={feedbackToValidate}
                                                switchFeedbackList={this.switchFeedbackList.bind(this)}/>
                        </div>

                    </aside>
                </div>
            </section>
        );
    }
}
