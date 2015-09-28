import React, {Component, PropTypes} from 'react';

export class FeedbackCommentList extends Component {

  static propTypes = {
    elements: PropTypes.array.isRequired
  };

  render() {
    const {elements} = this.props;

    return (
      <div>
        Feedback Comment List
      </div>
    );
  }

}