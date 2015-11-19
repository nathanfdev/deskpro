import React, { PropTypes } from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { ControlBarContainer } from './ControlBarContainer';
import { CardView } from './View/Card/CardView';
import { KanbanView } from './View/Kanban/KanbanView';
import { TableView } from './View/Table/TableView';
import { CalendarView } from './View/Calendar/CalendarView';
import { ListGroupContainer } from './ListGroupContainer';
import Loader from 'react-loader';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends React.Component {

  static propTypes = {
    currentView: PropTypes.string.isRequired,
    currentNav: PropTypes.object,
    tasks: PropTypes.object.isRequired,
    loaded: PropTypes.bool
  };

  renderView() {
    switch (this.props.currentView) {
      case constants.VIEW_MODE_CALENDAR:
        return <CalendarView />;
      case constants.VIEW_MODE_TABLE:
        return <TableView />;
      case constants.VIEW_MODE_KANBAN:
        return <KanbanView />;
      case constants.VIEW_MODE_CARD:
      default:
        return <CardView />;
    }
  }

  render() {
    const { currentNav, loaded, tasks } = this.props;

    return (
      <ListFrameContainer className="task-list-frame">
        <ControlBarContainer />
        {currentNav &&
          <Loader loaded={loaded}
                  color="green"
                  opacity={0}
                  width={3}>

            <ListFrameContents>
              <ListGroupContainer tasks={tasks}>
                {this.renderView()}
              </ListGroupContainer>
            </ListFrameContents>
          </Loader>
        }
      </ListFrameContainer>
    );
  }
}
