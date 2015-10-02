import React, {Component, PropTypes} from 'react';
import { FeedbackCommentCard } from './FeedbackCommentCard';

export class FeedbackCommentList extends Component {

  static propTypes = {
    comments: PropTypes.array.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    people: PropTypes.array.isRequired,
    massAction: PropTypes.bool.isRequired,
    selected: PropTypes.array.isRequired
  };

  render() {
    const {comments, selected, toggleSelected, massAction, people} = this.props;

    return (
      <div>
        {comments.map((element, index) =>
          <FeedbackCommentCard
            selected={selected.includes(element.id)}
            toggleSelected={toggleSelected}
            massAction={massAction}
            author={people[element.person_id]}
            comment={element}
            key={index}>
            {element.content}
          </FeedbackCommentCard>)}
      </div>
    );
  }

}