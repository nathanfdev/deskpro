import React from "react";
import AppContainer from "DeskPRO/Component/AppContainer";
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';
import * as actions from '../Actions/FeedbackListActions'
import { connect } from 'redux/react';
import $ from "jquery";
import reducer from '../Reducers/index';

@connect(state => state.FeedbackList)

export class FeedbackApp extends React.Component {

    constructor(props) {
        super(props);
        const { query, sort, dispatch } = this.props;
        dispatch(actions.feedbackToValidate());
        dispatch(actions.commentsToReview());
        dispatch(actions.feedbackLabels());
        dispatch(actions.feedbackTypes());
        dispatch(actions.feedbackCustomCategories());
        dispatch(actions.feedbackNew());
        dispatch(actions.feedbackActiveStatus());
        dispatch(actions.feedbackClosedStatus());
        dispatch(actions.feedbackHiddenStatus());
        dispatch(actions.loadFeedbackList(query, sort));
    }


    choiceClick(params, event) {
        event.preventDefault();
        event.stopPropagation();
        const {sort, dispatch} = this.props;
        dispatch(actions.changeQueryState(params));
        dispatch(actions.loadFeedbackList(params, sort));
        $('.sidebar-list a.item, .sidebar-list a.item-label').removeClass('active');
        $(event.target).closest('a').addClass('active');
    }

    render() {
        return (
            <AppContainer thisAppId="feedback">
                <NavContainer {...this.props} choiceClick={this.choiceClick.bind(this)}/>
                <ListContainer {...this.props} />
            </AppContainer>
        );
    }
}
