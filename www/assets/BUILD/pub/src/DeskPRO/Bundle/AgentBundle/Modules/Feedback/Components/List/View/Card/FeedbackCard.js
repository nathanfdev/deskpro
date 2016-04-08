import React, { Component, PropTypes } from 'react';
import createFragment from 'react-addons-create-fragment';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import jQuery from 'jquery';
import Immutable from 'immutable';
import {
  Card, CardLine, CardLineLeft, CardLineRight, CardLineFull, CardContentText, CardLineItem,
  CardCheckbox, CardDisc, CardTitle, CardUser, CardLabel, CardComments
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import { FeedbackCardMark } from './FeedbackCardMark';

@injectIntl
export class FeedbackCard extends Component {

  static propTypes = {
    intl:                   intlShape.isRequired,
    feedback:               PropTypes.object.isRequired,
    viewFields:             PropTypes.object,
    selected:               PropTypes.bool.isRequired,
    toggleSelected:         PropTypes.func.isRequired,
    author:                 PropTypes.object.isRequired,
    type:                   PropTypes.object.isRequired,
    feedbackLabels:         PropTypes.object,
    feedbackStatusCategory: PropTypes.object
  };

  renderLabels(labels) {
    const { viewFields } = this.props;
    if (labels.size && viewFields && viewFields.includes('labels')) {
      return (
        <CardLineItem>
          <CardDisc />
          <i className="fa fa-tags"></i> {labels.map((label, index) => <CardLabel key={index} label={label} />)}
          <CardDisc />
        </CardLineItem>
      );
    }
    return null;
  }

  renderStatus() {
    const { feedback, feedbackStatusCategory } = this.props;
    let realStatus = '';
    if (feedback.get('status') === 'new') {
      realStatus = 'New';
    } else if (feedback.get('status') === 'hidden') {
      realStatus = feedback.get('hidden_status');
    } else if (feedbackStatusCategory) {
      realStatus = feedbackStatusCategory.get('title');
    }
    return (
      <CardLineItem>{realStatus}</CardLineItem>
    );
  }

  renderId = (id) => <CardLineItem>ID: { id }</CardLineItem>;

  renderDate = (date) => <CardLineItem><CardDisc /><FormattedRelative value={date} /></CardLineItem>;

  renderCategory = () => {
    const { feedback } = this.props;
    const category = feedback.get('custom_data');
    if (category) {
      return (
        <CardLineItem><CardDisc />{ category }</CardLineItem>
      );
    }
    return null;
  };

  renderOptionalFields = () => {
    const { feedback, viewFields } = this.props;
    const output = {};
    let index    = 0;
    if (undefined !== viewFields) {
      if (viewFields.includes('id')) {
        output[`key${index}`] = this.renderId(feedback.get('id'));
        index++;
      }
      if (viewFields.includes('date_created')) {
        output[`key${index}`] = this.renderDate(feedback.get('date_created'));
        index++;
      }
      if (viewFields.includes('category')) {
        output[`key${index}`] = this.renderCategory();
      }
    }
    return (
      <CardLine>
        <CardLineLeft>
          { createFragment(output) }
        </CardLineLeft>
      </CardLine>
    );
  };

  render() {
    const { feedback, author, selected, toggleSelected } = this.props;
    const type              = this.props.type || Immutable.fromJS({});
    const labels            = this.props.feedbackLabels || Immutable.fromJS({});
    const containerWidth    = jQuery('.dp-list-frame-contents').innerWidth();
    const feedbackMarkWidth = jQuery('.dpw--feedback-card-mark').innerWidth();
    const cardWidth         = containerWidth - feedbackMarkWidth - 20;

    return (
      <Card type="feedback" width={cardWidth}>

        <FeedbackCardMark numRatings={feedback.get('num_ratings')} />

        <CardCheckbox selected={selected} onClick={toggleSelected(feedback.get('id'))} />

        <CardLine>
          <CardLineLeft>
            <CardTitle content={feedback.get('title')} />
          </CardLineLeft>

          <CardLineRight>
            {this.renderStatus()}
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineFull>
            <CardContentText>
              <p>{feedback.get('content')}</p>
            </CardContentText>
          </CardLineFull>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardUser user={author} />
            <CardDisc />
            <CardLineItem icon="fa-book">{type.get('title')}</CardLineItem>
          </CardLineLeft>
          { this.renderLabels(labels) }
          <CardLineRight>
            <CardComments commentsCounter={feedback.get('num_comments')} />
          </CardLineRight>
        </CardLine>

        { this.renderOptionalFields() }
      </Card>
    );
  }

}
