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
    const doneOptions = [
      {value: 'all', label: <span>All</span>},
      {value: 'done', label: <span>Done</span>},
      {value: 'undone', label: <span>Not Done</span>}
    ];

    const attachmentOptions = [
      {value: 'all', label: <span>Any</span>},
      {value: 'has', label: <span>Has Attachments</span>},
      {value: 'none', label: <span>No Attachments</span>}
    ];

    let departments = [];
    let teams = [];
    let agents = [];
    let projects = [];
    let labels = [];

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

    if (typeof this.props.labels !== 'undefined' && this.props.labels !== null) {
      this.props.labels.forEach(function(object){
        labels.push({value: object.label, label: object.label});
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
                  options={doneOptions}
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
                  value={filter && filter.departments ? filter.departments : []}
                  multiple
                  /> : ''}
              </div>
              <div className="sidebar-hover-checkbox-collection">
                {teams ? <FRC.CheckboxGroupDeskPRO
                  name="teams"
                  label="Teams"
                  options={teams}
                  value={filter && filter.teams ? filter.teams : []}
                  multiple
                  /> : ''}
              </div>
              <div className="sidebar-hover-checkbox-collection">
                {agents ? <FRC.CheckboxGroupDeskPRO
                  name="agents"
                  label="Agents"
                  options={agents}
                  value={filter && filter.agents ? filter.agents : []}
                  multiple
                  /> : ''}
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Projects</h2>
              <div className="sidebar-hover-checkbox-collection">
                {projects ? <FRC.CheckboxGroupDeskPRO
                  name="projects"
                  label="Projects"
                  options={projects}
                  value={filter && filter.projects ? filter.projects : []}
                  multiple
                  /> : ''}
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Labels</h2>
              <div className="sidebar-hover-checkbox-collection">
                {labels ? <FRC.CheckboxGroupDeskPRO
                  name="labels"
                  label="Labels"
                  options={labels}
                  value={filter && filter.labels ? filter.labels : []}
                  multiple
                  /> : ''}
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Attachments</h2>
              <div className="sidebar-hover-checkbox-collection inline-radio">
                <FRC.RadioGroupDeskPRO
                  layout="horizontal"
                  name="has_attachments"
                  type="inline"
                  options={attachmentOptions}
                  value={filter && filter.has_attachments ? filter.has_attachments : 'all'}
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
