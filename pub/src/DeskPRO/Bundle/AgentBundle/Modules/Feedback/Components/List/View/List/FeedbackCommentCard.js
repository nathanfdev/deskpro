import React, {Component, PropTypes} from 'react';
import { Card, CardLine, CardLineLeft, CardLineFull, CardLineItem, CardCheckbox, CardDisc, CardContentText, CardDate, CardUser, CardStatusBar }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import $ from 'jquery';
import { deleteComment, editComment } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class FeedbackCommentCard extends Component {

  static propTypes = {
    dispatch: PropTypes.object.isRequired,
    comment: PropTypes.object.isRequired,
    feedback: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    author: PropTypes.object.isRequired,
    email: PropTypes.string.isRequired,
    selected: PropTypes.array.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      isEditingNow: false
    };
  }

  deleteComment(id, event) {
    event.preventDefault();
    const {dispatch} = this.props;
    dispatch(deleteComment(id));
  }


  toggleEditMode(event) {
    event.preventDefault();
    this.setState({isEditingNow: !this.state.isEditingNow});
  }

  approveComment(commentId) {
    const newValues = {
      commentId: commentId,
      status: constants.STATUS_VISIBLE,
      is_reviewed: true
    };
    const {dispatch} = this.props;
    dispatch(editComment(newValues));
  }

  renderContent() {
    const { comment } = this.props;
    if (this.state.isEditingNow) {
      return (
        <textarea value={comment.content} style={{width: '100%'}}/>
      );
    }
    return (
      <CardContentText>
        <p>{comment.content}</p>
      </CardContentText>
    );
  }

  render() {
    const { comment, author, feedback, selected, toggleSelected } = this.props;
    const containerWidth = $('.dp-list-frame-contents').innerWidth();
    const cardWidth = containerWidth - 15;
    return (
      <Card type="feedback" width={cardWidth} additionalClasses="dpmw--single-card-requires-validation">
        <div className="dpmw--single-card-requires-validation-line">
          <ul>
            <li><span className="validation-mark">Waiting for approval:</span></li>
            <li>
              <a href="#" onClick={this.approveComment.bind(this, comment.id)}>
                <span className="validation-line-icon"><i className="fa fa-check-circle"></i></span> <span
                className="validation-line-title">Approve</span>
              </a>
            </li>
            <li>
              <a href="#" onClick={this.toggleEditMode.bind(this)}>
                <span className="validation-line-icon edit"><i className="fa fa-edit"></i></span> <span
                className="validation-line-title">{this.state.isEditingNow ? 'Save' : 'Edit'}</span>
              </a>
            </li>
            <li>
              <a href="#" onClick={this.deleteComment.bind(this, comment.id)}>
                <span className="validation-line-icon trash"><i className="fa fa-trash"></i></span> <span
                className="validation-line-title">Delete</span>
              </a>
            </li>
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
            {this.renderContent(comment)}
          </CardLineFull>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem icon="fa-comments-o">
              <CardDate date={comment.date_created} label="Posted"/>
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