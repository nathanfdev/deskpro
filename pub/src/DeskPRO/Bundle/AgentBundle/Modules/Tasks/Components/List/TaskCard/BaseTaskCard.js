import React, { PropTypes } from 'react';

export class BaseTaskCard extends React.Component {

  static propTypes = {
    task: PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      expanded: false,
      ticketLink: 'Some Ticket',
      comments: 1
    };
  }

  onTitleChange = value => {
    this.setState({
      title: value
    });
  };

  onChangeDate = value => {
    this.setState({
      dateDue: value
    });
  };

  onToggleExpand = () => {
    this.setState({
      expanded: !this.state.expanded
    });
  };

  isMinimized() {
    return !this.state.expanded && this.props.task.get('is_done');
  }
}
