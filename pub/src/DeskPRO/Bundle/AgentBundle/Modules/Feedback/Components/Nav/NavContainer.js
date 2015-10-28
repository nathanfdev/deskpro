import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/FeedbackListActions';
import * as commentActions from '../../Actions/FeedbackCommentsActions';
import { Nav } from './Nav';
import { loadFeedbackTypes } from '../../RecordStores/Actions/feedbackTypesActions';
import { loadFeedbackLabels } from '../../RecordStores/Actions/feedbackLabelsActions';

@connect(state => {
  return ({
    toValidateCount: state.Feedback.nav.get('toValidateCount'),
    commentsToReviewCount: state.Feedback.nav.get('commentsToReviewCount'),
    statuses: state.Feedback.nav.get('statuses').toJS(),
    types: state.Feedback.nav.get('types'),
    labels: state.Feedback.nav.get('labels'),
    customCategories: state.Feedback.nav.get('customCategories').toJS(),
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

  constructor(props) {
    super(props);
    const { dispatch } = this.props;

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
  }

  commentsView(group, event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch } = this.props;
    dispatch(actions.setSort('date_created'));
    dispatch(actions.setOrder('desc'));
    dispatch(commentActions.loadCommentsList());
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
        commentsView={this.commentsView.bind(this)}
        dpWindow={dpWindow}
        />
    );
  }
}
