import React, {Component, PropTypes} from 'react';
import { FeedbackCommentCard } from './FeedbackCommentCard';

export class FeedbackCommentList extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    comments: PropTypes.array.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    people: PropTypes.array.isRequired,
    emails: PropTypes.array.isRequired,
    feedback: PropTypes.array.isRequired,
    massAction: PropTypes.bool.isRequired,
    selected: PropTypes.array.isRequired
  };

  render() {
    const {dispatch, comments, selected, toggleSelected, massAction, people, emails, feedback} = this.props;

    return (
      <div>
        {comments.map((element, index) =>
          <FeedbackCommentCard
            dispatch={dispatch}
            feedback={feedback.get(element.feedback_id)}
            selected={selected.includes(element.id)}
            toggleSelected={toggleSelected}
            massAction={massAction}
            author={people.get(element.person_id)}
            email={emails.get(element.person_id) ? emails.get(element.person_id).get('email') : ''}
            comment={element}
            key={index}>
            {element.content}
          </FeedbackCommentCard>)}
      </div>
    );
  }

}