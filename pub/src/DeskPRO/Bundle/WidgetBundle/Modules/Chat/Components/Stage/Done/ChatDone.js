import React from 'react';
import { RatingComplete } from './RatingComplete';
import { ExtraRatingInfo } from './ExtraRatingInfo';

export class ChatDone extends React.Component {

  render() {
    return (
      <div>
        <RatingComplete />
        <ExtraRatingInfo />
      </div>
    );
  }
}
