import React, { PropTypes } from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { Controls } from './ControlBar/Controls';
import { CardView } from './View/Card/CardView';
import { KanbanView } from './View/Kanban/KanbanView';
import { TableView } from './View/Table/TableView';
import { CalendarView } from './View/Calendar/CalendarView';
import Loader from 'react-loader';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends React.Component {

  static propTypes = {
    view: PropTypes.string.isRequired,
    tasks: PropTypes.array.isRequired,
    loaded: PropTypes.bool.isRequired
  };

  renderView() {
    const { tasks, view } = this.props;

    switch (view) {
      case constants.VIEW_MODE_CALENDAR:
        return <CalendarView tasks={tasks} />;
      case constants.VIEW_MODE_TABLE:
        return <TableView tasks={tasks} />;
      case constants.VIEW_MODE_KANBAN:
        return <KanbanView tasks={tasks} />;
      case constants.VIEW_MODE_CARD:
      default:
        return <CardView tasks={tasks} />;
    }
  }

  render() {
    return (
      <ListFrameContainer>
        <Controls />
        <Loader loaded={this.props.loaded}>
          {this.renderView()}
        </Loader>
      </ListFrameContainer>
    );
  }
}
