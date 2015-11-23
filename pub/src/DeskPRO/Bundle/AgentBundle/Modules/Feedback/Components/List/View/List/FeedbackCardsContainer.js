import React, {Component, PropTypes} from 'react';
import { FeedbackCard } from './FeedbackCard';
import { connect } from 'react-redux';
import { peopleSelector, feedbackTypesSelector, feedbackCommentsSelector, feedbackCategoriesSelector, feedbackStatusCategoriesSelector }
  from '../../../../Selectors/list';

@connect(state => {
  return ({
    feedback: state.Feedback.list.get('elements'),
    viewFields: state.Feedback.list.get('cardVisibleFields'),
    selected: state.Feedback.list.get('selected'),
    people: peopleSelector(state),
    feedbackTypes: feedbackTypesSelector(state),
    feedbackComments: feedbackCommentsSelector(state),
    feedbackCategories: feedbackCategoriesSelector(state),
    feedbackStatusCategories: feedbackStatusCategoriesSelector(state)
  });
})

export class FeedbackCardsContainer extends Component {

  static propTypes = {
    feedback: PropTypes.object.isRequired,
    viewFields: PropTypes.object,
    selected: PropTypes.object.isRequired,
    people: PropTypes.object.isRequired,
    feedbackTypes: PropTypes.object.isRequired,
    feedbackComments: PropTypes.object.isRequired,
    feedbackCategories: PropTypes.object.isRequired,
    feedbackStatusCategories: PropTypes.object,
    toggleSelected: PropTypes.func.isRequired
  };

  render() {
    const { feedback, viewFields, selected, toggleSelected, people, feedbackTypes, feedbackComments,
      feedbackCategories, feedbackStatusCategories } = this.props;

    return (
      <div>
        {feedback.map((element, index) =>
            <FeedbackCard key={index}
                          viewFields={viewFields}
                          feedback={element}
                          selected={selected.includes(element.id)}
                          toggleSelected={toggleSelected}
                          author={people.get(element.person)}
                          feedbackStatusCategory={feedbackStatusCategories.get(element.status_category)}
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