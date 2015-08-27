import React from 'react';
import * as actions from '../../Actions/FeedbackListActions'
import { Nav } from './Nav';
import $ from "jquery";

export class NavContainer extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {
        const {labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories, handleClick} = this.props;

        return (
            <Nav
                onClick={handleClick.bind(this)}
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
