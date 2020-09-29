import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { FormattedMessage } from 'react-intl';
import moment from 'moment';
import { CommentForm } from '../index';

class Comment extends React.Component {
  static propTypes = {
    comment: PropTypes.object
  };

  renderAvatar() {
    const { comment } = this.props;

    if (comment.avatar) {
      return (
        <img src={comment.avatar} className="dp-po-avatar-image" role="presentation" />
      );
    }
    return (
      <span className="dp-po-avatar-name" aria-hidden="true">{comment.initials}</span>
    );
  }

  render() {
    const { comment } = this.props;

    return (
      <div className="dp-po-comments-wrap">
        <div className="dp-po-comments-item">
          <div className="row">
            <div className="col-sm-9">
              <div className="dp-po-avatar">
                {this.renderAvatar()}
                <span>{comment.name}</span>
              </div>
            </div>
            <div className="col-sm-3">
              <div className="dp-po-comments-extras">
                <div className="dp-po-comments-time">
                  <i
                    className="dp-po-icon far fa-clock"
                    title={moment(comment.date_created).format('MMMM Do YYYY, h:mm a')}
                  /> {moment(comment.date_created).fromNow()}
                </div>
              </div>
            </div>
          </div>
          <div className="dp-po-comments-desc">
            <div className="dp-po-comments-content">
              {comment.content}
            </div>
          </div>
        </div>
      </div>
    );
  }
}

class CommentsBlock extends React.Component {
  static propTypes = {
    comments:    PropTypes.array,
    flashes:     PropTypes.array,
    count:       PropTypes.number,
    postComment: PropTypes.func,
  };

  static defaultProps = {
    comments: [],
    flashes:  [],
  };

  renderComments() {
    if (this.props.comments.length === 0) {
      return null;
    }
    const comments = this.props.comments.filter(comment => comment.status === 'visible').map(comment =>
      <Comment comment={comment} key={comment.id} />
    );

    return (
      <div className="dp-po-section dp-po-comments">
        <div className="dp-po-title">
          <div className="dp-po-title-text dp-po-title-small">
            <FormattedMessage id="helpcenter.general.comments_title" values={{ count: this.props.count }} />
          </div>
        </div>
        <div className="dp-po-block">
          <div className="dp-po-comments-thread">
            {comments}
          </div>
        </div>
      </div>
    );
  }

  render() {
    if (!window.topicCommentForm && !this.props.comments.length) {
      return null;
    }

    const flashMessage = this.props.flashes.length ? (
      <div className="flashes dp-po-message-bar">
        {this.props.flashes.map((flash, index) => (
          <div
            className={classNames('flash', `flash-${flash.type}`)}
            key={`flash-${index}`}
          >
            {flash.message}
          </div>
          )
        )}
      </div>
    ) : null;

    return (
      <Fragment>
        {this.renderComments()}
        <div className="dp-po-section dp-po-comments no-print">
          <div className="dp-po-title">
            <h2 className="dp-po-title-text dp-po-title-small"><FormattedMessage id="helpcenter.general.add_comment" /></h2>
          </div>
          {flashMessage}
          <CommentForm onSubmit={this.props.postComment} />
        </div>
      </Fragment>
    );
  }
}
export default CommentsBlock;
