import React, { PropTypes } from 'react';
import $ from 'jquery';

export class DragOverlayListener extends React.Component {

  static propTypes = {
    context: PropTypes.any,
    children: PropTypes.any
  };

  constructor(props) {
    super(props);
    this.state = {
      overlay: false
    };
  }

  componentDidMount() {
    this.getContext().forEach(context => {
      $(context).on('dragover', this.onDragStarted);
      $(context).on('drop dragover', this.onDefaultDrop);
    });
  }

  componentWillUnmount() {
    this.getContext().forEach(context => {
      $(context).off('dragover', this.onDragStarted);
      $(context).off('drop dragover', this.onDefaultDrop);
    });
  }

  onDefaultDrop = event => {
    event.preventDefault();
  };

  onDragStarted = () => {
    if (!this.timeout) {
      this.setState({
        overlay: true
      });
    } else {
      clearTimeout(this.timeout);
    }

    this.timeout = setTimeout(this.onDragEnd, 100);
  };

  onDragEnd = () => {
    this.timeout = null;
    this.setState({
      overlay: false
    });
  };

  getContext() {
    const { context = document } = this.props;
    return Array.isArray(context) ? context : [...context];
  }

  render() {
    return this.state.overlay ? this.props.children : null;
  }
}
