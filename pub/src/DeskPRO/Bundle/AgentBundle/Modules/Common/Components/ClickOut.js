import React, { PropTypes } from 'react';
import jQuery from 'jquery';

export class ClickOut extends React.Component {

  static propTypes = {
    children: PropTypes.node.isRequired,
    onClickOut: PropTypes.func.isRequired
  };

  componentDidMount() {
    jQuery(document).on('click', this.onClick);
  }

  componentWillUnmount() {
    jQuery(document).off('click', this.onClick);
  }

  onClick = event => {
    var $container = jQuery(this.refs.container);
    if (!$container.is(event.target) && $container.has(event.target).length === 0) {
      this.props.onClickOut(event);
    }
  };

  render() {
    return (
      <div ref="container">
        {this.props.children}
      </div>
    );
  }
}
