import React, {Component, PropTypes} from 'react';
import { FeedbackCard } from './FeedbackCard';
import { connect } from 'react-redux';
import { feedbackSelector, peopleSelector, feedbackTypesSelector, feedbackCommentsSelector, feedbackCategoriesSelector, feedbackStatusCategoriesSelector }
  from '../../../../Selectors/recordStores';

@connect(state => {
  return ({
    ids: state.Feedback.list.get('elements'),
    feedback: feedbackSelector(state),
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
    ids: PropTypes.array.isRequired,
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

  renderCard(id) {
    const { feedback, viewFields, selected, toggleSelected, people, feedbackTypes, feedbackComments,
      feedbackCategories, feedbackStatusCategories } = this.props;
    const element = feedback.get(id);

    return (
      <FeedbackCard key={id}
                    viewFields={viewFields}
                    feedback={element}
                    selected={selected.includes(id)}
                    toggleSelected={toggleSelected}
                    author={people.get(element.get('person'))}
                    feedbackStatusCategory={feedbackStatusCategories.get(element.get('status_category'))}
                    feedbackCategories={feedbackCategories}
                    feedbackComments={feedbackComments.get(id)}
                    feedbackLabels={element.get('labels')}
                    type={feedbackTypes.get(element.get('category'))}/>
    );
  }

  render() {
    const { ids } = this.props;

    return (
      <div>
        {ids.map(id =>this.renderCard(id))}
      </div>
    );
  }
}