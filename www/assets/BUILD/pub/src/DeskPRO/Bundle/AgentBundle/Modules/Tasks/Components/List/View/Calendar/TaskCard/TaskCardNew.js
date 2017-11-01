import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { listParamsNavSelector } from '../../../../../Selectors/list';
import { addTask } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Actions/listActions';
import Immutable from 'immutable';
import {
  Card,
  CardCheckbox,
  CardLine,
  CardLineLeft,
  CardLineRight
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import {
  TitleForm,
  DateDue,
  LinkedItemContainer,
  CardProjectContainer,
  AssignButton
} from '../../../TaskCard';
import { SaveTaskButton } from './SaveTaskButton';

@connect()

export class TaskCardNew extends React.Component {

  static propTypes = {
    onClose:      PropTypes.func,
    dispatch:     PropTypes.func.isRequired,
    isChanged:    PropTypes.func,
    task:         PropTypes.object.isRequired,
    onSetEditing: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      submit: false
    };
    this.state = {
      title:    null,
      due:      props.task.get('date_due'),
      assignee: Immutable.fromJS({
        agents:      [],
        teams:       [],
        departments: []
      }),
      links: Immutable.fromJS({
        linked_tickets:  [],
        linked_chats:    [],
        linked_articles: []
      }),
      project: null
    };
    this.defaultDate = this.state.due;
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      title:    null,
      due:      nextProps.task.get('date_due'),
      assignee: Immutable.fromJS({
        agents:      [],
        teams:       [],
        departments: []
      }),
      links: Immutable.fromJS({
        linked_tickets:  [],
        linked_chats:    [],
        linked_articles: []
      }),
      project: null
    });
    this.defaultDate = this.state.due;
  }

  onChange = (prop, value) => {
    this.setState({ [prop]: value });
  };

  onSave = () => {
    if (!this.state.title) return;
    const { dispatch, onClose } = this.props;

    const submitData = {
      title:       this.state.title,
      task_type:   'task',
      visibility:  'public',
      urgency:     1,
      date_due:    this.state.due,
      project:     this.state.project,
      agents:      this.state.assignee.get('agents').toArray(),
      departments: this.state.assignee.get('departments').toArray(),
      teams:       this.state.assignee.get('teams').toArray()
    };

    dispatch(addTask(submitData));
    if (onClose) {
      onClose();
    }
  };

  onSetEditing = (isEditing) => {
    if (!this.isChanged() && this.props.onSetEditing) {
      this.props.onSetEditing(isEditing);
    }
  };

  isChanged() {
    const { title, due, assignee, project } = this.state;
    return title || due !== this.defaultDate || project ||
      assignee.get('agents').size || assignee.get('teams').size || assignee.get('departments').size;
  }

  render() {
    const { title, due, project, assignee, links, submit } = this.state;

    return (
      <Card statusBars={false} type="task" additionalClasses="calendar-task-card">
        <SaveTaskButton onClick={this.onSave} />
        <CardLine>
          <CardLineLeft>
            <TitleForm value={title} onChange={val => this.onChange('title', val)} />
          </CardLineLeft>
          <CardLineRight>
            <AssignButton value={assignee}
              onChange={val => this.onChange('assignee', val)}
              onSetEditing={this.onSetEditing}
            />
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <div className="dpwd--card-line-item-container">
              <DateDue value={due}
                onChange={val => this.onChange('due', val)}
                onSetEditing={this.onSetEditing}
                openBySingleClick
              />
            </div>
            <div className="dpwd--card-line-item-container">
              <CardProjectContainer value={project}
                onChange={val => this.onChange('project', val)}
                onSetEditing={this.onSetEditing}
                openBySingleClick
              />
            </div>
            <div className="dpwd--card-line-item-container">
              <LinkedItemContainer value={links} />
            </div>
          </CardLineLeft>
        </CardLine>
      </Card>
    );
  }
}
