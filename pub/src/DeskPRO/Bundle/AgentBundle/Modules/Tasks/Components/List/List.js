import React, { PropTypes } from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { ControlBarContainer } from './ControlBarContainer';
import { CardView } from './View/Card/CardView';
import { KanbanView } from './View/Kanban/KanbanView';
import { TableView } from './View/Table/TableView';
import { CalendarView } from './View/Calendar/CalendarView';
import { ListGroupContainer } from './ListGroupContainer';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { toggleAll } from '../../Actions/listActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends React.Component {

  static propTypes = {
    currentView: PropTypes.string.isRequired,
    selectedCount: PropTypes.number.isRequired,
    currentNav: PropTypes.object,
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
    const { currentNav, loaded, selectedCount } = this.props;
    const checkbox = { count: selectedCount, action: toggleAll };
    return (
      <ListFrameContainer className="task-list-frame">
        <ListFrameMenu checkbox={checkbox}>
          <ControlBarContainer />
        </ListFrameMenu>
        {currentNav &&
          <LoadIndicator loaded={loaded}
                         opacity={0}
                         width={3}>

            <ListFrameContents>
              <ListGroupContainer>
                {this.renderView()}
              </ListGroupContainer>
            </ListFrameContents>
          </LoadIndicator>
        }
      </ListFrameContainer>
    );
  }
}
