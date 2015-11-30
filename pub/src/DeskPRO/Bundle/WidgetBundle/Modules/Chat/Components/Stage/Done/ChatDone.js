import React from 'react';
import { RatingComplete } from './RatingComplete';
import { ExtraRatingInfo } from './ExtraRatingInfo';
import { RateAgent } from './RateAgent';

export class ChatDone extends React.Component {

  render() {
    return (
      <div>
        <RatingComplete />
        <ExtraRatingInfo />
        <RateAgent />
      </div>
    );
  }
}
