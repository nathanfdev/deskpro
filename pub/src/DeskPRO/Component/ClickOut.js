import React, { PropTypes } from 'react';
import jQuery from 'jquery';

export class ClickOut extends React.Component {

  static propTypes = {
    context: PropTypes.any,
    ignoreNodes: PropTypes.array,
    additionalNodes: PropTypes.array,
    children: PropTypes.node,
    onClickOut: PropTypes.func.isRequired,
    onClick: PropTypes.func
  };

  componentDidMount() {
    jQuery(this.getContext()).on('click', this.onClick);
    let events = jQuery._data(this.getContext(), 'events');
    events.click = events.click || [];
    events.click.sort(function(a, b){
      return b.guid - a.guid;
    });
    jQuery._data(this.getContext(), 'events', events);
  }

  componentWillUnmount() {
    jQuery(this.getContext()).off('click', this.onClick);
  }

  onClick = event => {
    const { additionalNodes = [], ignoreNodes, onClickOut, onClick } = this.props;
    // skip if clicking on one of the ignored nodes
    if (ignoreNodes) {
      let skip = false;

      ignoreNodes.forEach(ignored => {
        let nodes;
        if (typeof ignored === 'string') {
          nodes = jQuery.find(ignored);
        } else {
          nodes = [ignored];
        }

        nodes.map(node => {
          const domNode = node && node.node ? node.node : node;
          if (domNode) {
            // skip when clicking on a node itself
            if (domNode === event.target) {
              skip = true;
            }

            // skip when clicking on a child of the ignored node
            if (jQuery.contains(domNode, event.target)) {
              skip = true;
            }
          }
        });
      });

      if (skip) {
        return;
      }
    }

    const nodes = [...additionalNodes];
    nodes.push(this.refs.container);
    let outside = true;
    nodes.forEach(node => {
      const $container = jQuery(node);
      if ($container.is(event.target) || $container.has(event.target).length > 0) {
        outside = false;
      }

      // If event target element was removed before this handler was called we can check by its class name
      if (typeof node === 'string' && node.length > 0) {
        const $target = jQuery(event.target);
        if (node[0] === '.' && $target.hasClass(node.substr(1))) {
          outside = false;
        }
      }
    });

    if (outside) {
      onClickOut(event);
    } else if (onClick) {
      onClick(event);
    }
  };

  getContext() {
    return this.props.context || document;
  }

  render() {
    return (
      <clickout ref="container">
        {this.props.children}
      </clickout>
    );
  }
}
