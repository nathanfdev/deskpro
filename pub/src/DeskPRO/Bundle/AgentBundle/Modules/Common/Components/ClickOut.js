import React, { PropTypes } from 'react';
import jQuery from 'jquery';

export class ClickOut extends React.Component {

  static propTypes = {
    additionalNodes: PropTypes.array,
    children: PropTypes.node,
    onClickOut: PropTypes.func.isRequired
  };

  componentDidMount() {
    jQuery(document).on('click', this.onClick);
  }

  componentWillUnmount() {
    jQuery(document).off('click', this.onClick);
  }

  onClick = event => {
    const { additionalNodes, onClickOut } = this.props;
    const nodes = additionalNodes || [];
    nodes.push(this.refs.container);

    let outside = true;
    nodes.forEach(node => {
      const $container = jQuery(node);
      if ($container.is(event.target) || $container.has(event.target).length > 0) {
        outside = false;
      }
    });

    if (outside) {
      onClickOut(event);
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
