import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/feedbackNavActions';
import { Nav } from './Nav';
import { typeCountersSelector, categoryCountersSelector, statusCountersSelector, feedbackLabelsSelector }
  from '../../Selectors/nav';


@connect(state => {
  return ({
    isLoaded: state.Feedback.nav.getIn(['async', 'done']),
    toValidateCount: state.Feedback.nav.get('toValidateCount'),
    commentsToReviewCount: state.Feedback.nav.get('commentsToReviewCount'),
    statuses: statusCountersSelector(state),
    types: typeCountersSelector(state),
    labels: feedbackLabelsSelector(state),
    categories: categoryCountersSelector(state)
  });
})
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.dispatch(actions.initialLoad());
  }

  render() {
    return (
      <Nav {...this.props} />
    );
  }
}
