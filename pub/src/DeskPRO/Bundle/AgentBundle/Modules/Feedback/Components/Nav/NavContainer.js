import React from 'react';
import { Nav } from './Nav';

export class NavContainer extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {
        const {labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories, choiceClick} = this.props;
        return (
            <Nav
                onClick={choiceClick.bind(this)}
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
