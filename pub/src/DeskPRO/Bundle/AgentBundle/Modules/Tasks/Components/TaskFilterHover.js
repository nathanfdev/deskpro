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
    const filter = this.props.taskFilter.taskFilter;
    const radioOptions = [
      {value: 'all', label: <span>All</span>},
      {value: 'done', label: <span>Done</span>},
      {value: 'undone', label: <span>Not Done</span>}
    ];

    let departments = [];
    let teams = [];
    let agents = [];
    let projects = [];

    if (typeof this.props.departments !== 'undefined' && this.props.departments !== null) {
      this.props.departments.forEach(function(object) {
        departments.push({value: object.id, label:object.title});
      });
    }

    if (typeof this.props.teams !== 'undefined' && this.props.teams !== null) {
      this.props.teams.forEach(function(object) {
        teams.push({value: object.id, label:object.name});
      });
    }

    if (typeof this.props.agents !== 'undefined' && this.props.agents !== null) {
      this.props.agents.forEach(function(object) {
        let label = (<span>
          {object.picture_blob ? <span className="chat-avatar" style={{backgroundImage: 'url(' + object.picture_blob.download_url + ')'}}/> : '' }
            {object.name}
                </span>
        );
        agents.push({value: object.id, label:label});
      });
    }

    if (typeof this.props.projects !== 'undefined' && this.props.projects !== null) {
      this.props.projects.forEach(function(object){
        projects.push({value: object.id, label: object.title});
      });
    }
    
    return (<div style={{top: 94}} className="sidebar-hover hide-indicator">
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
                  value={filter && filter.done ? filter.done : 'all'}
                />
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Assignment</h2>
              <div className="sidebar-hover-checkbox-collection">
                {departments ? <FRC.CheckboxGroupDeskPRO
                  name="departments"
                  label="Departments"
                  options={departments}
                  multiple
                  /> : ''}
              </div>
              <div className="sidebar-hover-checkbox-collection">
                {departments ? <FRC.CheckboxGroupDeskPRO
                  name="teams"
                  label="Teams"
                  options={teams}
                  multiple
                  /> : ''}
              </div>
              <div className="sidebar-hover-checkbox-collection">
                {departments ? <FRC.CheckboxGroupDeskPRO
                  name="agents"
                  label="Agents"
                  options={agents}
                  multiple
                  /> : ''}
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Projects</h2>
              <div className="sidebar-hover-checkbox-collection">
                {departments ? <FRC.CheckboxGroupDeskPRO
                  name="projects"
                  label="Projects"
                  options={projects}
                  value={filter && filter.projects ? filter.projects : []}
                  multiple
                  /> : ''}
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
