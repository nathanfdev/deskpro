import React from "react";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";
import Picker from "anytime";
import Moment from "moment";
import $ from 'jquery';

import * as TaskActions from "../Actions/TaskListActions";

const TaskOrderHover = React.createClass({

  mixins: [
    require('react-onclickoutside')
  ],

  getInitialState: function() {
    return {
      filterDates: {}
    };
  },

  handleClickOutside: function(evt) {
    this.props.closeWindow();
  },

  componentDidMount: function() {

  },

  render: function() {
    const direction = this.props.direction;
    const order = this.props.order;

    const sortOptions = [
      {value: 'asc', label: <span>Ascending</span>},
      {value: 'desc', label: <span>Descending</span>}
    ];

    const orderByOptions = [
      {value: 'list', label: 'List'},
      {value: 'project', label: 'Project'},
      {value: 'due', label: 'Due Date'},
      {value: 'done', label: 'Completed Date'},
      {value: 'created', label: 'Created Date'},
      {value: 'assignee', label: 'Assignee'}
    ];

    return (<div style={{top: 94}} className="sidebar-hover hide-indicator">
        <div className="sidebar-hover-content">
          <div className="sidebar-hover-header">
            <i className="fa fa-tags"/> <span className="title">Order</span>
          </div>
          <Formsy.Form onSubmit={this.props.applyOrder}>
            <div className="sidebar-hover-content-box">
              <h2>Order By</h2>
              <FRC.Select
                layout="horizontal"
                name="order"
                options={orderByOptions}
                value={order ? order : 'due'}
              />
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Direction</h2>
              <div className="sidebar-hover-checkbox-collection inline-radio">
                <FRC.RadioGroupDeskPRO
                  layout="horizontal"
                  name="direction"
                  type="inline"
                  options={sortOptions}
                  value={direction ? direction : 'asc'}
                />
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <button type="submit" value="Apply" className="button">Apply</button> <a href="#" className="button">Clear</a>
            </div>
          </Formsy.Form>
        </div>
      </div>
    );
  }
});

module.exports = TaskOrderHover;
