import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import {
  Card,
  CardLine,
  CardLineLeft,
  CardLineRight,
  CardLineFull,
  CardContentText,
  CardLineItem,
  CardCheckbox,
  CardDisc,
  CardTitle,
  CardUser,
  CardLabel
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import jQuery from 'jquery';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

@injectIntl
export class ContentCard extends Component {

  static propTypes = {
    intl:               intlShape.isRequired,
    element:            PropTypes.object.isRequired,
    author:             PropTypes.object.isRequired,
    lastRevisionAuthor: PropTypes.object.isRequired,
    selected:           PropTypes.bool.isRequired,
    toggleSelected:     PropTypes.func.isRequired
  };

  renderLabels = () => {
    const labels = this.props.element.get('labels');
    if (labels && labels.size) {
      return (
        <CardLine>
          <CardLineItem>
            <i className="fa fa-tags" /> {labels.map((label, index) => <CardLabel key={index} label={label} />)}
          </CardLineItem>
        </CardLine>
      );
    }
    return null;
  };

  render = () => {
    const { element, author, lastRevisionAuthor, toggleSelected, selected } = this.props;
    const containerWidth    = jQuery('.dp-list-frame-contents').innerWidth();
    const feedbackMarkWidth = jQuery('.dpw--feedback-card-mark').innerWidth();
    const cardWidth         = containerWidth - feedbackMarkWidth - 20;

    return (
      <Card type="article" width={cardWidth}>

        <ArticleCardMark numRatings={element.get('num_ratings')} />

        <CardCheckbox selected={selected} onClick={toggleSelected} />

        <CardLine>
          <CardLineLeft>
            <CardTitle content={element.get('title')} />
          </CardLineLeft>
          <CardLineRight>
            <CardUser user={author} />
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineFull>
            <CardContentText>
              <p><SlicedString string={element.get('content')} length={255} /></p>
            </CardContentText>
          </CardLineFull>
        </CardLine>

        {this.renderLabels()}

        <CardLine>
          <CardLineLeft>
            <CardLineItem>{element.get('status')}</CardLineItem>
            {element.get('revisions').size > 0
            && <CardLineItem>
              <CardDisc />
              <CardUser user={lastRevisionAuthor} />
              <CardDisc />
              <FormattedRelative value={element.get('date_updated')} />
              <CardDisc />
            </CardLineItem>}
          </CardLineLeft>
          <CardLineRight>
            <CardLineItem icon="fa-thumbs-up">{element.get('vote_stats').get('up')}</CardLineItem>
            <CardLineItem icon="fa-thumbs-down">{element.get('vote_stats').get('down')}</CardLineItem>
            <CardLineItem icon="fa-comments-o">{element.get('num_comments')}</CardLineItem>
            <CardLineItem icon="fa-eye">{element.get('view_count')}</CardLineItem>
          </CardLineRight>
        </CardLine>
      </Card>
    );
  }

}

export class ArticleCardMark extends Component {

  static propTypes = {
    numRatings: PropTypes.number.isRequired
  };

  render = () => {
    const { numRatings } = this.props;

    return (
      <div className="dpw--feedback-card-mark">
        <div className="dpw--feedback-card-mark-counter dpw--feedback-card-mark-thumbs">
          <i className="fa fa-thumbs-up" /> <span className="feedback-card-mark-count">{numRatings}</span>
        </div>
        <hr />
        <div className="dpw--feedback-card-mark-counter dpw--feedback-card-mark-stars">
          <i className="fa fa-star" /> <span className="feedback-card-mark-count">0</span>
        </div>
      </div>
    );
  }
}
