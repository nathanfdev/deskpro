import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';

/**
 *
 * React components tree:
 * Detached > ClickOut > Content1 > Detached > ClickOut > Content2
 *
 * DOM tree:
 * Detached > ClickOut > Content1
 * Detached > ClickOut > Content2
 * fixme:
 * click on Content2 fires click outside of Content1
 */


export class ClickOut extends React.Component {

  static propTypes = {
    context:         PropTypes.array,
    ignoreNodes:     PropTypes.array,
    additionalNodes: PropTypes.array,
    children:        PropTypes.node,
    onClickOut:      PropTypes.func.isRequired,
    onClick:         PropTypes.func,
    className:       PropTypes.string
  };

  componentDidMount() {
    $(this.getContext()).on('click touchend', this.onClick);

    const events = $.data(this.getContext(), 'events');
    if (events) {
      events.click = events.click || [];
      events.click.sort((a, b) => b.guid - a.guid);

      $.data(this.getContext(), 'events', events);
    }
  }

  componentWillUnmount() {
    $(this.getContext()).off('click touchend', this.onClick);
  }

  onClick = (event) => {
    // dont use stopImmediatePropagation here because there may be multiple
    // event listeners on the same context, and if we immediately stop propagation,
    // it stops all listeners, not just the bubble
    event.stopPropagation();

    const { additionalNodes = [], ignoreNodes, onClickOut, onClick } = this.props;
    // skip if clicking on one of the ignored nodes
    if (ignoreNodes) {
      let skip = false;

      ignoreNodes.forEach((ignored) => {
        let nodes;
        if (typeof ignored === 'string') {
          nodes = $.find(ignored);
        } else {
          nodes = [ignored];
        }

        nodes.map((node) => {
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

          return null;
        });
      });

      if (skip) {
        return;
      }
    }

    const nodes = Array.isArray(additionalNodes) ? [...additionalNodes] : [additionalNodes];
    nodes.push(this.container);
    let outside = true;
    nodes.forEach((node) => {
      const $container = $(node);
      if ($container.is(event.target) || $container.has(event.target).length > 0) {
        outside = false;
      }

      // If event target element was removed before this handler was called we can check by its class name
      if (typeof node === 'string' && node.length > 0) {
        const $target = $(event.target);
        if (node[0] === '.' && ($target.hasClass(node.substr(1)) || $target.parent().hasClass(node.substr(1)))) {
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
    if (this.props.context) {
      return this.props.context;
    }

    const context = [document];
    $('iframe').each((i, iframe) => {
      try {
        context.push(iframe.contentWindow.document);
      } catch (e) {
        // cross origin frame
      }
    });

    return context;
  }

  render() {
    return (
      <div className={this.props.className} ref={(c) => { this.container = c; }}>
        {this.props.children}
      </div>
    );
  }
}
