import React, { PropTypes } from 'react';
import Immutable from 'immutable';

export class BaseTaskCard extends React.Component {

  static propTypes = {
    task: PropTypes.object,
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      expanded: false,
      ticketLink: 'Some Ticket',
      comments: 1
    };
  }

  onToggleExpand = () => {
    this.setState({
      expanded: !this.state.expanded
    });
  };

  isMinimized() {
    const { task } = this.props;
    return !this.state.expanded && task.get('is_done');
  }
}
