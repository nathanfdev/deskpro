import React, { PropTypes } from 'react';
import $ from 'jquery';

export class DragOverlayListener extends React.Component {

  static propTypes = {
    context: PropTypes.oneOfType([
      PropTypes.object,
      PropTypes.arrayOf(PropTypes.object)
    ]),
    children: PropTypes.any,

    // todo temp to make fine-uploader works
    dropNode: PropTypes.string
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
      $(context).on('dragover drop', this.onDefaultDrop);
    });
  }

  componentWillUnmount() {
    this.getContext().forEach(context => {
      $(context).off('dragover', this.onDragStarted);
      $(context).off('dragover drop', this.onDefaultDrop);
    });
  }

  onDefaultDrop = event => {
    // todo
    // temp code to make fine-uploader works
    // remove after refactor of the portal attach component

    const { dropNode } = this.props;
    let skip = false;

    if (dropNode) {
      this.getContext().forEach(context => {
        const $dropNone = $(dropNode, context);
        if ($dropNone.is(event.target) || $dropNone.has(event.target).length > 0) {
          skip = true;
        }
      });
    }

    if (skip) {
      return;
    }
    // end of tmp code

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
    return Array.isArray(context) ? context : [context];
  }

  render() {
    return this.state.overlay ? this.props.children : null;
  }
}
