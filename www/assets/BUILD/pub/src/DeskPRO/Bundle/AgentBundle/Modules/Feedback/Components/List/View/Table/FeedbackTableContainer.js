import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { injectIntl } from 'react-intl';
import { applyParams } from '../../../../Actions/FeedbackListActions';
import { currentListOrderBySelector, currentListOrderDirSelector } from '../../../../Selectors/list';
import { collectionSelectorFactory } from '../../../../../../../AppBundle/Modules/RecordsStore';
import { FeedbackTable } from './FeedbackTable';


@connect(state => ({
  feedback:                 collectionSelectorFactory('Feedback', 'feedback')(state),
  orderBy:                  currentListOrderBySelector(state),
  orderDir:                 currentListOrderDirSelector(state),
  feedbackTypes:            collectionSelectorFactory('FeedbackType', 'feedback')(state),
  feedbackComments:         collectionSelectorFactory('FeedbackComment', 'feedback')(state),
  feedbackStatusCategories: collectionSelectorFactory('FeedbackStatusCategory', 'feedback')(state),
  feedbackCategories:       collectionSelectorFactory('FeedbackCategory', 'feedback')(state),
  people:                   collectionSelectorFactory('Person', 'feedback')(state)
}), {
  applyParams
})
@injectIntl
export class FeedbackTableContainer extends Component {
  render() {
    return <FeedbackTable {...this.props} />;
  }
}
