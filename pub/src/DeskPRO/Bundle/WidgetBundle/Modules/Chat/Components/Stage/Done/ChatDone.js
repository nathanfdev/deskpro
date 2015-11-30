import React from 'react';
import { RatingComplete } from './RatingComplete';
import { ExtraRatingInfo } from './ExtraRatingInfo';
import { RateAgent } from './RateAgent';
import { TranscriptSent } from './TranscriptSent';
import { TranscriptForm } from './TranscriptForm';

export class ChatDone extends React.Component {

  render() {
    return (
      <div>
        <RatingComplete />
        <TranscriptSent />
        <TranscriptForm />
      </div>
    );
  }
}
