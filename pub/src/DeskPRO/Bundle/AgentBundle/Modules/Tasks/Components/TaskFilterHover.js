import React from 'react';
import ReactDOM from 'react-dom';
import Formsy from 'formsy-react';
import FRC from '../../../../../Component/FormComponents/main.js';
import Picker from 'anytime';
import Moment from 'moment';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';

const TaskFilterHover = React.createClass({

  propTypes: {
    applyFilter: React.PropTypes.func,
    closeWindow: React.PropTypes.func,
    taskFilter: React.PropTypes.object,
    agents: React.PropTypes.object,
    departments: React.PropTypes.object,
    teams: React.PropTypes.object,
    labels: React.PropTypes.object,
    projects: React.PropTypes.object,
  },

  mixins: [
    require('react-onclickoutside')
  ],

  getInitialState: function() {
    return {
      filterDates: {}
    };
  },

  componentDidMount: function() {
    const dateFields = [
      'filter_created_after',
      'filter_created_before',
      'filter_due_after',
      'filter_due_before',
      'filter_done_after',
      'filter_done_before'
    ];

    const pickers = [];

    const pickerOptions = {
      format: 'HH:mm, MMMM D, YYYY'
    };

    dateFields.forEach((field) => {
      const options = pickerOptions;
      options.input = ReactDOM.findDOMNode(this.refs[field + '_value']);
      options.button = ReactDOM.findDOMNode(this.refs[field + '_button']);
      options.offset = 16;

      // Create the picker
      const picker = new Picker(options);
      picker.render();

      // Change the component state and submit the edit when the date is changed
      picker.on('change', (newDate) => {
        const dates = this.state.filterDates;
        dates[field.replace(/-/g, '_')] = newDate ? Moment(newDate) : null;

        this.setState({
          filterDates: dates
        });

        picker.updateInput();
      });

      pickers.push(picker);
    });
  },

  handleClickOutside: function() {
    this.props.closeWindow();
  },

  applyFilter: function(model) {
    const filtered = model;
    if (this.state.filterDates) {
      Object.keys(this.state.filterDates).forEach((key) => {
        if (key.substring(0, 7) === 'filter_' && this.state.filterDates[key] && Moment.isMoment(this.state.filterDates[key])) {
          filtered[key.substring(7)] = this.state.filterDates[key].format();
        }
      });
    }

    // Merge model and state
    this.props.applyFilter(filtered);
  },

  render: function() {
    const filter = this.props.taskFilter.taskFilter;
    const dates = this.state.filterDates;

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

    const departments = [];
    const teams = [];
    const agents = [];
    const projects = [];
    const labels = [];

    if (typeof this.props.departments !== 'undefined' && this.props.departments !== null) {
      this.props.departments.map((object) => {
        departments.push({value: object.get('id'), label: object.get('title')});
      });
    }

    if (typeof this.props.teams !== 'undefined' && this.props.teams !== null) {
      this.props.teams.map((object) => {
        teams.push({value: object.get('id'), label: object.get('name')});
      });
    }

    if (typeof this.props.agents !== 'undefined' && this.props.agents !== null) {
      this.props.agents.map((object) => {
        const label = (<span>
          <span style={{position: 'relative'}}><PersonAvatar person={object} size="16" /></span>
            {object.get('name')}
          </span>
        );
        agents.push({value: object.get('id'), label: label});
      });
    }

    if (typeof this.props.projects !== 'undefined' && this.props.projects !== null) {
      this.props.projects.map((object) => {
        projects.push({value: object.get('id'), label: object.get('title')});
      });
    }

    if (typeof this.props.labels !== 'undefined' && this.props.labels !== null) {
      this.props.labels.map((object) => {
        labels.push({value: object.get('label'), label: object.get('label')});
      });
    }

    return (<div style={{top: 94}} className="sidebar-hover hide-indicator">
        <div className="sidebar-hover-content">
          <div className="sidebar-hover-header">
            <i className="fa fa-tags"/> <span className="title">Filter</span>
          </div>
          <Formsy.Form onSubmit={this.applyFilter}>
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
              <h2>Created</h2>
              <div className="filter-date">
                <FRC.Input type="hidden" ref="filter_created_after_value" name="created_after" />
                After: <a href="#" ref="filter_created_after_button"><i className="fa fa-calendar-o" /> <span className="filter-created-after" ref="filter_created_after">{dates && dates.filter_created_after ? dates.filter_created_after.format('h:mma, MMMM D, YYYY') : 'N/A'}</span></a>
              </div>
              <div className="filter-date">
                <FRC.Input type="hidden" ref="filter_created_before_value" name="created_before" />
                Before: <a href="#" ref="filter_created_before_button"><i className="fa fa-calendar-o" /> <span className="filter-created-before" ref="filter_created_before">{dates && dates.filter_created_before ? dates.filter_created_before.format('h:mma, MMMM D, YYYY') : 'N/A'}</span></a>
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Due</h2>
              <div className="filter-date">
                <FRC.Input type="hidden" ref="filter_due_after_value" name="due_after" />
                After: <a href="#" ref="filter_due_after_button"><i className="fa fa-calendar-o" /> <span className="filter-due-after" ref="filter_due_after">{dates && dates.filter_due_after ? dates.filter_due_after.format('h:mma, MMMM D, YYYY') : 'N/A'}</span></a>
              </div>
              <div className="filter-date">
                <FRC.Input type="hidden" ref="filter_due_before_value" name="due_before" />
                Before: <a href="#" ref="filter_due_before_button"><i className="fa fa-calendar-o" /> <span className="filter-due-before" ref="filter_due_before">{dates && dates.filter_due_before ? dates.filter_due_before.format('h:mma, MMMM D, YYYY') : 'N/A'}</span></a>
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Completed</h2>
              <div className="filter-date">
                <FRC.Input type="hidden" ref="filter_done_after_value" name="done_after" />
                After: <a href="#" ref="filter_done_after_button"><i className="fa fa-calendar-o" /> <span className="filter-done-after" ref="filter_done_after">{dates && dates.filter_done_after ? dates.filter_done_after.format('h:mma, MMMM D, YYYY') : 'N/A'}</span></a>
              </div>
              <div className="filter-date">
                <FRC.Input type="hidden" ref="filter_done_before_value" name="done_before" />
                Before: <a href="#" ref="filter_done_before_button"><i className="fa fa-calendar-o" /> <span className="filter-done-before" ref="filter_done_before">{dates && dates.filter_done_before ? dates.filter_done_before.format('h:mma, MMMM D, YYYY') : 'N/A'}</span></a>
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
