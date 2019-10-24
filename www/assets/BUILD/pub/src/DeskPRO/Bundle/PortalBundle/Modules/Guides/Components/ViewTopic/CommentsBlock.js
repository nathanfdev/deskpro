import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import moment from 'moment';
import classNames from 'classnames';
import { CommentForm } from '../index';

class Comment extends React.Component {
  static propTypes = {
    comment: PropTypes.object
  };

  render() {
    const { comment } = this.props;

    // style="background: url({{ avatar_url(comment.person) }}) no-repeat; background-size: cover"
    const avatarStyle = {
      background:     `url(${comment.avatar}) no-repeat`,
      backgroundSize: 'cover',
    };
    if (window.currentTheme === 'helpcenter') {
      return (
        <div className="dp-po-comments-wrap">
          <div className="dp-po-comments-item">
            <div className="row">
              <div className="col-sm-9">
                <div className="dp-po-avatar">
                  <img src={comment.avatar} className="dp-po-avatar-image" role="presentation" />
                  <strong>{comment.name}</strong>
                </div>
              </div>
              <div className="col-sm-3">
                <div className="dp-po-comments-extra">
                  <dp-po-comments-time>
                    <i
                      className="dp-po-icon far fa-clock"
                      title={moment(comment.date_created).format('MMMM Do YYYY, h:mm a')}
                    /> {moment(comment.date_created).fromNow()}
                  </dp-po-comments-time>
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
    return (
      <div className="single-comment">
        <div className="comment-info">
          <span className="avatar">
            <span style={avatarStyle} className="agent-avatar agent-avatar-tiny" />
          </span>
          <span className="author">{comment.name}</span>
          <span className="date" title={moment(comment.date_created).format('MMMM Do YYYY, h:mm a')}>
            {moment(comment.date_created).fromNow()}
          </span>
        </div>
        <div className="comment-content">
          <div className="blurb">
            <p>{comment.content}</p>
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

  render() {
    if (!window.topicCommentForm && !this.props.comments.length) {
      return null;
    }

    const comments = this.props.comments.filter(comment => comment.status === 'visible').map(comment =>
      <Comment comment={comment} key={comment.id} />
    );

    const flashMessage = this.props.flashes.length ? (
      <div className="flashes">
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

    if (window.currentTheme === 'helpcenter') {
      return [
        <div className="dp-po-section dp-po-comments" key="comments">
          <div className="dp-po-title">
            <div className="dp-po-title-text dp-po-title-small">
              <FormattedMessage id="portal.general.comments-title" values={{ count: this.props.count }} />
            </div>
          </div>
          <div className="dp-po-block">
            <div className="dp-po-comments-thread">
              {comments}
            </div>
          </div>
        </div>,
        <div className="dp-po-section dp-po-comments no-print" key="comment-form">
          <div className="dp-po-title">
            <h2 className="dp-po-title-text dp-po-title-small"><FormattedMessage id="portal.general.add-comment" /></h2>
          </div>
          <CommentForm onSubmit={this.props.postComment} />
        </div>
      ];
    }
    return (
      <div className="comment-box" id="comments">
        <div className="titled-header">
          <h1><FormattedMessage id="portal.general.comments-title" values={{ count: this.props.count }} /></h1>
        </div>
        {comments}
        {flashMessage}
        <CommentForm onSubmit={this.props.postComment} />
      </div>
    );
  }
}
export default CommentsBlock;
