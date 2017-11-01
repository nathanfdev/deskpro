import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import jQuery from 'jquery';
import { fromJS } from 'immutable';
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
    feedbackLabels:         PropTypes.object.isRequired,
    feedbackStatusCategory: PropTypes.object,
    fields:                 PropTypes.object.isRequired
  };

  renderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const { feedback, feedbackLabels } = this.props;
    const fieldId = field.get('id');

    switch (fieldId) {

      case 'date_created':
        return (
          <CardLineItem key={fieldId}>
            <CardDisc />
            <FormattedRelative value={feedback.get(fieldId)} />
          </CardLineItem>
        );

      case 'category':
        if (!feedback.get(fieldId)) {
          return null;
        }
        return <CardLineItem key={fieldId}><CardDisc />{feedback.get(fieldId)}</CardLineItem>;

      case 'labels':
        if (!feedbackLabels.size) {
          return null;
        }
        return (
          <CardLineItem key={fieldId}>
            <CardDisc />
            <i className="fa fa-tags" /> {feedbackLabels.map((label, index) => <CardLabel key={index} label={label} />)}
            <CardDisc />
          </CardLineItem>
        );

      default:
        return null;
    }
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

  render() {
    const { feedback, author, selected, toggleSelected, fields } = this.props;
    const type              = this.props.type || fromJS({});
    const containerWidth    = jQuery('.dp-list-frame-contents').innerWidth();
    const feedbackMarkWidth = jQuery('.dpw--feedback-card-mark').innerWidth();
    const cardWidth         = containerWidth - feedbackMarkWidth - 20;

    let labelsField;
    this.fields = [];
    fields.forEach(field => {
      if (field.get('id') === 'labels') {
        labelsField = field;
      } else {
        this.fields.push(field);
      }
    });

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
          {labelsField && this.renderField(labelsField)}
          <CardLineRight>
            <CardComments commentsCounter={feedback.get('num_comments')} />
          </CardLineRight>
        </CardLine>

        {this.fields.map(field => this.renderField(field))}
      </Card>
    );
  }

}
