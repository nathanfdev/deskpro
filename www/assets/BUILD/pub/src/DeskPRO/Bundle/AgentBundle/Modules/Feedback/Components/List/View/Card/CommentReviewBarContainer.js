import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { deleteComment, editComment }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

@connect()
export class CommentReviewBarContainer extends Component {

  static propTypes = {
    dispatch:       PropTypes.func.isRequired,
    toggleEditMode: PropTypes.func.isRequired,
    comment:        PropTypes.object.isRequired,
    isEditingNow:   PropTypes.bool
  };

  approveComment = (event) => {
    event.preventDefault();
    const { dispatch, comment } = this.props;
    const newValues = {
      status:      constants.STATUS_VISIBLE,
      is_reviewed: true
    };
    dispatch(editComment(newValues, comment.get('id')));
  };

  deleteComment = (event) => {
    event.preventDefault();
    const { dispatch, comment } = this.props;
    dispatch(deleteComment(comment.get('id')));
  };

  saveComment = (event) => {
    event.preventDefault();
    const { dispatch, comment, toggleEditMode } = this.props;
    const content = toggleEditMode();
    if (content) {
      dispatch(editComment({ content }, comment.get('id')));
    }
  };

  render() {
    const { isEditingNow } = this.props;

    return (
      <div className="dpmw--single-card-requires-validation-line">
        <ul>
          <li><span className="validation-mark">Waiting for approval:</span></li>
          <li>
            <a href="#" onClick={this.approveComment}>
              <span className="validation-line-icon"><i className="fa fa-check-circle"></i></span> <span
              className="validation-line-title">Approve</span>
            </a>
          </li>
          <li>
            <a href="#" onClick={this.saveComment}>
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
