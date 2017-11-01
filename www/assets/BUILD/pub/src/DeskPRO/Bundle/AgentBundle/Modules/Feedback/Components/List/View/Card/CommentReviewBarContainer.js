import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { editComment } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';

@connect()
export class CommentReviewBarContainer extends Component {

  static propTypes = {
    dispatch:       PropTypes.func.isRequired,
    toggleEditMode: PropTypes.func.isRequired,
    openModal:      PropTypes.func.isRequired,
    comment:        PropTypes.object.isRequired,
    isEditingNow:   PropTypes.bool
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
    const { isEditingNow, openModal, comment } = this.props;
    const id             = comment.get('id');
    const approveComment = openModal.bind(this, 'approve', id);
    const deleteComment  = openModal.bind(this, 'delete', id);

    return (
      <div className="dpmw--single-card-requires-validation-line">
        <ul>
          <li><span className="validation-mark">Waiting for approval:</span></li>
          <li>
            <a href="#" onClick={approveComment}>
              <span className="validation-line-icon"><i className="fa fa-check-circle" /></span>
              <span className="validation-line-title">Approve</span>
            </a>
          </li>
          <li>
            <a href="#" onClick={this.saveComment}>
              <span className="validation-line-icon edit"><i className="fa fa-edit" /></span>
              <span className="validation-line-title">{isEditingNow ? 'Save' : 'Edit'}</span>
            </a>
          </li>
          <li>
            <a href="#" onClick={deleteComment}>
              <span className="validation-line-icon trash"><i className="fa fa-trash" /></span>
              <span className="validation-line-title">Delete</span>
            </a>
          </li>
        </ul>
      </div>
    );
  }
}
