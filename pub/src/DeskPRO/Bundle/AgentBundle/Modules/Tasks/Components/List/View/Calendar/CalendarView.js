import React, { PropTypes } from 'react';
import { Calendar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Calendar/Calendar';

export class CalendarView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object
  };

  render() {
    return <Calendar elements={this.props.tasks} />;
  }
}
