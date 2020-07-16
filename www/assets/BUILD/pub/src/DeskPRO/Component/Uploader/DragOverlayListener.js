import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';

export class DragOverlayListener extends React.Component {

  static propTypes = {
    context: PropTypes.oneOfType([
      PropTypes.object,
      PropTypes.arrayOf(PropTypes.object)
    ]),
    children: PropTypes.node,
    types:    PropTypes.array,
  };

  constructor(props) {
    super(props);
    this.state = {
      overlay: false
    };
  }

  componentDidMount() {
    this.getContext().forEach((context) => {
      $(context).on('dragover', this.onDragStarted);
      $(context).on('dragover drop', this.onDefaultDrop);
    });
  }

  componentWillUnmount() {
    this.getContext().forEach((context) => {
      $(context).off('dragover', this.onDragStarted);
      $(context).off('dragover drop', this.onDefaultDrop);
    });
  }

  onDefaultDrop = (event) => {
    event.preventDefault();
  };

  onDragStarted = (e) => {
    const ev = e.originalEvent;
    const { types } = this.props;
    if (!this.timeout) {
      let type = null;
      ev.preventDefault();
      ev.dataTransfer.dropEffect = 'move';
      if (ev.dataTransfer.items.length > 0) {
        const file = ev.dataTransfer.items[0];
        type = file.type.slice(0, file.type.indexOf('/'));
      }
      if (!type || (types.length && types.indexOf(type) !== -1)) {
        this.setState({
          overlay: true
        });
      }
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
    return Array.isArray(context) ? context : [context];
  }

  render() {
    return this.state.overlay ? this.props.children : null;
  }
}
