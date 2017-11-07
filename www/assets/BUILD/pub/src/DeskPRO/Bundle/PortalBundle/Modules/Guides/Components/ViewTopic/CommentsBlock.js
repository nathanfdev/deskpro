import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';
import classNames from 'classnames';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
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

    const comments = this.props.comments.map(comment =>
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

    return (
      <div className="comment-box" id="comments">
        <div className="titled-header">
          <h1>{portalPhrases.get('portal.general.comments-title', { '{count}': this.props.count }) }</h1>
        </div>
        {comments}
        {flashMessage}
        <CommentForm onSubmit={this.props.postComment} />
      </div>
    );
  }
}
export default CommentsBlock;
