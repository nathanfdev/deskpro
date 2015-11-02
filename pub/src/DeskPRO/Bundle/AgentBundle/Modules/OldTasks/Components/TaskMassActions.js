import React from 'react';

import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import ProjectDialog from '../Components/MassActions/ProjectDialog';
import AssignHover from '../Components/AssignHover';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';

export default class TaskMassActions extends React.Component {
  static propTypes = {
    hideMassActionControls: React.PropTypes.func,
    projects: React.PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      activeElement: false,
      massActionable: {},
      openExtras: false,
      openProjectDialog: false
    };
  }

  setMassActionProject(projectId) {
    const massActionable = this.state.massActionable;
    massActionable.project = projectId;
    this.setState({
      massActionable: massActionable,
      openExtras: false,
      openProjectDialog: false
    });
  }

  setActiveMenu(menu) {
    this.setState({
      activeElement: menu
    });
  }

  setClass(standard, active) {
    const className = standard;

    if (active) {
      return className + ' active';
    }

    return className;
  }

  toggleProjectDialog() {
    let active = 'project';

    if (this.state.openProjectDialog) {
      active = false;
    }

    this.setState({
      openAssignDialog: false,
      openProjectDialog: !this.state.openProjectDialog,
      openExtras: false,
      activeElement: active
    });
  }

  toggleAssignDialog() {
    let active = 'assign';

    if (this.state.openAssignDialog) {
      active = false;
    }

    this.setState({
      openAssignDialog: !this.state.openAssignDialog,
      openProjectDialog: false,
      openExtras: false,
      activeElement: active
    });
  }

  toggleExtrasMenu() {
    let active = 'extras';

    if (this.state.openExtras) {
      active = false;
    }

    this.setState({
      openAssignDialog: false,
      openProjectDialog: false,
      openExtras: !this.state.openExtras,
      activeElement: active
    });
  }

  render() {
    return (
      <span className="ticket-controls-bulk-editing">
        <a href="#">
          <span>Complete</span>
        </a>
        <a href="#" onClick={this.toggleProjectDialog.bind(this)} ref="massProject" className={this.setClass('', (this.state.activeElement === 'project'))}>
          <span>Project</span>
          <hr />
          <i className="fa fa-caret-down"/>
        </a>
        <a href="#" onClick={this.toggleAssignDialog.bind(this)} ref="massAssign" className={this.setClass('', (this.state.activeElement === 'assign'))}>
          <span>Assign</span>
          <hr />
          <i className="fa fa-caret-down"/>
        </a>
        <a href="#">
          <span>Due</span>
          <hr />
          <i className="fa fa-caret-down"/>
        </a>
        <a href="#" onClick={this.toggleExtrasMenu.bind(this)} ref="massExtras" className={this.setClass('', (this.state.activeElement === 'extras'))}>
          <span><i className="fa fa-asterisk" /></span>
          <hr />
          <i className="fa fa-caret-down"/>
        </a>

        <hr />

        <a href="#" className="active">
          <span>GO</span>
        </a>

        <a href="#" className="cancel" onClick={this.props.hideMassActionControls.bind(this)}>
          <span>Cancel</span>
        </a>

        <Positioned isOpen={this.state.openProjectDialog}
                    positionTarget={this.refs.massProject}
                    positionAt="left bottom">
          <ProjectDialog projects={this.props.projects}
                    massActionable={this.state.massActionable}
                    setMassActionProject={this.setMassActionProject.bind(this)} />
        </Positioned>

        <Positioned isOpen={this.state.openAssignDialog}
                    positionTarget={this.refs.massAssign}
                    positionAt="left bottom">
          <AssignHover />
        </Positioned>

        <Positioned isOpen={this.state.openExtras}
                    positionTarget={this.refs.massExtras}
                    positionAt="left bottom">
          <Menu isOpen={this.state.openExtras}>
            <Item icon="eye" label="Visibility"/>
            <Item icon="tag" label="Label"/>
            <Item icon="times" itemType="danger" label="Delete"/>
          </Menu>
        </Positioned>
      </span>
    );
  }
}
