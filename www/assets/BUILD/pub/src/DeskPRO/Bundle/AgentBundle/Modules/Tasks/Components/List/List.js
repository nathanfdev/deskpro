import PropTypes from 'prop-types';
import React from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameContents';
import { PaginationBoxView } from '../../../Common/Components/Pagination/PaginationBoxView';
import { ControlBarContainer } from './ControlBarContainer';
import { MassActionContainer } from './MassActionContainer';
import { CardView } from './View/Card/CardView';
import { KanbanView } from './View/Kanban/KanbanView';
import { TableView } from './View/Table/TableView';
import { CalendarView } from './View/Calendar/CalendarView';
import { ListGroupContainer } from './ListGroupContainer';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends React.Component {
  static propTypes = {
    currentView:     PropTypes.string.isRequired,
    selected:        PropTypes.object.isRequired,
    handlePageClick: PropTypes.func.isRequired,
    pagination:      PropTypes.object,
    currentNav:      PropTypes.object,
    isLoaded:        PropTypes.bool
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
    const { currentNav, isLoaded, selected, pagination, handlePageClick } = this.props;

    return (
      <ListFrameContainer className="task-list-frame">
        <ListFrameMenu>
          {!selected.size && <ControlBarContainer key="1" />}
          {selected.size && <MassActionContainer key="2" />}
        </ListFrameMenu>
        {currentNav
        && <ListFrameContents isLoaded={isLoaded}>
          <ListGroupContainer>
            {this.renderView()}
          </ListGroupContainer>
          {pagination && pagination.get('total_pages') > 1
          && <PaginationBoxView
            breakLabel={<li><span className="pagination-dots">&hellip;</span></li>}
            pageNum={pagination.get('total_pages')}
            currentPage={pagination.get('current_page')}
            clickCallback={handlePageClick}
          />
          }
        </ListFrameContents>
        }
      </ListFrameContainer>
    );
  }
}
