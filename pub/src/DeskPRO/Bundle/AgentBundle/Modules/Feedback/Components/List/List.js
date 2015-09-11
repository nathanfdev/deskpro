import React, {Component, PropTypes} from 'react';
import { ListFrame, ControlBar, ListTableViewSwitcher, OrderBy, TableView, TableBody, Pagination }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackListControlBar } from './ControlBar/FeedbackListControlBar';
import { FeedbackList } from './View/List/FeedbackList';
import { FeedbackTable } from './View/Table/FeedbackTable';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";

import { connect } from 'redux/react';
@connect(state => state.FeedbackList)

export class List extends Component {

  constructor(props) {
    super(props);
    const { query, sort, order, filters, dispatch } = this.props;
    dispatch(actions.loadFeedbackList(query, sort, order, filters));
    dispatch(actions.getFilterValues(filters.alias));
  }

  static propTypes = {
    elements: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired
  };


  render() {

    const { elements, viewMode } = this.props;

    return (
      <ListFrame>
        <FeedbackListControlBar />
        {viewMode === 'list' ? <FeedbackList elements={elements}/> : <FeedbackTable elements={elements}/>}
        <Pagination/>
      </ListFrame>
    );
  }

}
