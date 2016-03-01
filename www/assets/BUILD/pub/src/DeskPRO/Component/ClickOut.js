import React, { PropTypes } from 'react';
import $ from 'jquery';

/**
 *
 * React components tree:
 * Detached > ClickOut > Content1 > Detached > ClickOut > Content2
 *
 * DOM tree:
 * Detached > ClickOut > Content1
 * Detached > ClickOut > Content2
 *
 * click on Content2 firest click outside of Content1
 */


export class ClickOut extends React.Component {

  static propTypes = {
    context: PropTypes.any,
    ignoreNodes: PropTypes.array,
    additionalNodes: PropTypes.any,
    children: PropTypes.node,
    onClickOut: PropTypes.func.isRequired,
    onClick: PropTypes.func
  };

  componentDidMount() {
    $(this.getContext()).on('click', this.onClick);

    const events = $._data(this.getContext(), 'events');
    if (events) {
      events.click = events.click || [];
      events.click.sort((a, b) => b.guid - a.guid);

      $._data(this.getContext(), 'events', events);
    }
  }

  componentWillUnmount() {
    $(this.getContext()).off('click', this.onClick);
  }

  onClick = event => {
    event.stopImmediatePropagation();

    const { additionalNodes = [], ignoreNodes, onClickOut, onClick } = this.props;
    // skip if clicking on one of the ignored nodes
    if (ignoreNodes) {
      let skip = false;

      ignoreNodes.forEach(ignored => {
        let nodes;
        if (typeof ignored === 'string') {
          nodes = $.find(ignored);
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
            if ($.contains(domNode, event.target)) {
              skip = true;
            }
          }
        });
      });

      if (skip) {
        return;
      }
    }

    const nodes = Array.isArray(additionalNodes) ? [...additionalNodes] : [additionalNodes];
    nodes.push(this.refs.container);
    let outside = true;
    nodes.forEach(node => {
      const $container = $(node);
      if ($container.is(event.target) || $container.has(event.target).length > 0) {
        outside = false;
      }

      // If event target element was removed before this handler was called we can check by its class name
      if (typeof node === 'string' && node.length > 0) {
        const $target = $(event.target);
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
      <div ref="container">
        {this.props.children}
      </div>
    );
  }
}
