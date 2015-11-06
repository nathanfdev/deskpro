import React, { PropTypes } from 'react';
import jQuery from 'jquery';

export class ClickOut extends React.Component {

  static propTypes = {
    ignoreNodes: PropTypes.array,
    additionalNodes: PropTypes.array,
    children: PropTypes.node,
    onClickOut: PropTypes.func.isRequired,
    onClick: PropTypes.func
  };

  componentDidMount() {
    jQuery(document).on('click', this.onClick);
  }

  componentWillUnmount() {
    jQuery(document).off('click', this.onClick);
  }

  onClick = event => {
    event.preventDefault();
    const { additionalNodes, ignoreNodes, onClickOut, onClick } = this.props;

    // skip if clicking on one of the ignored nodes
    if (ignoreNodes) {
      let skip = false;

      ignoreNodes.forEach(ignored => {
        const node = ignored && ignored.node ? ignored.node : ignored;
        if (node) {
          if (node === event.target) {
            skip = true;
          }

          if (node.contains(event.target)) {
            skip = true;
          }
        }
      });

      if (skip) {
        return;
      }
    }

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
    } else if (onClick) {
      onClick(event);
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
