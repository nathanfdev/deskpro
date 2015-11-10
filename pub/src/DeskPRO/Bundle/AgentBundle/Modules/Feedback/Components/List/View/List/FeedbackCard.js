import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineFull, CardContentText, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardUser, CardLabel, CardComments, CardStatusBar }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import jQuery from 'jquery';
import Immutable from 'immutable';
import { defaultCardFields} from '../../../List/ControlBar/FeedbackViewOptions';

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
    feedbackLabels: PropTypes.object,
    feedbackStatus: PropTypes.object.isRequired,
    feedbackCategory: PropTypes.object,
    feedbackComments: PropTypes.object
  };

  renderLabels(labels) {
    if (labels) {
      return (
        <CardLineItem>
          <CardDisc/>
          <i className="fa fa-tags"></i> {labels.map((label, index)=> <CardLabel key={index} label={label}/>)}
          <CardDisc/>
        </CardLineItem>
      );
    }
  }

  renderStatus(status = Immutable.fromJS({})) {
    var realStatus = '';
    if (status.get('title')) {
      realStatus = status.get('title');
    } else if (status.get('status') === 'new') {
      realStatus = 'New';
    } else {
      realStatus = status.get('hidden_status');
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
    const { feedbackCategory } = this.props;
    if (feedbackCategory) {
      return (
        <CardLineItem><CardDisc/>{ feedbackCategory.get('input') }</CardLineItem>
      );
    }
  }

  renderOptionalFields() {
    const { feedback, viewFields } = this.props;
    const optionalFields = (viewFields && viewFields.card) ? viewFields.card : defaultCardFields;
    let output = [];
    if (optionalFields.id.isShown) {
      output = output.concat(this.renderId(feedback.id));
    }
    if (optionalFields.date_created.isShown) {
      output = output.concat(this.renderDate(feedback.date_created));
    }
    if (optionalFields.custom_category.isShown) {
      output = output.concat(this.renderCategory());
    }
    return (
      <CardLine>
        <CardLineLeft>
          { output }
        </CardLineLeft>
      </CardLine>
    );
  }

  render() {
    const { feedback, author, selected, toggleSelected, feedbackLabels, feedbackComments, feedbackStatus } = this.props;
    const type = this.props.type || Immutable.fromJS({});
    const labels = feedbackLabels ? feedbackLabels.get('labels') : false;
    const comments = feedbackComments ? feedbackComments.get('counter') : 0;
    const containerWidth = jQuery('.dp-list-frame-contents').innerWidth();
    const feedbackMarkWidth = jQuery('.dpw--feedback-card-mark').innerWidth();
    const cardWidth = containerWidth - feedbackMarkWidth - 20;
    return (
      <Card type="feedback" width={cardWidth}>

        <FeedbackCardMark numRatings={feedback.num_ratings}/>

        <CardStatusBar align="left" level="5"/>
        <CardStatusBar align="right" level="5"/>

        <CardCheckbox selected={selected} onClick={toggleSelected(feedback.id)}/>

        <CardLine>
          <CardLineLeft>
            <CardTitle content={feedback.title}/>
          </CardLineLeft>

          <CardLineRight>
            {this.renderStatus(feedbackStatus)}
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineFull>
            <CardContentText>
              <p>{feedback.content}</p>
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
            <CardComments commentsCounter={comments}/>
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
