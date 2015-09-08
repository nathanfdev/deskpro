import React from 'react';
import { Nav } from './Nav';

export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
  }

  render() {
    const {labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories, choiceClick, dp_window, dispatch} = this.props;

    console.log(this.props);
    return (
      <Nav
        dispatch={dispatch.bind(this)}
        dp_window={dp_window}
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
