import React, { PropTypes } from 'react';
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
  TicketLinkContainer,
  CardProjectContainer,
  AssignButton,
  AssigneeAvatar
} from '../../../TaskCard';

@connect()

export class TaskCardNew extends React.Component {

  static propTypes = {
    onClose: PropTypes.func,
    dispatch: PropTypes.func.isRequired,
    isChanged: PropTypes.func,
    task: PropTypes.object.isRequired,
    onSetEditing: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.model = {
      title: null,
      due: props.task.get('date_due'),
      assignee: Immutable.fromJS({
        agents: [],
        teams: [],
        departments: []
      }),
      project: null
    };
  }

  onChange(prop, value) {
    this.model[prop] = value;
    this.isChanged = true;
  }

  onAssign = (assignee) => {
    return new Promise(resolve => {
      this.model.assignee = assignee;
      resolve();
    });
  };

  onSave = () => {
    const { dispatch, onClose } = this.props;
    const submitData = {
      title: this.model.title,
      task_type: 'task',
      visibility: 'public',
      urgency: 1,
      date_due: this.model.due,
      project: this.model.project,
      agents: this.model.assignee.get('agents') || [],
      departments: this.model.assignee.get('departments') || [],
      teams: this.model.assignee.get('teams') || []
    };

    this.setState({
      submit: true
    });

    dispatch(addTask(submitData));
    onClose && onClose();
  };

  onSetEditing = (isEditing) => {
    this.props.onSetEditing && this.props.onSetEditing(isEditing);
  };

  render() {
    const { submit, isChanged } = this.props;
    const { title, due, project, assignee } = this.model;

    return (
      <Card statusBars={false}
            type="task"
            additionalClasses="calendar-task-card">

        <CardLine>
          <CardLineLeft>
            <TitleForm value={this.model.title} onChange={this.onChange.bind(this, 'title')} />
          </CardLineLeft>
          <CardLineRight>
            <AssignButton ref="assignee" task={assignee}
                          onAssign={this.onAssign}
                          onSetEditing={this.onSetEditing} />
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <DateDue value={this.model.due}
                     onChange={this.onChange.bind(this, 'due')}
                     openBySingleClick={true}
                     onSetEditing={this.onSetEditing} />
            <CardProjectContainer value={this.model.project}
                                  onChange={this.onChange.bind(this, 'project')}
                                  openBySingleClick={true}
                                  onSetEditing={this.onSetEditing} />
            <TicketLinkContainer />
          </CardLineLeft>
        </CardLine>
      </Card>
    );
  }
}
