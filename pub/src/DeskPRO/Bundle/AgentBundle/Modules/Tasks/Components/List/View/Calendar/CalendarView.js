import React, { PropTypes } from 'react';
import { Calendar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Calendar/index';
import { TaskDragCard } from './TaskCard/TaskDragCard';

export class CalendarView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object
  };

  render() {
    const config = {
      elements: this.props.tasks,
      elementName: 'task',
      dateField: 'date_due',
      draggable: {
        sourceCard: <TaskDragCard />
      }
    };

    return <Calendar {...config} />;
  }
}
