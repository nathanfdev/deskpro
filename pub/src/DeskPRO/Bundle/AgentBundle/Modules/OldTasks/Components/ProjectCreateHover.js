import React from 'react';
import Formsy from 'formsy-react';
import FRC from '../../../../../Component/FormComponents/main.js';
import $ from 'jquery';
import Immutable from 'immutable';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

const ProjectCreateHover = React.createClass({

  propTypes: {
    agentList: React.PropTypes.array,
    closeWindow: React.PropTypes.func,
    createdProject: React.PropTypes.object,
    createProject: React.PropTypes.func,
    departmentList: React.PropTypes.array,
    position: React.PropTypes.object,
    projectData: React.PropTypes.object,
    teamList: React.PropTypes.array,
    user: React.PropTypes.object
  },

  mixins: [
    require('react-onclickoutside')
  ],

  getInitialState: function() {
    return {
      canSubmit: false,
      projectTitle: null,
      departments: null,
      teams: null,
      agents: null,
      selected: null,
      filterSelected: false,
      filterValue: null
    };
  },

  componentWillReceiveProps: function() {
    if (this.state.projectTitle === null || (this.props.projectData.get && this.state.projectTitle !== this.props.projectData.get('title'))) {
      this.resetSelected();
    }
  },

  resetSelected: function() {
    this.setState({
      selected: {
        departments: this.props.projectData.get('departments') ? this.props.projectData.get('departments').toArray() : [],
        teams: this.props.projectData.get('teams') ? this.props.projectData.get('teams').toArray() : [],
        agents: this.props.projectData.get('agents') ? this.props.projectData.get('agents').toArray() : []
      },
      projectTitle: this.props.projectData.get('title')
    });
  },

  handleClickOutside: function() {
    this.props.closeWindow();
  },

  enableButton: function() {
    this.setState({
      canSubmit: true
    });
  },

  disableButton: function() {
    this.setState({
      canSubmit: false
    });
  },

  updateValues: function(event) {
    this.setState({
      projectTitle: event.target.value
    });
  },

  clearFilter: function() {
    this.setState({
      filterValue: null,
      filterSelected: false
    });
  },

  quickFilter: function(event) {
    const value = $(event.target).val().toLowerCase();
    this.setState({
      filterValue: value
    });
  },

  assignSelf: function() {
    const userId = this.props.user.get('id');

    if (this.props.agentList.indexOf(userId) < 0) {
      const selected = this.state.selected;
      selected.agents.push(userId);

      this.setState({
        selected: selected
      });

      this.refs.agentSelect.setValue(selected.agents);
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
    this.refs.agentSelect.setValue([]);
    this.refs.teamSelect.setValue([]);
    this.refs.departmentSelect.setValue([]);
  },

  serverValidation: function(field) {
    if (this.props.createdProject && typeof this.props.createdProject.has === 'function') {
      if (!this.props.createdProject.has('failedProject')
        || this.props.createdProject.get('failedProject', null) === null
        || typeof this.props.createdProject.get('failedProject').errors === 'undefined'
        || this.props.createdProject.get('failedProject').errors === null
        || typeof this.props.createdProject.get('failedProject').errors.fields[field] === 'undefined') {
        return '';
      }

      const errors = this.props.createdProject.get('failedProject').errors.fields[field].errors;

      return errors.map(function(error) {
        return (<span className="form-error-description" key={error.code}>{error.message}</span>);
      });
    }

    return '';
  },

  toggleFilterSelected: function() {
    this.setState({
      filterSelected: !this.state.filterSelected
    });
  },

  filterAssignees: function(items, type, filter = null, onlySelected = false) {
    let filteredItems = items;

    if (filter && filter.length > 0) {
      filteredItems = items.filter((item) => {
        return item.name.toLowerCase().indexOf(filter.toLowerCase()) > -1;
      });
    }

    if (onlySelected) {
      filteredItems = items.filter((item) => {
        return this.state.selected[type].indexOf(item.value) > -1;
      });
    }

    return filteredItems;
  },

  parseMembers: function(members) {
    const result = {
      department: [],
      team: [],
      person: []
    };

    members.forEach(function(object) {
      let linkType = null;
      switch (true) {
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
    const selected = this.state.selected;
    selected[field] = value;
    this.setState({
      selected: selected
    });
  },

  render: function() {
    const project = this.props.projectData && typeof this.props.projectData.has === 'function' ? this.props.projectData : Immutable.Map();
    const positionY = (this.props.position.y - 20);
    const maxY = window.innerHeight - 400;
    let overshotY = false;

    let top = positionY + 'px';

    if (positionY > maxY) {
      overshotY = true;
      top = maxY + 'px';
    }

    return (<div style={{top: top}} className={overshotY ? 'sidebar-hover hide-indicator' : 'sidebar-hover'}>
        <div className="dpw--popup-main">
          <div className="dpw--popup-header">
            <i className="fa fa-tags"/> Project - {project.has('id') ? 'Edit' : 'Create New'}
          </div>
          <Formsy.Form onValid={this.enableButton} onInvalid={this.disableButton} onSubmit={this.props.createProject}>
            <div className="dpw--popup-content">
              <div className="dpw--popup-content-line">
                <div className="dpw--popup-content-full">
                  <FRC.Input name="projectId" type="hidden" value={project.get('id', false)} />
                  <h2 className="dpw--popup-item-section-title">Title</h2>
                  <div className="dpw--popup-form-container">
                    <FRC.Input name="title" type="text" placeholder="Title" validations="minLength:1" validationErrors={{minLength: 'The title field is required'}} value={project.get('title')} />
                    {this.serverValidation('title')}
                  </div>
                </div>
              </div>
              <div className="dpw--popup-content-line">
                <div className="dpw--popup-content-left">
                  <div className="dpw-quick-filter">
                    <div className="dpw-quick-filter-container">
                      <div className="dpw-quick-filter-icon"><i className="fa fa-filter" /></div>
                      <input type="text" placeholder="Quick Filter" value={this.state.filterValue} onChange={this.quickFilter} />
                      <span className="dpw-quick-filter-clear-link" onClick={this.clearFilter}><i className="fa fa-times-circle"></i></span>
                    </div>
                  </div>
                </div>

                <div className="dpw--popup-content-right">
                  <div className="dpw-popup-content-item">
                    <div className="dpw-popup-content-item-show-only-selected">
                      <a href="#" className={this.state.filterSelected === true ? 'checkbox-link checked' : 'checkbox-link'} onClick={this.toggleFilterSelected}>
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
                <div className="dpw--popup-content-of-three">
                  <h1 className="dpw--popup-item-collection-title">Agent <a href="#" onClick={this.assignSelf}>Assign to me</a></h1>
                  <div className="dpw--popup-item-collection">
                    <div className="dpw--assignment-scrollable-container">
                      <Scrollable vertical>
                        {this.props.agentList ? <FRC.CheckboxGroupDeskPRO
                          name="agents"
                          label="Agent"
                          options={this.filterAssignees(this.props.agentList, 'agents', this.state.filterValue, this.state.filterSelected)}
                          value={this.state.selected && this.state.selected.agents ? this.state.selected.agents : []}
                          ref="agentSelect"
                          onChange={this.updateAssignment}
                          multiple
                          /> : ''}
                      </Scrollable>
                    </div>
                  </div>
                </div>

                <div className="dpw--popup-content-of-three">
                  <h1 className="dpw--popup-item-collection-title">Team</h1>
                  <div className="dpw--popup-item-collection">
                    <div className="dpw--assignment-scrollable-container">
                      <Scrollable vertical>
                        {this.props.teamList ? <FRC.CheckboxGroupDeskPRO
                          name="teams"
                          label="Team"
                          options={this.filterAssignees(this.props.teamList, 'teams', this.state.filterValue, this.state.filterSelected)}
                          value={this.state.selected && this.state.selected.teams ? this.state.selected.teams : []}
                          ref="teamSelect"
                          onChange={this.updateAssignment}
                          multiple
                          /> : ''}
                      </Scrollable>
                    </div>
                  </div>
                </div>

                <div className="dpw--popup-content-of-three">
                  <h1 className="dpw--popup-item-collection-title">Department</h1>
                  <div className="dpw--popup-item-collection">
                    <div className="dpw--assignment-scrollable-container">
                      <Scrollable vertical>
                        {this.props.departmentList ? <FRC.CheckboxGroupDeskPRO
                          name="departments"
                          label="Department"
                          options={this.filterAssignees(this.props.departmentList, 'departments', this.state.filterValue, this.state.filterSelected)}
                          value={this.state.selected && this.state.selected.departments ? this.state.selected.departments : []}
                          ref="departmentSelect"
                          onChange={this.updateAssignment}
                          multiple
                          /> : ''}
                      </Scrollable>
                    </div>
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
