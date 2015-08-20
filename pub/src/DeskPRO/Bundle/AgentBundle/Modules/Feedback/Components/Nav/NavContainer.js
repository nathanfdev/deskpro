import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/FeedbackListActions'
import { Nav } from './Nav';

@connect(state => state.FeedbackList)

export class NavContainer extends React.Component {

    constructor(props) {
        super(props);
        const { dispatch } = this.props;
        dispatch(actions.feedbackToValidate());
        dispatch(actions.commentsToReview());
        dispatch(actions.feedbackLabels());
        dispatch(actions.feedbackTypes());
        dispatch(actions.feedbackCustomCategories());
        dispatch(actions.feedbackNew());
        dispatch(actions.feedbackActiveStatus());
        dispatch(actions.feedbackClosedStatus());
        dispatch(actions.feedbackHiddenStatus());
    }

    render() {
        const {labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories} = this.props;

        return (
            <Nav
                labels={labels}
                types={types}
                toValidateCount={toValidateCount}
                commentsToReviewCount={commentsToReviewCount}
                statuses={statuses}
                customCategories={customCategories}
                />
        );
    }
}
