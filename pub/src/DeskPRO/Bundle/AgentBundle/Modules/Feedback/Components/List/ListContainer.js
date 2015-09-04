import React from 'react';
import { ListFrame, ControlBar, ListTableViewSwitcher, TableView, OrderBy }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard} from './FeedbackCard';
import { TableHeader} from './TableHeader';
import { TableBody} from './TableBody';
//import { OrderBy} from './OrderBy';
import { FilterBy} from './FilterBy';

import * as actions from '../../Actions/FeedbackListActions'
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";
import { connect } from 'redux/react';

@connect(state => state.FeedbackList)

export class ListContainer extends React.Component {

  changeView(event) {
    event.stopPropagation();
    const {dispatch} = this.props;
    dispatch(actions.switchViewMode());
  }

  orderSwitch(event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch, sort, query, filters} = this.props;
    var elem = $(event.target),
      name = elem.text();
    sort.sort = elem.data('field');
    this.setState({sortName: name});
    elem.closest('a.ticket-control-button').find('span.sort-name').text(name);
    $('div.dropdown-choice').hide();
    dispatch(actions.loadFeedbackList(query, sort, filters));
  }

  showOrderChoice(event) {
    event.preventDefault();
    event.stopPropagation();
    var elem = $(event.target),
      filterChoice = elem.closest('a.ticket-control-button').find('div.focus-choice');
    $('div.dropdown-choice').hide();
    filterChoice.show();
  }


  switchSortDirection(event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch, sort, query, filters} = this.props;
    $('div.dropdown-choice').hide();
    var elem = $(event.target);
    if (sort.order === constants.ORDER_ASC) {
      elem.closest('a.ticket-control-button').find('i.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
    }
    else {
      elem.closest('a.ticket-control-button').find('i.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
    }
    dispatch(actions.switchOrderDirection());
    dispatch(actions.loadFeedbackList(query, sort, filters));
  }


  render() {
    let itemKey = 0;

    const { feedback, viewMode, sortTable, sort, sortName, filters, query } = this.props;

    const fields = [
      {name: 'id', label: 'ID'},
      {name: 'status', label: 'Status'},
      {name: 'hidden_status', label: 'Hidden status'},
      {name: 'status_category', label: 'Status category'},
      {name: 'title', label: 'Status category'},
      {name: 'author_name', label: 'Submitter'},
      {name: 'language_id', label: 'Lang'},
      {name: 'type', label: 'Type'},
      {name: 'slug', label: 'Slug'},
      {name: 'date_created', label: 'Created'},
      {name: 'date_published', label: 'Published'},
      {name: 'view_count', label: 'Views'},
      {name: 'total_rating', label: 'Rating'},
      {name: 'num_rating', label: 'Votes'},
      {name: 'num_comments', label: 'Comments'},
      {name: 'validating', label: 'Validating'},
      {name: 'popularity', label: 'Popularity'},
      {name: 'content', label: 'Content'},
      {name: 'custom_category', label: 'Category'}
    ];

    const options = [
      {field: 'date_created', label: 'Date'},
      {field: 'total_rating', label: 'Rating'},
      {field: 'num_ratings', label: 'Number of votes'}
    ];

    return (
      <ListFrame>
        <ControlBar>
          <OrderBy sort={sort} sortName={sortName} options={options}
                   orderSwitch={this.orderSwitch.bind(this)}
                   showOrderChoice={this.showOrderChoice.bind(this)}
                   switchSortDirection={this.switchSortDirection.bind(this)}
            />
          <FilterBy filters={filters} query={query}/>
          <ListTableViewSwitcher fields={fields} changeView={this.changeView.bind(this)} {...this.props}/>
        </ControlBar>

        {viewMode === constants.VIEW_MODE_LIST ?
          feedback.map(item =>
              <FeedbackCard key={itemKey++} feedback={item}/>
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
