import React, {Component, PropTypes} from 'react';
import { FeedbackCard } from './FeedbackCard';
import { connect } from 'react-redux';
import { peopleSelector, feedbackTypesSelector, feedbackLabelsSelector, feedbackCommentsSelector, feedbackStatusesSelector, feedbackCategoriesSelector }
  from '../../../../Selectors/list';

@connect(state => {
  return ({
    feedback: state.Feedback.list.get('elements'),
    viewFields: state.Feedback.list.get('viewFields'),
    selected: state.Feedback.list.get('selected'),
    people: peopleSelector(state),
    feedbackTypes: feedbackTypesSelector(state),
    massAction: state.Feedback.list.get('massAction'),
    feedbackLabels: feedbackLabelsSelector(state),
    feedbackComments: feedbackCommentsSelector(state),
    feedbackCategories: feedbackCategoriesSelector(state),
    feedbackStatuses: feedbackStatusesSelector(state)
  });
})

export class FeedbackCardsContainer extends Component {

  static propTypes = {
    feedback: PropTypes.object.isRequired,
    viewFields: PropTypes.object,
    selected: PropTypes.object.isRequired,
    people: PropTypes.object.isRequired,
    feedbackTypes: PropTypes.object.isRequired,
    massAction: PropTypes.bool.isRequired,
    feedbackLabels: PropTypes.object.isRequired,
    feedbackComments: PropTypes.object.isRequired,
    feedbackStatuses: PropTypes.object.isRequired,
    feedbackCategories: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired
  };

  render() {
    const { feedback, viewFields, selected, toggleSelected, people, feedbackTypes, massAction, feedbackLabels, feedbackComments,
      feedbackStatuses, feedbackCategories } = this.props;

    return (
      <div>
        {feedback.map((element, index) =>
            <FeedbackCard key={index}
                          viewFields={viewFields}
                          feedback={element}
                          selected={selected.includes(element.id)}
                          toggleSelected={toggleSelected}
                          massAction={massAction}
                          author={people.get(element.person_id)}
                          feedbackStatus={feedbackStatuses.get(element.id)}
                          feedbackCategory={feedbackCategories.get(element.id)}
                          feedbackComments={feedbackComments.get(element.id)}
                          feedbackLabels={element.labels}
                          type={feedbackTypes.get(element.category_id)}
              />
        )}
      </div>
    );
  }
}