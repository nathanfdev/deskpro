import React from 'react';
import TaskFilterHover from '../Components/TaskFilterHover';
import TaskOrderHover from '../Components/TaskOrderHover';
import ComponentRootWrapper from 'DeskPRO/Component/ComponentRootWrapper';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import TaskControlsViewSwitcher from '../Components/TaskControlsViewSwitcher';
import jQuery from 'jquery';
import { MassActionCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar';

export default class TaskControls extends React.Component {
  static propTypes = {
    actionable: React.PropTypes.array,
    agents: React.PropTypes.object,
    applyFilter: React.PropTypes.func,
    changeView: React.PropTypes.bool,
    departments: React.PropTypes.object,
    direction: React.PropTypes.string,
    labels: React.PropTypes.object,
    order: React.PropTypes.string,
    projects: React.PropTypes.object,
    setSortOrder: React.PropTypes.func,
    setView: React.PropTypes.func,
    taskFilter: React.PropTypes.object,
    teams: React.PropTypes.object,
    toggleAllMassActions: React.PropTypes.func,
    toggleView: React.PropTypes.func,
    view: React.PropTypes.string,
    windowProps: React.PropTypes.func
  }

  constructor(props) {
    super(props);

    this.state = {
      showWindow: false,
      changeOrder: false
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
    const taskView = this.props.windowProps.get('taskView');
    const viewSwitcherPosition = jQuery('.task-list-view-switcher');

    const {actionable, toggleAllMassActions, setView} = this.props;

    const count = actionable > 0 ? actionable.toString() : '';

    return (
      <ListFrameMenu ref="ticketControlBar">

        <MassActionCheckbox count={count} massAction={(actionable > 0)} onClick={toggleAllMassActions.bind(this)}/>

        <li>
          <a href="#" ref="orderButton" className="dpwd-navigation-dropdown-top-row-button"
             onClick={this.toggleShowOrder.bind(this)}>
            <span className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">Order by:</span>
            <span className="dpwd-navigation-dropdown-top-row-button-text">&nbsp;
              <span className="focus">{ this.props.order.charAt(0).toUpperCase() + this.props.order.slice(1) }</span>
              <span className="down"> ({ this.props.direction.charAt(0).toUpperCase() + this.props.direction.slice(1) }) <i
                className="fa fa-caret-down"/></span>
            </span>
          </a>

          <Positioned isOpen={this.state.changeOrder}
                      positionAt="left bottom"
                      positionTarget={this.refs.orderButton}>
            <TaskOrderHover
              position={this.state.position}
              applyOrder={this.props.setSortOrder.bind(this)}
              closeWindow={this.closeOrder.bind(this)}
              order={this.props.order}
              direction={this.props.direction}
              />
          </Positioned>
        </li>
        <li>
          <hr/>
        </li>
        <li>
          <a href="#" className="dpwd-navigation-dropdown-top-row-button" onClick={this.toggleWindow.bind(this)}>
            <span className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">Filter by:</span>
            <span className="dpwd-navigation-dropdown-top-row-button-text">&nbsp;
              <span className="focus">12</span>
              <span className="down"> Completed <i className="fa fa-caret-down"/></span>
            </span>
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
        </li>
        <li>
          <hr/>
        </li>
        <li>
          <a href="#" className="dpwd-navigation-dropdown-top-row-button" onClick={this.props.toggleView.bind(this)}>
            <span className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">View:</span>
            <span className="dpwd-navigation-dropdown-top-row-button-text">&nbsp;
              <span className="multi task-list-view-switcher">
                { taskView.charAt(0).toUpperCase() + taskView.slice(1) }
                <span className="multi-down"> <i className="fa fa-caret-down"/></span>
              </span>
            </span>
          </a>

          <Positioned isOpen={this.props.changeView}
                      positionAt="left bottom"
                      positionTarget={viewSwitcherPosition}>
            <TaskControlsViewSwitcher
              setView={setView.bind(this)}
              view={this.props.view}
              />
          </Positioned>
        </li>
      </ListFrameMenu>
    );
  }
}
