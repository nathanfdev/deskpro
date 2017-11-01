import PropTypes from 'prop-types';
// @flow
import React from 'react';

export const FeedbackCardMark = ({ numRatings }:{numRatings:number}) =>
  <div className="dpw--feedback-card-mark">
    <div className="dpw--feedback-card-mark-counter dpw--feedback-card-mark-thumbs">
      <i className="fa fa-thumbs-up" /> <span className="feedback-card-mark-count">{numRatings}</span>
    </div>
    <hr />
    <div className="dpw--feedback-card-mark-counter dpw--feedback-card-mark-stars">
      <i className="fa fa-star" /> <span className="feedback-card-mark-count">0</span>
    </div>
  </div>;

FeedbackCardMark.propTypes = {
  numRatings: PropTypes.number.isRequired
};
