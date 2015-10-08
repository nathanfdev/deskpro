import React, {Component, PropTypes} from 'react';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineFull, CardContentText, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardUser, CardLabel, CardComments, CardStatusBar }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import $ from 'jquery';

export class FeedbackCard extends Component {

  static propTypes = {
    feedback: PropTypes.object.isRequired,
    selected: PropTypes.bool.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    author: PropTypes.object.isRequired,
    type: PropTypes.object.isRequired,
    feedbackLabels: PropTypes.object.isRequired,
    feedbackStatus: PropTypes.string.isRequired,
    feedbackComments: PropTypes.object.isRequired
  };

  renderLabels(labels) {
    if (labels) {
      return (
        <CardLineItem>
          <i className="fa fa-tags"></i> {labels.map((label, index)=> <CardLabel key={index} label={label}/>)}
          <CardDisc/>
        </CardLineItem>
      );
    }
  }

  renderStatus(status = {}) {
    var realStatus = '';
    if (status.get('title')) {
      realStatus = status.get('title');
    } else if (status.get('status') === 'new') {
      realStatus = 'New';
    } else {
      realStatus = status.get('hidden_status');
    }
    return <CardLineItem>{realStatus}</CardLineItem>;
  }

  render() {
    const { feedback, author, type, selected, toggleSelected, feedbackLabels, feedbackComments, feedbackStatus }
      = this.props;
    const labels = feedbackLabels ? feedbackLabels.get('labels') : false;
    const comments = feedbackComments ? feedbackComments.get('counter') : 0;
    const containerWidth = $('.dp-list-frame-contents').innerWidth();
    const feedbackMarkWidth = $('.dpw--feedback-card-mark').innerWidth();
    const cardWidth = containerWidth - feedbackMarkWidth - 20;
    console.log('Width: ', cardWidth);
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
            <CardLineItem icon="fa-book">{type.get('title')}</CardLineItem>
            <CardDisc/>
          </CardLineLeft>
          { this.renderLabels(labels) }
          <CardLineRight>
            <CardComments commentsCounter={comments}/>
          </CardLineRight>
        </CardLine>
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
