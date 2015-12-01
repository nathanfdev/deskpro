import React from 'react';
import { RateAgent } from './RateAgent';
import { RatingComplete } from './RatingComplete';
import { ExtraRatingInfo } from './ExtraRatingInfo';

export class AgentFeedback extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      dialog: null
    };
  }

  onClickHelpful = () => {
    this.setState({
      dialog: 'helpful'
    });
  };

  onClickNotHelpful = () => {
    this.setState({
      dialog: 'notHelpful'
    });
  };

  render() {
    switch (this.state.dialog) {
      case 'helpful':
        return <RatingComplete />;
      case 'notHelpful':
        return <ExtraRatingInfo />;
      default:
        return (
          <RateAgent onClickHelpful={this.onClickHelpful}
                     onClickNotHelpful={this.onClickNotHelpful} />
        );
    }
  }
}
