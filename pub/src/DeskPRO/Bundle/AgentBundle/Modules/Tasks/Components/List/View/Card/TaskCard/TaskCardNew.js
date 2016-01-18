import React, { PropTypes } from 'react';
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
  CardProject,
  Comments,
  AssignButton,
  AssigneeAvatar
} from '../../../TaskCard/index';

export class TaskCardNew extends React.Component {

  static propTypes = {
    onClose: PropTypes.func,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.model = {
      title: null,
      due: null,
      assignee: Immutable.fromJS({
        agents: [],
        teams: [],
        departments: []
      }),
      project: null
    };

    this.state = {
      isChanged: false
    };
    this.prev = false;
  }

  reset = () => {
    if (!this.state.isChanged) {
      return;
    }
    this.model = {
      title: null,
      due: null,
      assignee: Immutable.fromJS({
        agents: [],
        teams: [],
        departments: []
      }),
      project: null
    };
    this.setState({isChanged: false});
  };

  onSetEditing = (isEditing) => {
    this.setState({isChanged: isEditing || this.prev});
  };

  onChange(prop, value) {
    const update = this.state.isChanged;
    this.model[prop] = value;
    update ? this.forceUpdate() : this.setState({isChanged: true});
  }

  componentDidUpdate(prevProps, prevState) {
    this.prev = prevState.isChanged;
  }

  onAssign = (assignee) => {
    return new Promise(resolve => {
      this.model.assignee = assignee;
      this.setState({isChanged: true});
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

    const promise = dispatch(addTask(submitData));
    promise.then(() => onClose && onClose());
  };

  render() {
    const { submit } = this.props;
    const { title, due, project, assignee } = this.model;

    return (
      <Card type="task">
        <SaveTaskButton onClick={this.onSave} submit={submit} />
        <CardReset isActive={this.state.isChanged} onClick={this.reset} />
        <CardLine>
          <CardLineLeft>
            <div className="dpwd--card-title">
              <TitleForm onChange={this.onChange.bind(this, 'title')} value={title} />
            </div>
          </CardLineLeft>
          <CardLineRight>
            <AssignButton onSetEditing={this.onSetEditing} task={assignee} onAssign={this.onAssign} />
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <DateDue onChange={this.onChange.bind(this, 'due')} value={due} onSetEditing={this.onSetEditing}
                     openBySingleClick={true} />
            <CardProject projectId={project} onChange={this.onChange.bind(this, 'project')}
                         onSetEditing={this.onSetEditing}
                         openBySingleClick={true} />
          </CardLineLeft>
        </CardLine>
      </Card>
    );
  }
}
