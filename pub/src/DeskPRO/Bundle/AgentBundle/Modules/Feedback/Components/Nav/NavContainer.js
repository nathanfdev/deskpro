import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/FeedbackListActions'
import { Nav } from './Nav';
import $ from "jquery";

@connect(state => state.FeedbackList)

export class NavContainer extends React.Component {

    constructor(props) {
        super(props);
    }



    render() {
        const {labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories, feedback} = this.props;

        return (
            <Nav
                onClick={this.props.handleClick.bind(this)}
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
