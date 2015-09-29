import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/FeedbackListActions';
import { Nav } from './Nav';
import { filterDataSelector } from '../../Selectors/list';
import { groupDataSelector } from '../../Selectors/nav';
import { loadFeedbackTypes } from '../../RecordStores/Actions/feedbackTypesActions';
import { loadFeedbackLabels } from '../../RecordStores/Actions/feedbackLabelsActions';

@connect(state => {
  return ({
    toValidateCount: state.Feedback.nav.get('toValidateCount'),
    commentsToReviewCount: state.Feedback.nav.get('commentsToReviewCount'),
    statuses: state.Feedback.nav.get('statuses').toJS(),
    types: state.Feedback.nav.get('types').toJS(),
    labels: state.Feedback.nav.get('labels'),
    customCategories: state.Feedback.nav.get('customCategories').toJS(),
    currentFilterMode: filterDataSelector(state),
    currentGroup: groupDataSelector(state)
  });
})

export class NavContainer extends Component {

  static propTypes = {
    groupChoice: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired,
    currentFilterMode: PropTypes.object.isRequired,
    toValidateCount: PropTypes.string.isRequired,
    commentsToReviewCount: PropTypes.string.isRequired,
    statuses: PropTypes.array.isRequired,
    labels: PropTypes.array.isRequired,
    types: PropTypes.array.isRequired,
    customCategories: PropTypes.array.isRequired,
    currentGroup: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    const { dispatch, currentFilterMode } = this.props;

    dispatch(loadFeedbackTypes());
    dispatch(loadFeedbackLabels());
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


  groupChoice(group, event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch } = this.props;
    dispatch(actions.changeGroupState(group));
  }

  render() {
    const {statuses, toValidateCount, commentsToReviewCount, dispatch, labels, types, customCategories, currentGroup} = this.props;

    return (
      <Nav
        toValidateCount={toValidateCount}
        commentsToReviewCount={commentsToReviewCount}
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
}
