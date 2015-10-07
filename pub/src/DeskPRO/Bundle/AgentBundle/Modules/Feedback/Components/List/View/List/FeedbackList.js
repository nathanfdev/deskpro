import React, {Component, PropTypes} from 'react';
import { FeedbackCard } from './FeedbackCard';

export class FeedbackList extends Component {

  static propTypes = {
    elements: PropTypes.array.isRequired,
    selected: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired
  };

  render() {
    const {elements, selected, toggleSelected, people,feedbackTypes,  massAction, feedbackLabels, feedbackComments,
      feedbackStatuses} = this.props;

    return (
      <div>
        {elements.map((element, index) => <FeedbackCard key={index}
                                                        feedback={element}
                                                        selected={selected.includes(element.id)}
                                                        toggleSelected={toggleSelected}
                                                        massAction={massAction}
                                                        author={people[element.person_id]}
                                                        feedbackStatus={feedbackStatuses[element.id]}
                                                        feedbackComments={feedbackComments[element.id]}
                                                        feedbackLabels={feedbackLabels[element.id]}
                                                        type={feedbackTypes[element.category_id]}/>
        )}
      </div>
    );
  }

}