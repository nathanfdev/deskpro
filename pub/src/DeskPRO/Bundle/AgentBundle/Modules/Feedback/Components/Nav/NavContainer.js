import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/feedbackNavActions';
import { Nav } from './Nav';
import { typeCountersSelector, categoryCountersSelector, statusCountersSelector, feedbackLabelsSelector }
  from '../../Selectors/nav';


@connect(state => {
  return ({
    loaded: state.Feedback.nav.getIn(['async', 'done']),
    toValidateCount: state.Feedback.nav.get('toValidateCount'),
    commentsToReviewCount: state.Feedback.nav.get('commentsToReviewCount'),
    statuses: statusCountersSelector(state),
    types: typeCountersSelector(state),
    labels: feedbackLabelsSelector(state),
    categories: categoryCountersSelector(state),
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
    types: PropTypes.object,
    categories: PropTypes.object,
    dpWindow: PropTypes.object.isRequired
  };

  componentDidMount() {
    this.props.dispatch(actions.initialLoad());
  }

  render() {
    const {statuses, toValidateCount, commentsToReviewCount, dispatch, labels, types, categories, dpWindow, loaded} = this.props;

    return (
        <Nav loaded={loaded}
             toValidateCount={toValidateCount}
             commentsToReviewCount={commentsToReviewCount}
             dispatch={dispatch}
             statuses={statuses}
             labels={labels}
             types={types}
             categories={categories}
             dpWindow={dpWindow}/>
    );
  }
}
