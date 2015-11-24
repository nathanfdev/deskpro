import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { listParamsNavSelector } from '../../../Selectors/list';
import { addTask } from '../../../Actions/listActions';
import moment from 'moment';
import Immutable from 'immutable';

@connect(state => ({
  currentNav: listParamsNavSelector(state)
}))
export class TaskCardNewContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    updateData: PropTypes.object,
    currentNav: PropTypes.object,
    onClose: PropTypes.func.isRequired
  };

  onSaveTask = title => {
    const { dispatch, onClose } = this.props;
    const submitData = {
      title: title,
      task_type: 'task',
      visibility: 'public',
      urgency: 1,
      date_due: this.getDateDue(),
      project: this.getProject()
    };

    const agent = this.getAgent();
    if (agent) {
      submitData.agents = [agent];
    }

    const team = this.getTeam();
    if (team) {
      submitData.teams = [team];
    }

    const department = this.getDepartment();
    if (department) {
      submitData.departments = [department];
    }

    dispatch(addTask(submitData));
    onClose();
  };

  getDateDue() {
    const { updateData = {} } = this.props;
    return updateData.date_due ? updateData.date_due : moment().endOf('day').format();
  }

  getProject() {
    const { updateData = {}, currentNav } = this.props;
    const projects = currentNav.get('project');

    return projects && projects.size ? projects.first() : updateData.project;
  }

  getAgent() {
    const { updateData = {}, currentNav } = this.props;
    const agents = currentNav.get('assigned_agent');

    if (agents && agents.size) {
      return agents.first();
    }

    return updateData.agents && updateData.agents.length ? updateData.agents[0] : null;
  }

  getTeam() {
    const { updateData = {} } = this.props;
    return updateData.teams && updateData.teams.length ? updateData.teams[0] : null;
  }

  getDepartment() {
    const { updateData = {} } = this.props;
    return updateData.departments && updateData.departments.length ? updateData.departments[0] : null;
  }

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;

    const agent = this.getAgent();
    const team = this.getTeam();
    const department = this.getDepartment();

    return React.cloneElement(children, {
      ...childProps,
      ...props,

      dateDue: this.getDateDue(),
      project: this.getProject(),
      assignee: Immutable.fromJS({
        agents: agent ? [agent] : [],
        teams: team ? [team] : [],
        departments: department ? [department] : []
      }),

      onChangeTitle: this.onChangeTitle,
      onSaveTask: this.onSaveTask
    });
  }
}
