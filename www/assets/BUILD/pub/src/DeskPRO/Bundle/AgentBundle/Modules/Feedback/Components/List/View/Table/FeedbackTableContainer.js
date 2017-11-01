import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { injectIntl } from 'react-intl';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { applyParams } from '../../../../Actions/FeedbackListActions';
import {
  currentListOrderBySelector, currentListOrderDirSelector, fieldsSelector, idsSelector
} from '../../../../Selectors/list';
import { collectionSelectorFactory } from '../../../../../../../AppBundle/Modules/RecordsStore';
import { FeedbackTable } from './FeedbackTable';

@connect(
  state => ({
    feedback:                 collectionSelectorFactory('Feedback', 'feedback')(state),
    fields:                   fieldsSelector(state),
    elements:                 idsSelector(state),
    orderBy:                  currentListOrderBySelector(state),
    orderDir:                 currentListOrderDirSelector(state),
    feedbackTypes:            collectionSelectorFactory('FeedbackType', 'feedback')(state),
    feedbackComments:         collectionSelectorFactory('FeedbackComment', 'feedback')(state),
    feedbackStatusCategories: collectionSelectorFactory('FeedbackStatusCategory', 'feedback')(state),
    feedbackCategories:       collectionSelectorFactory('FeedbackCategory', 'feedback')(state),
    people:                   collectionSelectorFactory('Person', 'feedback')(state)
  }),
  { applyParams }
)

@injectIntl
export class FeedbackTableContainer extends Component {

  static propTypes = {
    feedback:                 PropTypes.object.isRequired,
    fields:                   PropTypes.object.isRequired,
    people:                   PropTypes.object.isRequired,
    feedbackTypes:            PropTypes.object.isRequired,
    feedbackStatusCategories: PropTypes.object,
    elements:                 PropTypes.object.isRequired
  };

  render() {
    return <FeedbackTable {...this.props} fields={this.props.fields.get('feedback').get(constants.VIEW_MODE_TABLE)} />;
  }
}
