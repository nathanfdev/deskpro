import React, { PropTypes } from 'react';
import { Calendar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Calendar/index';
import { TaskCard } from './TaskCard/TaskCard';
import { TaskDragCard } from './TaskCard/TaskDragCard';
import { TaskCardPreviewContainer } from '../TaskCardPreviewContainer';
import { TaskCardPreview } from './TaskCard/TaskCardPreview';
import { CustomCardDragLayer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class CalendarView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object
  };

  render() {
    const config = {
      elements: this.props.tasks,
      elementName: 'task',
      dateField: 'date_due',
      additionalPrefix: 'Tasks for',
      card: <TaskCard />,
      draggable: {
        source: <TaskDragCard />
      }
    };

    return (
      <div>
        <Calendar {...config} />

        <CustomCardDragLayer>
          <TaskCardPreviewContainer>
            <TaskCardPreview />
          </TaskCardPreviewContainer>
        </CustomCardDragLayer>
      </div>
    );
  }
}
