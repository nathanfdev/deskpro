import React from "react";
import { connect } from 'redux/react';

import * as FeedbackListActions from "../Actions/FeedbackListActions";

@connect(state => ({
    user: state.user,
    dp_window: state.dp_window
}))
export default class FeedbackNavFrame extends React.Component {
  constructor(props) {
    super(props);
    this.props.dispatch(FeedbackListActions.bogusAction());
  }
    render() {
        console.log(this.props);
        //const { taskList, projectList, agentList, labelList, departmentList, teamList, createdProject } = this.props;

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
                    </aside>
                </div>
            </section>
        );
    }
}
