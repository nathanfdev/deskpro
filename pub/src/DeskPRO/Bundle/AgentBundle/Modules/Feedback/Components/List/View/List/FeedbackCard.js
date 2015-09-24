import React, {Component, PropTypes} from 'react';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardDate } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/Card';

export class FeedbackCard extends Component {

  static propTypes = {
    feedback: PropTypes.object.isRequired
  };

  render() {
    const {feedback} = this.props;
    return (
      <Card type="feedback">

        <CardCheckbox/>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>#{feedback.id}</CardLineItem>
            <CardDisc/>
            <CardLineItem>{feedback.num_ratings}</CardLineItem>
            <CardDisc/>
            <CardTitle content={feedback.title}/>
          </CardLineLeft>

          <CardLineRight>
            <CardLineItem>{feedback.status}</CardLineItem>
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardTitle content={feedback.content}/>
          </CardLineLeft>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>
              <i className="fa fa-calendar-o"></i> <CardDate label="Created" date={feedback.date_created}/>
            </CardLineItem>
            <CardDisc/>
            <div className="dpwd--card-assigned">
              <span className="dpw--avatar-face"
                    style={{backgroundImage: 'url(../img/avatars/avatar6.png)'}}>Sender</span>
            </div>
            <CardDisc/>
            <CardLineItem><i className="fa fa-book"></i> {feedback.category}</CardLineItem>
            <CardDisc/>
            <CardLineItem><i className="fa fa-link"></i> <a href="#">Linked ticket</a></CardLineItem>
          </CardLineLeft>

          <CardLineRight>
            <CardLineItem>5 <i className="fa fa-comment"></i></CardLineItem>
          </CardLineRight>
        </CardLine>
      </Card>
    );
  }
}
