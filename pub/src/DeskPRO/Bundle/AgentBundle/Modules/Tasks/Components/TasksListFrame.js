import React from "react";
import { connect } from 'redux/react';
import $ from 'jquery';
import * as TaskActions from "../Actions/TaskListActions";
import { IntlMixin, FormattedDate } from "react-intl";

@connect(state => ({
  taskFrameList: state.taskFrameList
}))
export default class TasksListFrame extends React.Component {
  constructor(props) {
    super(props);

    this.intl = IntlMixin;
  }

  toggleDone(done, taskId, reload) {
    let newValues = {
      taskId : taskId,
      is_done : !done
    };

    if (newValues.is_done === true) {
      newValues['percent_complete'] = 100;
    }

    this.props.dispatch(TaskActions.editTask(newValues, reload));
  }

  render() {
    const {taskFrameList} = this.props;
    const _this = this;
    return (
      <section className="task-list-frame dp-list-frame">
      <div className="ticket-list">

        <div className="tickets-control-bar">

          <div className="bulk-edit-control">
            <a href="#">
              <span className="checkbox"><i className="fa fa-check" /></span>
            </a>
            <span className="count" style={{display: "none"}}><span>14</span></span>
          </div>

          <span className="ticket-controls-default">
            <a href="#" className="ticket-control-button">
              <span className="title">Order by:</span>
              <span className="focus">Date</span>
              <span className="down">Asc <i className="fa fa-caret-down" /></span>
            </a>

            <a href="#" className="ticket-control-button">
              <span className="title">Filter by:</span>
              <span className="focus">12</span>
              <span className="down">Completed <i className="fa fa-caret-down" /></span>
            </a>

            <a href="#" className="ticket-control-button">
              <span className="title">View:</span>
              <span className="multi">
                List
                <span className="multi-down"><i className="fa fa-caret-down" /></span>
              </span>
            </a>
          </span>

          <span className="ticket-controls-bulk-editing">
            <a href="#">
              <span>Assign</span>
              <hr />
              <i className="fa fa-caret-down" />
            </a>

            <a href="#">
              <span>Statuses</span>
              <hr />
              <i className="fa fa-caret-down" />
            </a>

            <a href="#">
              <span>Macros</span>
              <hr />
              <i className="fa fa-caret-down" />
            </a>

            <a href="#">
              <span><i className="fa fa-reply" /></span>
              <hr />
              <i className="fa fa-caret-down" />
            </a>

            <a href="#">
              <span><i className="fa fa-asterisk" /></span>
              <hr />
              <i className="fa fa-caret-down" />
            </a>

            <hr />

            <a href="#" className="active">
              <span>GO</span>
            </a>

            <a href="#" className="cancel">
              <span>Cancel</span>
            </a>
          </span>
        </div>

        {taskFrameList.taskFrameList ? taskFrameList.taskFrameList.map(function(object) {
          let cardClass = object.is_done ? "card task-card task-card-completed" : "card task-card";
          let doneButton = object.is_done ? <span>Done <i className="fa fa-check" /></span> : "Mark Done";

          return <div className={cardClass} key={object.id}>
            <div className="card-status-bar status-bar-left"></div>
            <div className="card-status-bar status-bar-right"></div>

            <div className="card-checkbox">
              <span className="checkbox"><i className="fa fa-check"/></span>
            </div>

            <div className="top-right-box">
              <span className="text">Carlton Bush</span> <span className="chat-avatar"
                                                               style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
            </div>


            <div className="card-line">
              <span className="line-box card-task-mark" onClick={_this.toggleDone.bind(_this, object.is_done, object.id, taskFrameList.taskFrameSource)}>
                {doneButton}
              </span>

              <h1>{object.title}</h1>
            </div>

            <div className="card-line">
              <div className="task-extras">
                <div>5 <i className="fa fa-comment"/></div>
                <span className="disc"></span>

                <div>1/3 <i className="fa fa-folder-open"/></div>
              </div>

              <div className="task-properties">
                <div>
                  <i className="fa fa-calendar-o"/> Due: {object.date_due ? <FormattedDate
                  value={Date.parse(object.date_due)}
                  day="numeric"
                  month="long"
                  year="numeric" /> : 'N/A' }
                </div>

                <span className="disc"></span>

                <i className="fa fa-book"/> Project Title
                <span className="disc"></span>

                <div>
                  <i className="fa fa-link"/> <a href="#">Linked ticket</a>
                </div>
              </div>
            </div>
          </div>
        }) : '' }
      </div>
    </section>
    );
  }
}
