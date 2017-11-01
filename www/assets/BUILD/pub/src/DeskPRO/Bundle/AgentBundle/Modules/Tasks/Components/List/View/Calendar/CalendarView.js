import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { Calendar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Calendar/Calendar';
import { TaskCard } from './TaskCard/TaskCard';
import { TaskCardNew } from './TaskCard/TaskCardNew';
import { TaskDragCard } from './TaskCard/TaskDragCard';
import { TaskCardDragTarget } from './TaskCard/TaskCardDragTarget';
import { TaskCardEditContainer } from '../../TaskCard/TaskCardEditContainer';
import { TaskCardPreviewContainer } from '../../TaskCard/TaskCardPreviewContainer';
import { TaskCardPreview } from './TaskCard/TaskCardPreview';
import { CustomCardDragLayer } from '../../CustomCardDragLayer';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Detached as Positioned } from 'DeskPRO/Component/Positioned/Detached';

export class CalendarView extends React.Component {

  static propTypes = {
    tasks:         PropTypes.object,
    onChangeGroup: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      task: null
    };
  }

  resetNewTask = (force) => {
    if (!force && this.isEditing) return;
    this.newTaskTarget = null;
    this.setState({
      task: null
    });
  };

  onSetEditing = (isEditing) => {
    this.isEditing = isEditing;
  };

  createNewTask = (date, targetElement) => {
    this.newTaskTarget = targetElement;
    this.setState({
      task: Immutable.fromJS({ date_due: date.format('YYYY-MM-DDTHH:mm:ssZ') })
    });
  };

  render() {
    const { tasks, onChangeGroup } = this.props;
    const config = {
      elements:         tasks,
      elementName:      'task',
      dateField:        'date_due',
      additionalPrefix: 'Tasks for',
      card:             (
                          <TaskCardEditContainer>
                            <TaskCard calendarFields={Immutable.List()} />
                          </TaskCardEditContainer>
                        ),
      draggable: {
        source: <TaskDragCard />,
        target: <TaskCardDragTarget onChangeGroup={onChangeGroup} />
      },
      onDoubleClick: (date, event) => {
        this.createNewTask(date, event.target);
      }
    };

    return (
      <div>
        <Calendar {...config} />

        <Positioned positionTarget={this.newTaskTarget} positionAt="center center" isOpen={!!this.state.task}>
          <ClickOut onClickOut={() => this.resetNewTask(false)}>
            <TaskCardNew
              task={this.state.task}
              onSetEditing={this.onSetEditing}
              onClose={() => this.resetNewTask(true)}
            />
          </ClickOut>
        </Positioned>

        <CustomCardDragLayer>
          <TaskCardPreviewContainer>
            <TaskCardPreview />
          </TaskCardPreviewContainer>
        </CustomCardDragLayer>
      </div>
    );
  }
}

