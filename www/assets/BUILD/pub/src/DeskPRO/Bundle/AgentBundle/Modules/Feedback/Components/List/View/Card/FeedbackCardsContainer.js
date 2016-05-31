import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { collectionSelectorFactory } from '../../../../../../../AppBundle/Modules/RecordsStore';
import { FeedbackCards } from './FeedbackCards';

@connect(state => ({
  selected:                 selectedSelector(state),
  feedback:                 collectionSelectorFactory('Feedback', 'feedback')(state),
  people:                   collectionSelectorFactory('Person', 'feedback')(state),
  feedbackTypes:            collectionSelectorFactory('FeedbackType', 'feedback')(state),
  feedbackStatusCategories: collectionSelectorFactory('FeedbackStatusCategory', 'feedback')(state)
}))

export class FeedbackCardsContainer extends Component {
  render() {
    return <FeedbackCards {...this.props} />;
  }
}
