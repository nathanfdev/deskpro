import React from "react";
import AppContainer from "DeskPRO/Component/AppContainer";
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';
import * as actions from '../Actions/FeedbackListActions'
import $ from "jquery";

import { connect } from 'redux/react';
@connect(state => state.FeedbackList)

export class FeedbackApp extends React.Component {

    constructor(props) {
        super(props);
        const { query, sort, filters, dispatch } = this.props;
        dispatch(actions.feedbackToValidate());
        dispatch(actions.commentsToReview());
        dispatch(actions.feedbackLabels());
        dispatch(actions.feedbackTypes());
        dispatch(actions.feedbackCustomCategories());
        dispatch(actions.feedbackNew());
        dispatch(actions.feedbackActiveStatus());
        dispatch(actions.feedbackClosedStatus());
        dispatch(actions.feedbackHiddenStatus());
        dispatch(actions.loadFeedbackList(query, sort, filters));
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
        th.data('order') === 'Asc' ? th.data('order', 'Desc') : th.data('order', 'Asc');
        siblings.find('i.fa').remove();
        siblings.data('order', '');
        if (caret.length > 0) {
            caret.toggleClass('fa-caret-down').toggleClass('fa-caret-up');
        }
        else {
            th.append('<i class="fa fa-caret-up"/>')
        }
        let order = th.data('order');
        dispatch(actions.setSort(param, th.data('order')));
        dispatch(actions.loadFeedbackList(query, {sort: param, order: order}, filters));
    }

    render() {
        return (
            <AppContainer thisAppId="feedback">
                <NavContainer {...this.props} choiceClick={this.choiceClick.bind(this)}/>
                <ListContainer {...this.props} sortTable={this.sortTable.bind(this)}/>
            </AppContainer>
        );
    }
}
