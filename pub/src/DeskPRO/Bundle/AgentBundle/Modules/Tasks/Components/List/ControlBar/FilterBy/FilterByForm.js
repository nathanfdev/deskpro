import React, { PropTypes } from 'react';
import {
  BaseForm,
  Popup,
  Header,
  AgentsList,
  AgentTeamsList,
  DepartmentsList } from '../../../Form/index';

export class FilterByForm extends BaseForm {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    agents: PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired
  };

  onSubmit = event => {
    event.preventDefault();
  };

  render() {
    const { departments, agentTeams, agents } = this.props;

    return (
      <Popup indicator="none">
        <Header>
          Filter
        </Header>

        <div className="sidebar-hover-content">
          <form onSubmit={this.onSubmit}>
            <div className="sidebar-hover-content-box">
              <h2>Status</h2>
              <div className="sidebar-hover-checkbox-collection inline-radio">
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Assignment</h2>
              <div className="sidebar-hover-checkbox-collection">
                <DepartmentsList values={departments}
                                 selected={this.state.departments}
                                 onChange={this.onChangeDepartments} />
              </div>
              <div className="sidebar-hover-checkbox-collection">
                <AgentTeamsList values={agentTeams}
                                selected={this.state.agentTeams}
                                onChange={this.onChangeAgentTeams} />
              </div>
              <div className="sidebar-hover-checkbox-collection">
                <AgentsList values={agents}
                            selected={this.state.agents}
                            onChange={this.onChangeAgents} />
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Created</h2>
              <div className="filter-date">
                <span>After:</span>
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-created-after">N/A</span>
                </a>
              </div>
              <div className="filter-date">
                <span>Before:</span>
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-created-before">N/A</span>
                </a>
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Due</h2>
              <div className="filter-date">
                <span>After:</span>
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-due-after">N/A</span>
                </a>
              </div>
              <div className="filter-date">
                <span>Before:</span>
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-due-before">N/A</span>
                </a>
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Completed</h2>
              <div className="filter-date">
                <span>After:</span>
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-done-after">N/A</span>
                </a>
              </div>
              <div className="filter-date">
                <span>Before:</span>
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-done-before">N/A</span>
                </a>
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Projects</h2>
              <div className="sidebar-hover-checkbox-collection">

              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Labels</h2>
              <div className="sidebar-hover-checkbox-collection">

              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Attachments</h2>
              <div className="sidebar-hover-checkbox-collection inline-radio">

              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <span><button type="submit" value="Apply" className="button">Apply</button></span>
              <a href="#" className="button">Clear</a>
            </div>
          </form>
        </div>
      </Popup>
    );
  }
}
