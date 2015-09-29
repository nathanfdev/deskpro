import React, {Component, PropTypes} from 'react';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardDate, CardUser }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/Card';

export class FeedbackCard extends Component {

  static propTypes = {
    feedback: PropTypes.object.isRequired
  };

  render() {
    const {feedback, author, type,  massAction, feedbackLabels} = this.props;
    //const labels = feedbackLabels.filter((obj)=>obj.id === feedback.id);
    const labels = feedbackLabels ? feedbackLabels.labels : [];
    return (
      <Card type="feedback">

        <CardCheckbox massAction={massAction}/>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>#{feedback.id}</CardLineItem>
            <CardDisc/>
            <CardLineItem icon="fa-thumbs-up">{feedback.num_ratings}</CardLineItem>
            <CardDisc/>
            <CardTitle content={feedback.title}/>
          </CardLineLeft>

          <CardLineRight>
            <CardLineItem>{feedback.status}</CardLineItem>
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem icon="fa-book">{type.title}</CardLineItem>
            <CardDisc/>
            {labels.map((label, index)=><CardLineItem key={index}>{label};&nbsp;</CardLineItem>)}
            <CardDisc/>
            <CardLineItem>
              <CardUser user={author}/>
            </CardLineItem>
          </CardLineLeft>

          <CardLineRight>
            <CardLineItem icon="fa-comment">5</CardLineItem>
          </CardLineRight>
        </CardLine>
      </Card>
    );
  }
}
