import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { listParamsNavSelector } from '../../../../../Selectors/list';
import { addTask } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Actions/listActions';
import Immutable from 'immutable';
import { SaveTaskButton } from './SaveTaskButton';
import {
  Card,
  CardReset,
  CardLine,
  CardLineLeft,
  CardLineRight
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import {
  TitleForm,
  DateDue,
  CardProjectContainer,
  Comments,
  AssignButton,
  AssigneeAvatar
} from '../../../TaskCard';

@connect()

export class TaskCardNew extends React.Component {

  static propTypes = {
    onClose:   PropTypes.func,
    dispatch:  PropTypes.func.isRequired,
    isChanged: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.model = {
      title:    null,
      due:      null,
      assignee: Immutable.fromJS({
        agents:      [],
        teams:       [],
        departments: []
      }),
      project: null
    };
  }

  onReset = () => {
    this.model = {
      title:    null,
      due:      null,
      assignee: Immutable.fromJS({
        agents:      [],
        teams:       [],
        departments: []
      }),
      project: null
    };
    if (this.props.isChanged) {
      this.props.isChanged(false);
    }
    this.forceUpdate();
  };

  onSetEditing = (isEditing) => {
    this.refs.reset.onSetEditing(isEditing);
  };

  onChange = (prop, value) => {
    this.model[prop] = value;
    if (value) {
      this.refs.reset.onChange();
    }
    if (this.props.isChanged) {
      this.props.isChanged(true);
    }
  };

  onAssign = (assignee) => new Promise(resolve => {
    this.model.assignee = assignee;
    this.refs.reset.onChange();
    resolve();
  });

  onSave = () => {
    if (!this.model.title) return;
    const { dispatch, onClose } = this.props;

    const submitData = {
      title:       this.model.title,
      task_type:   'task',
      visibility:  'public',
      urgency:     1,
      date_due:    this.model.due,
      project:     this.model.project,
      agents:      this.model.assignee.get('agents') || [],
      departments: this.model.assignee.get('departments') || [],
      teams:       this.model.assignee.get('teams') || []
    };

    this.setState({
      submit: true
    });

    dispatch(addTask(submitData));
    if (onClose) {
      onClose();
    }
  };

  render() {
    const { submit } = this.props;
    const { assignee } = this.model;

    return (
      <Card type="task">
        <SaveTaskButton onClick={this.onSave} submit={submit} />
        <CardReset ref="reset" onReset={this.onReset} />

        <div className="title-line">
          <TitleForm value={this.model.title} onChange={val => this.onChange('title', val)} />
          <div className="icon-block">
            <AssignButton ref="assignee" onSetEditing={this.onSetEditing} value={assignee} onChange={this.onAssign} />
          </div>
        </div>

        <div className="details-line">
          <div className="dpwd--card-line-item-container">
            <DateDue
              value={this.model.due}
              onChange={val => this.onChange('due', val)}
              onSetEditing={this.onSetEditing}
              openBySingleClick
            />
          </div>
          <div className="dpwd--card-line-item-container">
            <CardProjectContainer
              value={this.model.project}
              onChange={val => this.onChange('project', val)}
              onSetEditing={this.onSetEditing}
              openBySingleClick
            />
          </div>
        </div>
      </Card>
    );
  }
}
