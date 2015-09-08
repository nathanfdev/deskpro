import React from "react";
import TaskFilterHover from "../Components/TaskFilterHover";
import TaskOrderHover from "../Components/TaskOrderHover";
import ComponentRootWrapper from "DeskPRO/Component/ComponentRootWrapper";

export default class TaskControls extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      showWindow: false,
      changeOrder: false,
    };
  }

  toggleWindow() {
    this.setState({
      showWindow: !this.state.showWindow
    });
  }

  toggleShowOrder() {
    this.setState({
      changeOrder: !this.state.changeOrder
    });
  }

  applyFilter(filter) {
    this.props.applyFilter(filter);
    this.closeWindow();
  }

  closeWindow() {
    this.setState({
      showWindow: false
    });
  }

  closeOrder() {
    this.setState({
      changeOrder: false
    });
  }

  render() {
    const taskView = this.props.windowProps.taskView;
    return <span className="ticket-controls-default">
      <a href="#" className="ticket-control-button" onClick={this.toggleShowOrder.bind(this)}>
        <span className="title">Order by:</span>
        <span className="focus">{ this.props.order.charAt(0).toUpperCase() + this.props.order.slice(1) }</span>
        <span className="down">{ this.props.direction.charAt(0).toUpperCase() + this.props.direction.slice(1) } <i className="fa fa-caret-down" /></span>
      </a>

      <ComponentRootWrapper open={this.state.changeOrder}>
        <TaskOrderHover
          position={this.state.position}
          applyOrder={this.props.setSortOrder.bind(this)}
          closeWindow={this.closeOrder.bind(this)}
          order={this.props.order}
          direction={this.props.direction}
          />
      </ComponentRootWrapper>

      <a href="#" className="ticket-control-button" onClick={this.toggleWindow.bind(this)}>
        <span className="title">Filter by:</span>
        <span className="focus">12</span>
        <span className="down">Completed <i className="fa fa-caret-down" /></span>
      </a>

      <ComponentRootWrapper open={this.state.showWindow}>
        <TaskFilterHover
          position={this.state.position}
          applyFilter={this.applyFilter.bind(this)}
          agents={this.props.agents}
          teams={this.props.teams}
          departments={this.props.departments}
          projects={this.props.projects}
          labels={this.props.labels}
          closeWindow={this.closeWindow.bind(this)}
          taskFilter={this.props.taskFilter}
          />
      </ComponentRootWrapper>

      <a href="#" className="ticket-control-button" onClick={this.props.toggleView.bind(this)}>
        <span className="title">View:</span>
        <span className="multi">
          { taskView.charAt(0).toUpperCase() + taskView.slice(1) }
          <span className="multi-down"><i className="fa fa-caret-down" /></span>
        </span>
      </a>
    </span>;
  }
}
