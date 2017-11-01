import PropTypes from 'prop-types';
// @flow
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { collectionSelectorFactory } from '../../../../../../../AppBundle/Modules/RecordsStore';
import { fieldsSelector, idsSelector } from '../../../../Selectors/list';
import { FeedbackCards } from './FeedbackCards';

@connect(state => ({
  selected:                 selectedSelector(state),
  feedback:                 collectionSelectorFactory('Feedback', 'feedback')(state),
  fields:                   fieldsSelector(state),
  elements:                 idsSelector(state),
  people:                   collectionSelectorFactory('Person', 'feedback')(state),
  feedbackTypes:            collectionSelectorFactory('FeedbackType', 'feedback')(state),
  feedbackStatusCategories: collectionSelectorFactory('FeedbackStatusCategory', 'feedback')(state)
}))

export class FeedbackCardsContainer extends Component {

  static propTypes = {
    feedback:                 PropTypes.object.isRequired,
    fields:                   PropTypes.object.isRequired,
    selected:                 PropTypes.object.isRequired,
    people:                   PropTypes.object.isRequired,
    feedbackTypes:            PropTypes.object.isRequired,
    feedbackStatusCategories: PropTypes.object,
    toggleSelected:           PropTypes.func.isRequired,
    elements:                 PropTypes.array.isRequired
  };

  render() {
    const { fields } = this.props;
    return <FeedbackCards {...this.props} fields={fields.get('feedback').get(constants.VIEW_MODE_CARD)} />;
  }
}
