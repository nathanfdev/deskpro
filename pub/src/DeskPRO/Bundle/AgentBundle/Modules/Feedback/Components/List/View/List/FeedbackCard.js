import React, {Component, PropTypes} from 'react';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardDate, CardUser }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/Card';

export class FeedbackCard extends Component {

  static propTypes = {
    feedback: PropTypes.object.isRequired
  };

  render() {
    const {feedback, author, type,  massAction} = this.props;
    return (
      <Card type="feedback">

        <CardCheckbox massAction={massAction}/>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>#{feedback.id}</CardLineItem>
            <CardDisc/>
            <CardLineItem icon="fa-thumbs-up">{feedback.num_ratings}</CardLineItem>
          </CardLineLeft>

          <CardLineRight>
            <CardLineItem>{feedback.status}</CardLineItem>
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardTitle content={feedback.title}/>
        </CardLine>

        <CardLine>
          <CardTitle content={feedback.content}/>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem icon="fa-calendar-plus-o"><CardDate date={feedback.date_created}/></CardLineItem>
            <CardDisc/>
            <CardLineItem>
              <CardUser user={author}/>
            </CardLineItem>
            <CardDisc/>
            <CardLineItem icon="fa-book">{type.title}</CardLineItem>
            <CardDisc/>
            <CardLineItem icon="fa-link"><a href="#">Linked ticket</a></CardLineItem>
          </CardLineLeft>

          <CardLineRight>
            <CardLineItem icon="fa-comment">5</CardLineItem>
          </CardLineRight>
        </CardLine>
      </Card>
    );
  }
}
