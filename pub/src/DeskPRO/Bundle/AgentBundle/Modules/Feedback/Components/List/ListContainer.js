import React, {Component, PropTypes} from 'react';
import { ListFrame, ControlBar, ListTableViewSwitcher, OrderBy, TableView }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard} from './FeedbackCard';
import { TableHeader} from './TableHeader';
import { TableBody} from './TableBody';
import { FilterBy} from './FilterBy';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";

export class ListContainer extends Component {

  static propTypes = {
    feedback: PropTypes.array.isRequired,
    sortOptions: PropTypes.array.isRequired,
    displayFields: PropTypes.array.isRequired,
    sort: PropTypes.object.isRequired,
    filters: PropTypes.object.isRequired,
    query: PropTypes.object.isRequired,
    viewMode: PropTypes.string.isRequired,
    sortName: PropTypes.string.isRequired,
    sortTable: PropTypes.func.isRequired,
    switchView: PropTypes.func.isRequired,
    showOrderChoice: PropTypes.func.isRequired,
    switchSortDirection: PropTypes.func.isRequired,
    switchOrder: PropTypes.func.isRequired
  };

  render() {

    const {
      feedback, viewMode, sortTable, sort, sortName, filters, query, sortOptions, displayFields,
      switchView, switchOrder, showOrderChoice, switchSortDirection
      } = this.props;


    return (
      <ListFrame>
        <ControlBar>
          <OrderBy sort={sort} sortName={sortName} sortOptions={sortOptions}
                   switchOrder={switchOrder.bind(this)}
                   showOrderChoice={showOrderChoice.bind(this)}
                   switchSortDirection={switchSortDirection.bind(this)}
            />
          <FilterBy filters={filters} query={query}/>
          <ListTableViewSwitcher displayFields={displayFields} switchView={switchView.bind(this)} {...this.props}/>
        </ControlBar>

        {viewMode === constants.VIEW_MODE_LIST ?
          feedback.map((item, index) =>
              <FeedbackCard key={index} feedback={item}/>
          ) :
          <TableView>
            <TableHeader sortTable={sortTable.bind(this)}/>
            <TableBody feedback={feedback}/>
          </TableView>
        }
      </ListFrame>
    );
  }
}
