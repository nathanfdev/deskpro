import React, {Component, PropTypes} from 'react';
import { FeedbackCard } from './FeedbackCard';

export class FeedbackList extends Component {

  static propTypes = {
    elements: PropTypes.array.isRequired
  };

  render() {
    const {elements} = this.props;

    return (
      <div>
        {elements.map((element, index) => <FeedbackCard key={index} feedback={element}/>)}
      </div>
    );
  }

}