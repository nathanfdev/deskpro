import React from "react";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";
import Picker from "anytime";
import Moment from "moment";
import $ from 'jquery';

import * as TaskActions from "../Actions/TaskListActions";

const AssignHover = React.createClass({

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

  handleClickOutside: function(evt) {
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

  quickFilter: function(event) {
    const value = $(event.target).val().toLowerCase();
    let agents = this.props.agents;
    let teams = this.props.teams;
    let departments = this.props.departments;

    if (value) {
      agents = agents.filter((agent) => {
        return agent.name.toLowerCase().indexOf(value) > -1;
      });
      teams = teams.filter((team) => {
        return team.name.toLowerCase().indexOf(value) > -1;
      });
      departments = departments.filter((department) => {
        return department.title.toLowerCase().indexOf(value) > -1;
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
    console.log(this.props.taskData);
    return (<div style={{top: this.props.position.y + 10, left: this.props.position.x - 300}} className="sidebar-hover assign-hover">
        <div className="dpmw--popup-main">
          <div className="dpmw--popup-header">
            <i className="fa fa-tags"/> Assign to Task
          </div>
          <div className="dpw--popup-content">

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
                  <div className="dpw-popup-content-item-unassign-all">
                    <a href="#" className="checkbox-link" onClick={this.props.assignTask.bind(this, {
                        id: this.props.taskData.id,
                        value: 'unassigned'
                      })}>
                      <span>Unassign</span>
                      <span className="unassign-all-icon"><span></span></span>
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <div className="dpw--popup-content-line">
              <div className="dpmw--popup-content-of-three">
                <h1 className="dpw--popup-item-collection-title">Agent <a href="#" onClick={this.props.assignTask.bind(this, {
                    id: this.props.taskData.id,
                    value: 'agents-me'
                  })}>Assign to me</a></h1>
                <div className="dpw--popup-item-collection">
                  <ul>
                    {this.state.agents ? this.state.agents.map((agent) => {
                      let avatarImage = agent.picture_blob ? {backgroundImage: 'url(' + agent.picture_blob.download_url + ')'} : {};
                      let lineClass = this.props.taskData.agents && this.props.taskData.agents[0] === agent.id ? "dpw--popup-item-person selected" : "dpw--popup-item-person";
                      return <li key={agent.id}>
                        <div className={lineClass} onClick={this.props.assignTask.bind(this, {
                            id: this.props.taskData.id,
                            value: 'agents-' + agent.id
                          })}>
                            <span className="dpw--avatar-face" style={avatarImage}/> <span className="dpw-popup-item-collection-name">{agent.name}</span>
                        </div>
                      </li>
                    }) : ''}
                  </ul>
                </div>
              </div>

              <div className="dpmw--popup-content-of-three">
                <h1 className="dpw--popup-item-collection-title">Team</h1>
                <div className="dpw--popup-item-collection">
                  <ul>
                    {this.state.teams ? this.state.teams.map((team) => {
                      let avatarImage = team.picture_blob ? {backgroundImage: 'url(' + team.picture_blob.download_url + ')'} : {};
                      let lineClass = this.props.taskData.teams && this.props.taskData.teams[0] === team.id ? "dpw--popup-item-person selected" : "dpw--popup-item-person";
                      return <li key={team.id}>
                        <div className={lineClass} onClick={this.props.assignTask.bind(this, {
                            id: this.props.taskData.id,
                            value: 'teams-' + team.id
                          })}>
                          <span className="dpw--avatar-face" style={avatarImage}/> <span className="dpw-popup-item-collection-name">{team.name}</span>
                        </div>
                      </li>
                    }) : ''}
                  </ul>
                </div>
              </div>

              <div className="dpmw--popup-content-of-three">
                <h1 className="dpw--popup-item-collection-title">Department</h1>
                <div className="dpw--popup-item-collection">
                  <ul>
                    {this.state.departments ? this.state.departments.map((department) => {
                      let avatarImage = department.picture_blob ? {backgroundImage: 'url(' + department.picture_blob.download_url + ')'} : {};
                      let lineClass = this.props.taskData.departments && this.props.taskData.departments[0] === department.id ? "dpw--popup-item-person selected" : "dpw--popup-item-person";
                      return <li key={department.id}>
                        <div className={lineClass} onClick={this.props.assignTask.bind(this, {
                            id: this.props.taskData.id,
                            value: 'departments-' + department.id
                          })}>
                          <span className="dpw--avatar-face" style={avatarImage}/> <span className="dpw-popup-item-collection-name">{department.title}</span>
                        </div>
                      </li>
                    }) : '' }
                  </ul>
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
