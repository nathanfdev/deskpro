import React from 'react';
import jQuery from 'jquery';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { AgentTeamAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/AgentTeamAvatar';
import { DepartmentAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/DepartmentAvatar';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

const AssignHover = React.createClass({

  propTypes: {
    agents: React.PropTypes.object,
    assignTask: React.PropTypes.func,
    closeWindow: React.PropTypes.func,
    departments: React.PropTypes.object,
    taskData: React.PropTypes.object,
    position: React.PropTypes.object,
    teams: React.PropTypes.object
  },

  mixins: [
    require('react-onclickoutside')
  ],

  getInitialState: function() {
    return {
      agents: this.props.agents,
      teams: this.props.teams,
      departments: this.props.departments,
      filterValue: null
    };
  },

  componentWillReceiveProps: function(newProps) {
    this.setState({
      agents: newProps.agents,
      teams: newProps.teams,
      departments: newProps.departments,
    });
  },

  handleClickOutside: function() {
    this.props.closeWindow();
  },

  clearFilter: function() {
    this.setState({
      agents: this.props.agents,
      teams: this.props.teams,
      departments: this.props.departments,
      filterValue: null
    });
  },

  handleAssignment: function(data) {
    if (this.props.assignTask) {
      this.props.assignTask(data);
    }
  },

  quickFilter: function(event) {
    const value = jQuery(event.target).val().toLowerCase();
    let agents = this.props.agents;
    let teams = this.props.teams;
    let departments = this.props.departments;

    if (value) {
      agents = agents.filter((agent) => {
        return agent.get('name').toLowerCase().indexOf(value) > -1;
      });
      teams = teams.filter((team) => {
        return team.get('name').toLowerCase().indexOf(value) > -1;
      });
      departments = departments.filter((department) => {
        return department.get('title').toLowerCase().indexOf(value) > -1;
      });
    }

    this.setState({
      agents: agents,
      teams: teams,
      departments: departments,
      filterValue: value
    });
  },

  render: function() {
    const taskId = this.props.taskData && this.props.taskData.get ? this.props.taskData.get('id') : null;

    return (<div style={this.props.position ? {top: this.props.position.y + 10, left: this.props.position.x - 300} : {}} className="sidebar-hover assign-hover">
        <div className="dpmw--popup-main">
          <div className="dpmw--popup-header">
            <i className="fa fa-tags"/> Assign to Task
          </div>
          <div className="dpw--popup-content">

            <div className="dpw--popup-content-line">
              <div className="dpw--popup-content-left">
                <div className="dpw-quick-filter">
                  <div className="dpw-quick-filter-container">
                    <div className="dpw-quick-filter-icon"><i className="fa fa-filter" /></div>
                    <input type="text" placeholder="Quick Filter" value={this.state.filterValue} onChange={this.quickFilter} />
                    <span className="dpw-quick-filter-clear-link" onClick={this.clearFilter}><i className="fa fa-times-circle" /></span>
                  </div>
                </div>
              </div>

              <div className="dpmw--popup-content-right">
                <div className="dpw-popup-content-item">
                  <div className="dpw-popup-content-item-unassign-all">
                    <a href="#" className="checkbox-link" onClick={this.handleAssignment.bind(this, {
                      taskId: taskId,
                      value: 'unassigned'
                    })}>
                      <span>Unassign</span>
                      <span className="unassign-all-icon"><span /></span>
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <div className="dpw--popup-content-line">
              <div className="dpmw--popup-content-of-three">
                <h1 className="dpw--popup-item-collection-title">Agent <a href="#" onClick={this.handleAssignment.bind(this, {
                  id: taskId,
                  value: 'agents-me'
                })}>Assign to me</a></h1>
                <div className="dpw--popup-item-collection">
                  <div className="dpw--assignment-scrollable-container">
                    <Scrollable vertical>
                      <ul>
                        {this.state.agents ? this.state.agents.map((agent) => {
                          const lineClass = this.props.taskData.has && this.props.taskData.has('agents') && this.props.taskData.get('agents').first() === agent.get('id') ? 'dpw--popup-item-person selected' : 'dpw--popup-item-person';
                          return (<li key={agent.get('id')}>
                                    <div className={lineClass} onClick={this.handleAssignment.bind(this, {
                                      taskId: taskId,
                                      value: 'agents-' + agent.get('id')
                                    })}>
                                      <span style={{position: 'relative'}}><PersonAvatar person={agent} size="16" /></span> <span className="dpw-popup-item-collection-name">{agent.get('name')}</span>
                                    </div>
                                  </li>);
                        }) : ''}
                      </ul>
                    </Scrollable>
                  </div>
                </div>
              </div>

              <div className="dpmw--popup-content-of-three">
                <h1 className="dpw--popup-item-collection-title">Team</h1>
                <div className="dpw--popup-item-collection">
                  <div className="dpw--assignment-scrollable-container">
                    <Scrollable vertical>
                      <ul>
                        {this.state.teams ? this.state.teams.map((team) => {
                          const lineClass = this.props.taskData.has && this.props.taskData.has('teams') && this.props.taskData.get('teams').first() === team.get('id') ? 'dpw--popup-item-person selected' : 'dpw--popup-item-person';
                          return (<li key={team.get('id')}>
                                    <div className={lineClass} onClick={this.handleAssignment.bind(this, {
                                      taskId: taskId,
                                      value: 'teams-' + team.get('id')
                                    })}>
                                      <span style={{position: 'relative'}}><AgentTeamAvatar agentTeam={team} size="16" /></span> <span className="dpw-popup-item-collection-name">{team.get('name')}</span>
                                    </div>
                                  </li>);
                        }) : ''}
                      </ul>
                    </Scrollable>
                  </div>
                </div>
              </div>

              <div className="dpmw--popup-content-of-three">
                <h1 className="dpw--popup-item-collection-title">Department</h1>
                <div className="dpw--popup-item-collection">
                  <div className="dpw--assignment-scrollable-container">
                    <Scrollable vertical>
                      <ul>
                        {this.state.departments ? this.state.departments.map((department) => {
                          const lineClass = this.props.taskData.has && this.props.taskData.has('departments') && this.props.taskData.get('departments').first() === department.get('id') ? 'dpw--popup-item-person selected' : 'dpw--popup-item-person';
                          return (<li key={department.get('id')}>
                                    <div className={lineClass} onClick={this.handleAssignment.bind(this, {
                                      id: taskId,
                                      value: 'departments-' + department.get('id')
                                    })}>
                                      <span style={{position: 'relative'}}><DepartmentAvatar department={department} size="16" /></span> <span className="dpw-popup-item-collection-name">{department.get('title')}</span>
                                    </div>
                                  </li>);
                        }) : '' }
                      </ul>
                    </Scrollable>
                  </div>
                </div>
              </div>
            </div>
            <div className="dpw--popup-content-line" />
          </div>
        </div>
      </div>
    );
  }
});

module.exports = AssignHover;
