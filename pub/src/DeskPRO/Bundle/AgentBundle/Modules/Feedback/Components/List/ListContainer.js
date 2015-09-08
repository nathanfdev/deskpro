import React, {Component, PropTypes} from 'react';
import { ListFrame, ControlBar, ListTableViewSwitcher, OrderBy, TableView, TableBody }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard } from './FeedbackCard';
import { TableHeader } from './TableHeader';
import { FilterBy } from './FilterBy';
import { Row } from './Row';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";

export class ListContainer extends Component {

  static propTypes = {
    feedback: PropTypes.array.isRequired,
    sortOptions: PropTypes.array.isRequired,
    displayFields: PropTypes.array.isRequired,
    sort: PropTypes.string.isRequired,
    sortName: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    filters: PropTypes.object.isRequired,
    query: PropTypes.object.isRequired,
    viewMode: PropTypes.string.isRequired,
    sortTable: PropTypes.func.isRequired,
    toggleOrder: PropTypes.func.isRequired,
    toggleSort: PropTypes.func.isRequired,
    toggleView: PropTypes.func.isRequired
  };

  render() {
    const {
      feedback, viewMode, sortTable, sort, sortName, order, filters, query, sortOptions, displayFields,
      toggleView, toggleOrder, toggleSort
      } = this.props;

    return (
      <ListFrame>
        <ControlBar>
          <OrderBy sort={sort} sortName={sortName} order={order} sortOptions={sortOptions}
                   toggleOrder={toggleOrder.bind(this)}
                   toggleSort={toggleSort.bind(this)}
            />
          <FilterBy filters={filters} query={query}/>
          <ListTableViewSwitcher displayFields={displayFields} toggleView={toggleView.bind(this)} {...this.props}/>
        </ControlBar>

          {viewMode === constants.VIEW_MODE_LIST ?
            feedback.map((item, index) =>
                <FeedbackCard key={index} feedback={item}/>
            ) :
            <TableView>
              <TableHeader sortTable={sortTable.bind(this)}/>
              <TableBody>
                {feedback.map((feedback, index) => <Row key={index} feedback={feedback}/>)}
              </TableBody>
            </TableView>
          }
      </ListFrame>
    );
  }
}
