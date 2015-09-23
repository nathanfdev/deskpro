import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/FeedbackListActions'
import { Nav } from './Nav';
import { sortingDataSelector, filterDataSelector } from '../../Selectors/list';
import { groupDataSelector } from '../../Selectors/nav';

@connect(state => {
  return ({
    toValidateCount: state.Feedback.nav.get('toValidateCount'),
    statuses: state.Feedback.nav.get('statuses').toJS(),
    types: state.Feedback.nav.get('types').toJS(),
    labels: state.Feedback.nav.get('labels'),
    customCategories: state.Feedback.nav.get('customCategories').toJS(),
    currentFilterMode: filterDataSelector(state),
    currentGroup: groupDataSelector(state)
  });
})

export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    const { dispatch, currentFilterMode } = this.props;

    dispatch(actions.feedbackToValidate());
    dispatch(actions.commentsToReview());
    dispatch(actions.feedbackLabels());
    dispatch(actions.feedbackTypes());
    dispatch(actions.feedbackCustomCategories());
    dispatch(actions.feedbackNew());
    dispatch(actions.feedbackActiveStatus());
    dispatch(actions.feedbackClosedStatus());
    dispatch(actions.feedbackHiddenStatus());
    dispatch(actions.getFilterValues(currentFilterMode.name));
    dispatch(actions.getDisplayFieldsFromPersonSetting());
    dispatch(actions.loadFeedbackList());
  }

  render() {
    const {statuses, toValidateCount, dispatch, labels, types, customCategories, currentGroup} = this.props;

    return (
      <Nav
        toValidateCount={toValidateCount}
        dispatch={dispatch}
        statuses={statuses}
        labels={labels}
        types={types}
        customCategories={customCategories}
        currentGroup={currentGroup}
        groupChoice={this.groupChoice.bind(this)}
        />
    );
  }

  groupChoice(group, event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch } = this.props;
    dispatch(actions.changeGroupState(group));
  }
}
