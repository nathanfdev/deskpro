import React, { Component, PropTypes } from 'react';
import { FeedbackCard } from './FeedbackCard';
import { connect } from 'react-redux';
import { idsSelector, cardVisibleFieldsSelector } from '../../../../Selectors/list';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  ids: idsSelector(state),
  feedback: collectionSelectorFactory('Feedback', 'feedback')(state),
  viewFields: cardVisibleFieldsSelector(state),
  selected: selectedSelector(state),
  people: collectionSelectorFactory('Person', 'feedback')(state),
  feedbackTypes: collectionSelectorFactory('FeedbackType', 'feedback')(state),
  feedbackStatusCategories: collectionSelectorFactory('FeedbackStatusCategory', 'feedback')(state)
}))
export class FeedbackCardsContainer extends Component {
  static propTypes = {
    ids: PropTypes.array.isRequired,
    feedback: PropTypes.object.isRequired,
    viewFields: PropTypes.object,
    selected: PropTypes.object.isRequired,
    people: PropTypes.object.isRequired,
    feedbackTypes: PropTypes.object.isRequired,
    feedbackStatusCategories: PropTypes.object,
    toggleSelected: PropTypes.func.isRequired
  };

  renderCard(id) {
    const { feedback, viewFields, selected, toggleSelected } = this.props;
    const { people, feedbackTypes, feedbackStatusCategories } = this.props;
    const element = feedback.get(id);

    return (
      <FeedbackCard key={id}
                    viewFields={viewFields}
                    feedback={element}
                    selected={selected.includes(id)}
                    toggleSelected={toggleSelected}
                    author={people.get(element.get('person'))}
                    feedbackStatusCategory={feedbackStatusCategories.get(element.get('status_category'))}
                    feedbackLabels={element.get('labels')}
                    type={feedbackTypes.get(element.get('category'))}/>
    );
  }

  render() {
    const { ids } = this.props;

    return (
      <div>
        {ids.map(id => this.renderCard(id))}
      </div>
    );
  }
}