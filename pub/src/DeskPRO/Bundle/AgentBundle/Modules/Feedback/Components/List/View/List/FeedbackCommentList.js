import React, {Component, PropTypes} from 'react';
import { FeedbackCommentCard } from './FeedbackCommentCard';

export class FeedbackCommentList extends Component {

  static propTypes = {
    comments: PropTypes.array.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    people: PropTypes.array.isRequired,
    emails: PropTypes.array.isRequired,
    feedback: PropTypes.array.isRequired,
    massAction: PropTypes.bool.isRequired,
    selected: PropTypes.array.isRequired
  };

  render() {
    const {comments, selected, toggleSelected, massAction, people, emails, feedback} = this.props;

    return (
      <div>
        {comments.map((element, index) =>
          <FeedbackCommentCard
            feedback={feedback[element.feedback_id]}
            selected={selected.includes(element.id)}
            toggleSelected={toggleSelected}
            massAction={massAction}
            author={people[element.person_id]}
            email={emails[element.person_id].email}
            comment={element}
            key={index}>
            {element.content}
          </FeedbackCommentCard>)}
      </div>
    );
  }

}