import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/FeedbackListActions';
import { Nav } from './Nav';
import { loadFeedbackTypes } from '../../RecordStores/Actions/feedbackTypesActions';
import { feedbackTypesSelector }
  from '../../Selectors/list';

@connect(state => {
  return ({
    toValidateCount: state.Feedback.nav.get('toValidateCount'),
    commentsToReviewCount: state.Feedback.nav.get('commentsToReviewCount'),
    statuses: state.Feedback.nav.get('statuses'),
    types: feedbackTypesSelector(state),
    labels: state.Feedback.nav.get('labels'),
    customCategories: state.Feedback.nav.get('customCategories'),
    dpWindow: state.Application.dpWindow
  });
})
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    toValidateCount: PropTypes.number.isRequired,
    commentsToReviewCount: PropTypes.number.isRequired,
    statuses: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired,
    types: PropTypes.object.isRequired,
    customCategories: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(actions.initialLoad());
   /* // dispatch(loadFeedbackTypes());
    dispatch(actions.loadLabels());
    dispatch(actions.feedbackToValidate());
    dispatch(actions.commentsToReview());
    // dispatch(actions.feedbackCustomCategories());
    dispatch(actions.feedbackNew());
    dispatch(actions.feedbackActiveStatus());
    dispatch(actions.feedbackClosedStatus());
    dispatch(actions.feedbackHiddenStatus());*/
  }

  render() {
    const {statuses, toValidateCount, commentsToReviewCount, dispatch, labels, types, customCategories, dpWindow} = this.props;
    return (
      <Nav
        toValidateCount={toValidateCount}
        commentsToReviewCount={commentsToReviewCount}
        dispatch={dispatch}
        statuses={statuses}
        labels={labels}
        types={types}
        customCategories={customCategories}
        dpWindow={dpWindow}
        />
    );
  }
}
