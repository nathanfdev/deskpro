import React, {Component, PropTypes} from 'react';
import { FeedbackCommentCard } from './FeedbackCommentCard';
import { feedbackCommentsSelector, peopleSelector, feedbackSelector } from '../../../../Selectors/recordStores';
import { idsSelector } from '../../../../Selectors/list';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    ids: idsSelector(state),
    comments: feedbackCommentsSelector(state),
    selected: selectedSelector(state),
    people: peopleSelector(state),
    massAction: state.Feedback.list.get('massAction'),
    feedback: feedbackSelector(state)
  });
})

export class FeedbackCommentsCardsContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    ids: PropTypes.array.isRequired,
    comments: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    people: PropTypes.object.isRequired,
    feedback: PropTypes.object.isRequired,
    massAction: PropTypes.bool.isRequired,
    selected: PropTypes.object.isRequired
  };

  renderComment(id) {
    const { dispatch, comments, selected, toggleSelected, massAction, people, feedback } = this.props;
    const element = comments.get(id);
    return (
      <FeedbackCommentCard key={id}
                           comment={element}
                           dispatch={dispatch}
                           feedback={feedback.get(element.get('feedback'))}
                           selected={selected.includes(id)}
                           toggleSelected={toggleSelected}
                           massAction={massAction}
                           author={people.get(element.get('person'))}/>
    );
  }

  render() {
    const {ids} = this.props;

    return (
      <div>
        {ids.map(id => this.renderComment(id))}
      </div>
    );
  }

}