import React from "react";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";

import * as TaskActions from "../Actions/TaskListActions";

const TaskFilterHover = React.createClass({

  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function(evt) {
    this.props.closeWindow();
  },

  render: function() {
    const radioOptions = [
      {value: 'all', label: <span>All</span>},
      {value: 'done', label: <span>Done</span>},
      {value: 'undone', label: <span>Not Done</span>}
    ];

    return (<div style={{top: 95}} className="sidebar-hover hide-indicator">
        <div className="sidebar-hover-content">
          <div className="sidebar-hover-header">
            <i className="fa fa-tags"/> <span className="title">Filter</span>
          </div>
          <Formsy.Form onSubmit={this.props.applyFilter}>
            <div className="sidebar-hover-content-box">
              <h2>Status</h2>
              <div className="sidebar-hover-checkbox-collection inline-radio">
                <FRC.RadioGroupDeskPRO
                  layout="horizontal"
                  name="done"
                  type="inline"
                  options={radioOptions}
                  value={'all'}
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

module.exports = TaskFilterHover;
