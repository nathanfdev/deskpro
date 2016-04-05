import React, {Component, PropTypes} from 'react';
import { Card, CardLine, CardLineLeft, CardLineFull, CardLineItem, CardCheckbox, CardDisc, CardContentText, CardDate, CardUser }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import jQuery from 'jquery';
import { deleteComment, editComment } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import Immutable from 'immutable';

export class FeedbackCommentCard extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    comment: PropTypes.object.isRequired,
    feedback: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    author: PropTypes.object.isRequired,
    selected: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      isEditingNow: false
    };
  }

  toggleEditMode(event) {
    event.preventDefault();
    const { comment, dispatch } = this.props;
    this.setState({ isEditingNow: !this.state.isEditingNow });
    if (this.state.isEditingNow) {
      const newValues = {
        id: comment.get('id'),
        content: this.refs.commentContent.value.trim()
      };
      dispatch(editComment(newValues));
    }
  }

  renderContent() {
    const { comment } = this.props;
    if (this.state.isEditingNow) {
      return (
        <textarea defaultValue={comment.get('content')} style={{width: '100%'}} ref="commentContent"/>
      );
    }
    return (
      <CardContentText>
        <p>{comment.get('content')}</p>
      </CardContentText>
    );
  }

  render() {
    const { dispatch, comment, author, selected, toggleSelected } = this.props;
    const feedback = this.props.feedback || Immutable.fromJS({});
    const containerWidth = jQuery('.dp-list-frame-contents').innerWidth();
    const cardWidth = containerWidth - 15;

    return (
      <Card type="feedback" width={cardWidth} additionalClasses="dpmw--single-card-requires-validation">

        <ValidationLine comment={comment}
                        isEditingNow={this.state.isEditingNow}
                        dispatch={dispatch}
                        toggleEditMode={this.toggleEditMode.bind(this)}/>

        <CardCheckbox selected={selected} onClick={toggleSelected(comment.get('id'))}/>

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
              <CardDate date={comment.get('date_created')} label="Posted"/>
            </CardLineItem>
            <CardDisc/>
            <CardLineItem icon="fa-link">
              <a href="#">{feedback.get('title')}</a>
            </CardLineItem>
          </CardLineLeft>
        </CardLine>
      </Card>
    );
  }
}

export class ValidationLine extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    toggleEditMode: PropTypes.func.isRequired,
    comment: PropTypes.object.isRequired,
    isEditingNow: PropTypes.bool.isRequired
  };

  approveComment(id, event) {
    event.preventDefault();
    const newValues = {
      id: id,
      status: constants.STATUS_VISIBLE,
      is_reviewed: true
    };
    const {dispatch} = this.props;
    dispatch(editComment(newValues));
  }

  deleteComment = (event) => {
    event.preventDefault();
    const {dispatch, comment} = this.props;
    dispatch(deleteComment(comment.get('id')));
  };

  render() {
    const { comment, isEditingNow, toggleEditMode } = this.props;

    return (
      <div className="dpmw--single-card-requires-validation-line">
        <ul>
          <li><span className="validation-mark">Waiting for approval:</span></li>
          <li>
            <a href="#" onClick={this.approveComment.bind(this, comment.get('id'))}>
              <span className="validation-line-icon"><i className="fa fa-check-circle"></i></span> <span
              className="validation-line-title">Approve</span>
            </a>
          </li>
          <li>
            <a href="#" onClick={toggleEditMode.bind(this)}>
              <span className="validation-line-icon edit"><i className="fa fa-edit"></i></span> <span
              className="validation-line-title">
              {isEditingNow ? 'Save' : 'Edit'}
            </span>
            </a>
          </li>
          <li>
            <a href="#" onClick={this.deleteComment}>
              <span className="validation-line-icon trash"><i className="fa fa-trash"></i></span> <span
              className="validation-line-title">Delete</span>
            </a>
          </li>
        </ul>
      </div>
    );
  }
}
