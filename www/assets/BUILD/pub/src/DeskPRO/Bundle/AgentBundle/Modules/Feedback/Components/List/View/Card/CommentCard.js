import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Immutable from 'immutable';
import jQuery from 'jquery';
import {
  Card, CardLine, CardLineLeft, CardLineFull, CardLineItem, CardCheckbox,
  CardDisc, CardContentText, CardDate, CardUser
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import { CommentReviewBarContainer } from './CommentReviewBarContainer';

export class CommentCard extends Component {

  static propTypes = {
    comment:        PropTypes.object.isRequired,
    feedback:       PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    openModal:      PropTypes.func.isRequired,
    author:         PropTypes.object.isRequired,
    selected:       PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      isEditingNow: false
    };
  }

  toggleEditMode = () => {
    this.setState({ isEditingNow: !this.state.isEditingNow });
    if (this.state.isEditingNow) {
      return this.refs.commentContent.value.trim();
    }
    return null;
  };

  renderContent() {
    const { comment } = this.props;
    if (this.state.isEditingNow) {
      return (
        <textarea defaultValue={comment.get('content')} style={{ width: '100%' }} ref="commentContent" />
      );
    }
    return (
      <CardContentText>
        <p>{comment.get('content')}</p>
      </CardContentText>
    );
  }

  render() {
    const { comment, author, selected, toggleSelected, openModal } = this.props;

    const feedback       = this.props.feedback || Immutable.fromJS({});
    const containerWidth = jQuery('.dp-list-frame-contents').innerWidth();
    const cardWidth      = containerWidth - 15;

    return (
      <Card type="feedback" width={cardWidth} additionalClasses="dpmw--single-card-requires-validation">

        <CommentReviewBarContainer
          comment={comment}
          openModal={openModal}
          toggleEditMode={this.toggleEditMode}
          isEditingNow={this.state.isEditingNow}
        />

        <CardCheckbox selected={selected} onClick={toggleSelected(comment.get('id'))} />

        <CardLine>
          <CardLineLeft>
            <CardLineItem>
              <CardUser user={author} />
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
              <CardDate date={comment.get('date_created')} label="Posted" />
            </CardLineItem>
            <CardDisc />
            <CardLineItem icon="fa-link">
              <a href="#">{feedback.get('title')}</a>
            </CardLineItem>
          </CardLineLeft>
        </CardLine>
      </Card>
    );
  }
}
