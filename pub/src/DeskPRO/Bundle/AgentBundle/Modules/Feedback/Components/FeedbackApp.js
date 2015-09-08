import React from "react";
import AppContainer from "DeskPRO/Component/AppContainer";
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';
import * as actions from '../Actions/FeedbackListActions'
import * as AppActions from "DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/AppActions";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";

import { connect } from 'redux/react';
@connect(state => state.FeedbackList)

export class FeedbackApp extends React.Component {

  constructor(props) {
    super(props);
    const { query, sort, order, filters, dispatch } = this.props;
    dispatch(actions.feedbackToValidate());
    dispatch(actions.commentsToReview());
    dispatch(actions.feedbackLabels());
    dispatch(actions.feedbackTypes());
    dispatch(actions.feedbackCustomCategories());
    dispatch(actions.feedbackNew());
    dispatch(actions.feedbackActiveStatus());
    dispatch(actions.feedbackClosedStatus());
    dispatch(actions.feedbackHiddenStatus());
    dispatch(actions.loadFeedbackList(query, sort, order, filters));
    dispatch(actions.getFilterValues(filters.alias));
  }

  choiceClick(params, event) {
    event.preventDefault();
    event.stopPropagation();
    const {sort, dispatch, filters} = this.props;
    dispatch(actions.changeQueryState(params));
    dispatch(actions.loadFeedbackList(params, sort, filters));
    $('.sidebar-list a.item, .sidebar-list a.item-label').removeClass('active');
    $(event.target).closest('a').addClass('active');
  }

  sortTable(param, event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch, query, filters} = this.props;
    let elem = $(event.target),
      th = elem.closest('th'),
      siblings = th.siblings('th'),
      caret = th.find('i.fa');
    th.data('order') === 'asc' ? th.data('order', 'desc') : th.data('order', 'asc');
    siblings.find('span.sort-direction').remove();
    siblings.data('order', '');
    if (caret.length > 0) {
      caret.toggleClass('fa-caret-down').toggleClass('fa-caret-up');
    }
    else {
      th.append('<span class="sort-direction"><i class="fa fa-caret-down"></i></span>')
    }
    let order = th.data('order');
    dispatch(actions.setSort(param, th.data('order')));
    dispatch(actions.loadFeedbackList(query, param, order, filters));
  }


  toggleView(event) {
    event.stopPropagation();
    const {dispatch} = this.props;
    dispatch(AppActions.toggleViewMode());
  }

  /** Change sort option (Order By ...)*/
  toggleSort(event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch, order, query, filters} = this.props;
    var elem = $(event.target),
      name = elem.text(),
      table = elem.closest('div.feedback-list').find('table'),
      newSort = elem.data('field');
    table.find('i.fa').remove();
    this.setState({sort: newSort});
    elem.closest('a.ticket-control-button').find('span.sort-name').text(name);
    $('div.dropdown-choice').hide();
    dispatch(actions.loadFeedbackList(query, newSort, order, filters));
  }

  /** Change sort order (ASC, DESC)*/
  toggleOrder(event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch, sort, order, query, filters} = this.props;
    $('div.dropdown-choice').hide();
    let elem = $(event.target),
      newOrder = constants.ORDER_ASC;
    if (order === newOrder) {
      newOrder = constants.ORDER_DESC;
      elem.closest('a.ticket-control-button').find('i.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
    }
    else {
      elem.closest('a.ticket-control-button').find('i.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
    }
    dispatch(AppActions.toggleOrder());
    dispatch(actions.loadFeedbackList(query, sort, newOrder, filters));
  }


  render() {

    const displayFields = [
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

    return (
      <AppContainer thisAppId="feedback" {...this.props}>
        <NavContainer {...this.props} choiceClick={this.choiceClick}/>
        <ListContainer {...this.props}
          sortTable={this.sortTable}
          toggleView={this.toggleView}
          toggleOrder={this.toggleOrder}
          toggleSort={this.toggleSort}
          displayFields={displayFields}
          />
      </AppContainer>
    );
  }
}
