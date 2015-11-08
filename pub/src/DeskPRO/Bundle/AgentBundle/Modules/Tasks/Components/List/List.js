import React, { PropTypes } from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { Controls } from './ControlBar/Controls';
import { CardView } from './View/Card/CardView';
import { KanbanView } from './View/Kanban/KanbanView';
import { TableView } from './View/Table/TableView';
import { CalendarView } from './View/Calendar/CalendarView';
import Loader from 'react-loader';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends React.Component {

  static propTypes = {
    currentView: PropTypes.string.isRequired,
    tasks: PropTypes.array.isRequired,
    loaded: PropTypes.bool.isRequired,
    listParams: PropTypes.object.isRequired
  };

  renderView() {
    const { tasks, currentView } = this.props;

    switch (currentView) {
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
    const { listParams, loaded } = this.props;

    return (
      <ListFrameContainer>
        <Controls />
        {listParams &&
          <Loader loaded={loaded}>
            <ListFrameContents>
              {this.renderView()}
            </ListFrameContents>
          </Loader>
        }
      </ListFrameContainer>
    );
  }
}
