import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/FeedbackListActions';
import { Nav } from './Nav';
import { feedbackTypesSelector }
  from '../../Selectors/list';

@connect(state => {
  return ({
    loaded: state.Feedback.nav.getIn(['async', 'done']),
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
    loaded: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired,
    toValidateCount: PropTypes.object.isRequired,
    commentsToReviewCount: PropTypes.object.isRequired,
    statuses: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired,
    types: PropTypes.object.isRequired,
    customCategories: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(actions.initialLoad());
  }

  render() {
    const {statuses, toValidateCount, commentsToReviewCount, dispatch, labels, types, customCategories, dpWindow, loaded} = this.props;

    return (
      <Nav
        loaded={loaded}
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
