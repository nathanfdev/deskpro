import React, {Component, PropTypes} from 'react';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineFull, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardContentText, CardDate, CardUser, CardStatusBar }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import $ from 'jquery';

export class FeedbackCommentCard extends Component {

  static propTypes = {
    comment: PropTypes.object.isRequired,
    feedback: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    author: PropTypes.object.isRequired,
    email: PropTypes.string.isRequired,
    selected: PropTypes.array.isRequired
  };

  render() {
    const { comment, author, email, feedback, selected, toggleSelected } = this.props;
    const containerWidth = $('.dp-list-frame-contents').innerWidth();
    const cardWidth = containerWidth - 15;
    return (
      <Card type="feedback" width={cardWidth} additionalClasses="dpmw--single-card-requires-validation">
        <div className="dpmw--single-card-requires-validation-line">
          <ul>
            <li><span className="validation-mark">Waiting for approval:</span></li>
            <li><a href="#"><span className="validation-line-icon"><i className="fa fa-check-circle"></i></span> <span
              className="validation-line-title">Approve</span></a></li>
            <li><a href="#"><span className="validation-line-icon edit"><i className="fa fa-edit"></i></span> <span
              className="validation-line-title">Edit</span></a></li>
            <li><a href="#"><span className="validation-line-icon trash"><i className="fa fa-trash"></i></span> <span
              className="validation-line-title">Delete</span></a></li>
          </ul>
        </div>
        <CardStatusBar align="left" level="5"/>
        <CardStatusBar align="right" level="5"/>
        <CardCheckbox selected={selected} onClick={toggleSelected(comment.id)}/>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>
              <CardUser user={author}/>
            </CardLineItem>
          </CardLineLeft>
        </CardLine>

        <CardLine>
          <CardLineFull>
            <CardContentText>
              <p>{comment.content}</p>
            </CardContentText>
          </CardLineFull>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem icon="fa-comments-o">
              <CardDate date={comment.date_created} label="Posted" />
            </CardLineItem>
            <CardDisc/>
            <CardLineItem>
              <span className="dpwd--card-line-item">
                <i className="fa fa-link"></i> <a href="#">{feedback.get('title')}</a>
              </span>
            </CardLineItem>
          </CardLineLeft>
        </CardLine>
      </Card>
    );
  }
}