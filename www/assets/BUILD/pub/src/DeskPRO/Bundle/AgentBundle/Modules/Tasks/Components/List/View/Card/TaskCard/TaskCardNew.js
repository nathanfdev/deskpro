import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
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
  AssignButton
} from '../../../TaskCard/index';

@connect()
export class TaskCardNew extends React.Component {

  static propTypes = {
    onClose:  PropTypes.func,
    dispatch: PropTypes.func.isRequired,
    submit:   PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      changed: false
    };

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

    this.setState({
      changed: false
    });
  };

  onChange = (prop, value) => {
    this.model[prop] = value;
    if (value) {
      this.setState({
        changed: true
      });
    }
  };

  onAssign = assignee => new Promise(resolve => {
    this.model.assignee = assignee;
    this.setState({
      changed: true
    });
    resolve();
  });

  onSave = () => {
    if (!this.model.title) {
      return;
    }

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
    onClose();
  };

  render() {
    const { submit } = this.props;
    const { title, due, project, assignee } = this.model;

    return (
      <Card type="task">
        <SaveTaskButton onClick={this.onSave} submit={submit} />
        <CardReset ref="reset" isChanged={this.state.changed} onReset={this.onReset} />
        <CardLine>
          <CardLineLeft>
            <div className="dpwd--card-title">
              <TitleForm value={title} onSubmit={value => this.onChange('title', value)} />
            </div>
          </CardLineLeft>
          <CardLineRight>
            <AssignButton ref="assignee" value={assignee} onChange={this.onAssign} />
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <DateDue
              value={due}
              onChange={value => this.onChange('due', value)}
              openBySingleClick
              />
            <CardProjectContainer
              value={project}
              onChange={value => this.onChange('project', value)}
              openBySingleClick
              />
          </CardLineLeft>
        </CardLine>
      </Card>
    );
  }
}
