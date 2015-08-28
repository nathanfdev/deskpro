import React from "react";
import TaskFilterHover from "../Components/TaskFilterHover";
import ComponentRootWrapper from "DeskPRO/Component/ComponentRootWrapper";

export default class TaskControls extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      showWindow: false
    };
  }

  toggleWindow() {
    this.setState({
      showWindow: !this.state.showWindow
    });
  }

  applyFilter() {
    console.log('Filter applied');
  }

  openWindow() {
    this.setState({
      showWindow: true
    });
  }

  closeWindow() {
    this.setState({
      showWindow: false
    });
  }

  render() {
    return <span className="ticket-controls-default">
      <a href="#" className="ticket-control-button">
        <span className="title">Order by:</span>
        <span className="focus">Date</span>
        <span className="down">Asc <i className="fa fa-caret-down" /></span>
      </a>

      <a href="#" className="ticket-control-button" onClick={this.toggleWindow.bind(this)}>
        <span className="title">Filter by:</span>
        <span className="focus">12</span>
        <span className="down">Completed <i className="fa fa-caret-down" /></span>
      </a>

      <ComponentRootWrapper open={this.state.showWindow}>
        <TaskFilterHover
          position={this.state.position}
          applyFilter={this.applyFilter.bind(this)}
          //agentList={agentList}
          //teamList={teamList}
          //departmentList={departmentList}
          closeWindow={this.closeWindow.bind(this)}
          />
      </ComponentRootWrapper>

      <a href="#" className="ticket-control-button" onClick={this.props.toggleView.bind(this)}>
        <span className="title">View:</span>
        <span className="multi">
          List
          <span className="multi-down"><i className="fa fa-caret-down" /></span>
        </span>
      </a>
    </span>;
  }
}