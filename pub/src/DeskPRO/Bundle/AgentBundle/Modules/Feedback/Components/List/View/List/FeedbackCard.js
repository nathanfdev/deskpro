import React, {Component, PropTypes} from 'react';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardUser }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/Card';

export class FeedbackCard extends Component {

  static propTypes = {
    feedback: PropTypes.object.isRequired,
    selected: PropTypes.bool.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    author: PropTypes.object.isRequired,
    type: PropTypes.object.isRequired,
    feedbackLabels: PropTypes.object.isRequired
  };

  renderLabels(labels) {
    if (labels) {
      return (
        <CardLineLeft>
          {labels.map((label, index)=><CardLineItem key={index}>{label};&nbsp;</CardLineItem>)}
          <CardDisc/>
        </CardLineLeft>
      );
    }
  }

  renderStatus(status = {}) {
    var realStatus = '';
    if (status.title) {
      realStatus = status.title;
    }
    else if (status.status === 'new') {
      realStatus = 'New';
    }
    else {
      realStatus = status.hidden_status;
    }
    return <CardLineItem>{realStatus}</CardLineItem>;
  }

  render() {
    const { feedback, author, type, selected, toggleSelected, feedbackLabels, feedbackComments, feedbackStatus }
            = this.props;
    const labels   = feedbackLabels ? feedbackLabels.labels : false;
    const comments = feedbackComments ? feedbackComments.counter : 0;

    return (
      <Card type="feedback">

        <CardCheckbox selected={selected} onClick={toggleSelected(feedback.id)}/>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>#{feedback.id}</CardLineItem>
            <CardDisc/>
            <CardLineItem icon="fa-thumbs-up">{feedback.num_ratings}</CardLineItem>
            <CardDisc/>
            <CardTitle content={feedback.title}/>
          </CardLineLeft>

          <CardLineRight>
            {this.renderStatus(feedbackStatus)}
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem icon="fa-book">{type.title}</CardLineItem>
            <CardDisc/>
          </CardLineLeft>
          { this.renderLabels(labels) }
          <CardLineLeft>
            <CardLineItem>
              <CardUser user={author}/>
            </CardLineItem>
          </CardLineLeft>

          <CardLineRight>
            <CardLineItem icon="fa-comment">{comments}</CardLineItem>
          </CardLineRight>
        </CardLine>
      </Card>
    );
  }

}
