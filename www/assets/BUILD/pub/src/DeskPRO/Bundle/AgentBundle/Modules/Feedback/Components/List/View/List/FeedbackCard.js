import React, {Component, PropTypes} from 'react';
import createFragment from 'react-addons-create-fragment';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineFull, CardContentText, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardUser, CardLabel, CardComments }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import jQuery from 'jquery';
import Immutable from 'immutable';

@injectIntl
export class FeedbackCard extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    feedback: PropTypes.object.isRequired,
    viewFields: PropTypes.object,
    selected: PropTypes.bool.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    author: PropTypes.object.isRequired,
    type: PropTypes.object.isRequired,
    feedbackLabels: PropTypes.array,
    feedbackStatusCategory: PropTypes.object
  };

  renderLabels(labels) {
    if (labels.size && this.props.viewFields.includes('labels')) {
      return (
        <CardLineItem>
          <CardDisc/>
          <i className="fa fa-tags"></i> {labels.map((label, index)=> <CardLabel key={index} label={label}/>)}
          <CardDisc/>
        </CardLineItem>
      );
    }
  }

  renderStatus() {
    const { feedback, feedbackStatusCategory } = this.props;
    var realStatus = '';
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

  renderId(id) {
    return (
      <CardLineItem>ID: { id }</CardLineItem>
    );
  }

  renderDate(date) {
    return (
      <CardLineItem><CardDisc/><FormattedRelative value={date}/></CardLineItem>
    );
  }

  renderCategory() {
    const { feedback } = this.props;
    const category = feedback.get('custom_data');
    if (category) {
      return (
        <CardLineItem><CardDisc/>{ category }</CardLineItem>
      );
    }
  }

  renderOptionalFields() {
    const { feedback, viewFields } = this.props;
    const output = {};
    let index = 0;
    if (viewFields.includes('id')) {
      output['key' + index] = this.renderId(feedback.get('id'));
      index++;
    }
    if (viewFields.includes('date_created')) {
      output['key' + index] = this.renderDate(feedback.get('date_created'));
      index++;
    }
    if (viewFields.includes('category')) {
      output['key' + index] = this.renderCategory();
    }
    return (
      <CardLine>
        <CardLineLeft>
          { createFragment(output) }
        </CardLineLeft>
      </CardLine>
    );
  }

  render() {
    const { feedback, author, selected, toggleSelected, feedbackLabels } = this.props;
    const type = this.props.type || Immutable.fromJS({});
    const labels = feedbackLabels ? feedbackLabels : [];
    const containerWidth = jQuery('.dp-list-frame-contents').innerWidth();
    const feedbackMarkWidth = jQuery('.dpw--feedback-card-mark').innerWidth();
    const cardWidth = containerWidth - feedbackMarkWidth - 20;

    return (
      <Card type="feedback" width={cardWidth}>

        <FeedbackCardMark numRatings={feedback.get('num_ratings')}/>

        <CardCheckbox selected={selected} onClick={toggleSelected(feedback.get('id'))}/>

        <CardLine>
          <CardLineLeft>
            <CardTitle content={feedback.get('title')}/>
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
            <CardUser user={author}/>
            <CardDisc/>
            <CardLineItem icon="fa-book">{type.get('title')}</CardLineItem>
          </CardLineLeft>
          { this.renderLabels(labels) }
          <CardLineRight>
            <CardComments commentsCounter={feedback.get('num_comments')}/>
          </CardLineRight>
        </CardLine>

        { this.renderOptionalFields() }
      </Card>
    );
  }

}

export class FeedbackCardMark extends Component {

  static propTypes = {
    numRatings: PropTypes.number.isRequired
  };

  render() {
    const {numRatings} = this.props;

    return (
      <div className="dpw--feedback-card-mark">
        <div className="dpw--feedback-card-mark-counter dpw--feedback-card-mark-thumbs">
          <i className="fa fa-thumbs-up"></i> <span className="feedback-card-mark-count">{numRatings}</span>
        </div>
        <hr/>
        <div className="dpw--feedback-card-mark-counter dpw--feedback-card-mark-stars">
          <i className="fa fa-star"></i> <span className="feedback-card-mark-count">0</span>
        </div>
      </div>
    );
  }
}
