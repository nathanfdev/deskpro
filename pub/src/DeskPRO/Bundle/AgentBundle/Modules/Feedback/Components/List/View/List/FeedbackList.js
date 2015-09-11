import React from 'react';
import { FeedbackCard } from './FeedbackCard';

export class FeedbackList extends React.Component {

  render() {
    return (
      <div>
        {this.props.elements.map((element, index) => <FeedbackCard key={index} feedback={element} />)}
      </div>
    );
  }

}