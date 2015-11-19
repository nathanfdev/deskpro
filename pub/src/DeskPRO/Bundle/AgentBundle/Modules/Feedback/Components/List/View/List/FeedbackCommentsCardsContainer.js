import React, {Component, PropTypes} from 'react';
import { FeedbackCommentCard } from './FeedbackCommentCard';
import { peopleSelector, feedbackSelector } from '../../../../Selectors/list';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    comments: state.Feedback.list.get('elements'),
    selected: state.Feedback.list.get('selected'),
    people: peopleSelector(state),
    massAction: state.Feedback.list.get('massAction'),
    feedbackFromStore: feedbackSelector(state)
  });
})

export class FeedbackCommentsCardsContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    comments: PropTypes.object.isRequired,
    feedbackFromStore: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    people: PropTypes.object.isRequired,
    feedback: PropTypes.object.isRequired,
    massAction: PropTypes.bool.isRequired,
    selected: PropTypes.object.isRequired
  };

  render() {
    const {dispatch, comments, selected, toggleSelected, massAction, people, feedbackFromStore} = this.props;

    return (
      <div>
        {comments.map((element, index) =>
          <FeedbackCommentCard
            dispatch={dispatch}
            feedback={feedbackFromStore.get(element.feedback)}
            selected={selected.includes(element.id)}
            toggleSelected={toggleSelected}
            massAction={massAction}
            author={people.get(element.person)}
            comment={element}
            key={index}>
            {element.content}
          </FeedbackCommentCard>)}
      </div>
    );
  }

}