import React from "react";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";
import $ from "jquery";

import * as TaskActions from "../Actions/TaskListActions";

const ProjectCreateHover = React.createClass({

  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function(evt) {
    this.props.closeWindow();
  },

  getInitialState: function() {
    return {
      canSubmit: false,
      projectTitle: null,
      departments: null,
      teams: null,
      agents: null,
      selected: null,
      filterSelected: false
    };
  },

  enableButton: function() {
    this.setState({
      canSubmit: true
    });
  },

  disableButton: function() {
    this.setState({
      canSubmit: false
    })
  },

  updateValues: function(event) {
    this.setState({
      projectTitle: event.target.value
    })
  },

  clearFilter: function() {
    this.setState({
      agents: this.props.agentList,
      teams: this.props.teamList,
      departments: this.props.departmentList,
      filterValue: null,
      filterSelected: false
    });
  },

  quickFilter: function(event) {
    const value = $(event.target).val().toLowerCase();
    let agents = this.props.agentList;
    let teams = this.props.teamList;
    let departments = this.props.departmentList;

    if (value) {
      agents = agents.filter((agent) => {
        return agent.name.toLowerCase().indexOf(value) > -1;
      });
      teams = teams.filter((team) => {
        return team.name.toLowerCase().indexOf(value) > -1;
      });
      departments = departments.filter((department) => {
        return department.name.toLowerCase().indexOf(value) > -1;
      });
    }

    this.setState({
      agents: agents,
      teams: teams,
      departments: departments,
      filterValue: value
    });
  },

  filterSelected: function() {
    if (this.state.filterSelected === true) {
      this.clearFilter();

      return;
    }

    let agents = this.props.agentList;
    let teams = this.props.teamList;
    let departments = this.props.departmentList;

    agents = agents.filter((agent) => {
      return this.state.selected.agents.indexOf(agent.value) > -1;
    });
    teams = teams.filter((team) => {
      return this.state.selected.teams.indexOf(team.value) > -1;
    });
    departments = departments.filter((department) => {
      return this.state.selected.departments.indexOf(department.value) > -1;
    });

    this.setState({
      agents: agents,
      teams: teams,
      departments: departments,
      filterSelected: true
    });
  },

  assignSelf: function() {
    const userId = this.props.user.id;
    if (this.state.selected.agents.indexOf(userId) < 0) {
      let selected = this.state.selected;
      selected.agents.push(userId);

      this.setState({
        selected: selected
      });

      this.refs['agentSelect'].setValue(selected.agents);
    }
  },

  unassignAll: function() {
    this.setState({
      selected: {
        agents: [],
        teams: [],
        departments: []
      }
    });
    this.refs['agentSelect'].setValue([]);
    this.refs['teamSelect'].setValue([]);
    this.refs['departmentSelect'].setValue([]);
  },

  serverValidation: function(field) {
    if (this.props.createdProject.failedProject === null
      || typeof this.props.createdProject.failedProject.errors === 'undefined'
      || this.props.createdProject.failedProject.errors === null
      || typeof this.props.createdProject.failedProject.errors.fields[field] === 'undefined') {
      return '';
    }

    let errors = this.props.createdProject.failedProject.errors.fields[field].errors;

    return errors.map(function(error) {
      return <span className="form-error-description" key={error.code}>{error.message}</span>
    });
  },

  componentWillReceiveProps: function() {
    let state = {};

    if (this.state.agents === null && this.props.agentList.length > 0) {
      state.agents = this.props.agentList;
    }

    if (this.state.teams === null && this.props.teamList.length > 0) {
      state.teams = this.props.teamList;
    }

    if (this.state.departments === null && this.props.departmentList.length > 0) {
      state.departments = this.props.departmentList;
    }

    if (this.state.selected === null && Object.keys(this.props.projectData).length > 0) {
      state.selected = {
        departments: this.props.projectData.departments,
        teams: this.props.projectData.teams,
        agents: this.props.projectData.agents
      }
    }
    
    if (Object.keys(state).length > 0) {
      this.setState(state);    
    }
  },

  parseMembers: function(members) {
    let result = {
      department: [],
      team: [],
      person: []
    };

    members.forEach(function(object) {
      let linkType = null;
      switch(true) {
        case object.department !== null:
          linkType = 'department';
          break;
        case object.team !== null:
          linkType = 'team';
          break;
        default:
          linkType = 'person';
      }

      result[linkType].push(object[linkType].id);
    });

    return result;
  },

  updateAssignment: function(field, value) {
    let selected = this.state.selected;
    selected[field] = value;
    this.setState({
      selected: selected
    });
  },

  render: function() {
    const {agentList, teamList, departmentList} = this.props;

    const project = this.props.projectData ? this.props.projectData : {};
    const currentMembers = project.members && project.members.length > 0 ? this.parseMembers(project.members) : {};
    const positionY = (this.props.position.y - 20);
    const maxY = window.innerHeight - 400;
    let overshotY = false;

    let top = positionY + 'px';

    if (positionY > maxY) {
      overshotY = true;
      top = maxY + 'px';
    }

    return (<div style={{top: top}} className={overshotY ? "sidebar-hover hide-indicator" : "sidebar-hover"}>
        <div className="dpmw--popup-main">
          <div className="dpmw--popup-header">
            <i className="fa fa-tags"/> Project - {project.id ? 'Edit' : 'Create New'}
          </div>
          <Formsy.Form onValid={this.enableButton} onInvalid={this.disableButton} onSubmit={this.props.createProject}>
            <div className="dpw--popup-content">
              <div className="dpw--popup-content-line">
                <div className="dpmw--popup-content-full">
                  <FRC.Input name="projectId" type="hidden" value={project.id} />
                  <h2 className="dpw--popup-item-section-title">Title</h2>
                  <div className="dpw--popup-form-container">
                    <FRC.Input name="title" type="text" placeholder="Title" validations="minLength:1" validationErrors={{minLength: "The title field is required"}} value={project.title} />
                    {this.serverValidation('title')}
                  </div>
                </div>
              </div>
              <div className="dpw--popup-content-line">
                <div className="dpw--popup-content-left">
                  <div className="dpw-quick-filter">
                    <div className="dpw-quick-filter-container">
                      <div className="dpw-quick-filter-icon"><i className="fa fa-filter"></i></div>
                      <input type="text" placeholder="Quick Filter" value={this.state.filterValue} onChange={this.quickFilter} />
                      <span className="dpw-quick-filter-clear-link" onClick={this.clearFilter}><i className="fa fa-times-circle"></i></span>
                    </div>
                  </div>
                </div>

                <div className="dpmw--popup-content-right">
                  <div className="dpw-popup-content-item">
                    <div className="dpw-popup-content-item-show-only-selected">
                      <a href="#" className={this.state.filterSelected === true ? "checkbox-link checked" : "checkbox-link"} onClick={this.filterSelected}>
                        <span>Show only Selected</span>
                        <span className="dpw--checkbox-boxy"><i className="fa fa-check" /></span>
                       </a>
                    </div>
                  </div>
                  <div className="dpw-popup-content-item">
                    <div className="dpw-popup-content-item-unassign-all">
                      <a href="#" className="checkbox-link" onClick={this.unassignAll}>
                        <span>Unassign</span>
                        <span className="unassign-all-icon"><span /></span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div className="dpw--popup-content-line">
                <div className="dpmw--popup-content-of-three">
                  <h1 className="dpw--popup-item-collection-title">Agent <a href="#" onClick={this.assignSelf}>Assign to me</a></h1>
                  <div className="dpw--popup-item-collection">
                    {this.state.agents ? <FRC.CheckboxGroupDeskPRO
                      name="agents"
                      label="Agent"
                      options={this.state.agents}
                      value={this.state.selected && this.state.selected.agents ? this.state.selected.agents : []}
                      ref="agentSelect"
                      onChange={this.updateAssignment}
                      multiple
                      /> : ''}
                  </div>
                </div>

                <div className="dpmw--popup-content-of-three">
                  <h1 className="dpw--popup-item-collection-title">Team</h1>
                  <div className="dpw--popup-item-collection">
                    {this.state.teams ? <FRC.CheckboxGroupDeskPRO
                      name="teams"
                      label="Team"
                      options={this.state.teams}
                      value={this.state.selected && this.state.selected.teams ? this.state.selected.teams : []}
                      ref="teamSelect"
                      onChange={this.updateAssignment}
                      multiple
                      /> : ''}
                  </div>
                </div>

                <div className="dpmw--popup-content-of-three">
                  <h1 className="dpw--popup-item-collection-title">Department</h1>
                  <div className="dpw--popup-item-collection">
                    {this.state.departments ? <FRC.CheckboxGroupDeskPRO
                      name="departments"
                      label="Department"
                      options={this.state.departments}
                      value={this.state.selected && this.state.selected.departments ? this.state.selected.departments : []}
                      ref="departmentSelect"
                      onChange={this.updateAssignment}
                      multiple
                      /> : ''}
                  </div>
                </div>
              </div>
              <div className="dpw--popup-content-line">
                <div className="dpw--popup-content-left">
                  <button type="submit" value="Save" className="dpw--popup-button" disabled={!this.state.canSubmit}>Save</button>
                </div>
              </div>
            </div>
          </Formsy.Form>
        </div>
      </div>
    );
  }
});

module.exports = ProjectCreateHover;
