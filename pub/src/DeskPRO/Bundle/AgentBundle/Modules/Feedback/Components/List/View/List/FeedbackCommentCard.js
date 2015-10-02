import React, {Component, PropTypes} from 'react';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardDate, CardUser }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/Card';

export class FeedbackCommentCard extends Component {

  static propTypes = {
    comment: PropTypes.object.isRequired,
    feedback: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    author: PropTypes.object.isRequired,
    selected: PropTypes.array.isRequired
  };

  render() {
    const { comment, author, feedback, selected, toggleSelected } = this.props;

    return (
      <Card type="feedback">

        <CardCheckbox selected={selected} onClick={toggleSelected(comment.id)}/>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>#{comment.id}</CardLineItem>
            <CardDisc/>
            <CardTitle content={comment.content}/>
          </CardLineLeft>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>
              <CardUser user={author}/>
            </CardLineItem>
          </CardLineLeft>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>
              <CardDate date={comment.date_created}/>
            </CardLineItem>
          <CardDisc/>
            <CardLineItem>{feedback.title}</CardLineItem>
          </CardLineLeft>
        </CardLine>
      </Card>
    );
  }
}