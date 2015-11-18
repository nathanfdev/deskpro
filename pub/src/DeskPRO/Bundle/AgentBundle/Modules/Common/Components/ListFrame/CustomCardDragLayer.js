import React, { PropTypes } from 'react';
import { DragLayer } from 'react-dnd';

@DragLayer(monitor => ({
  currentOffset: monitor.getSourceClientOffset(),
  isDragging: monitor.isDragging()
}))
export class CustomCardDragLayer extends React.Component {

  static propTypes = {
    item: PropTypes.object,
    itemType: PropTypes.string,
    initialOffset: PropTypes.shape({
      x: PropTypes.number.isRequired,
      y: PropTypes.number.isRequired
    }),
    currentOffset: PropTypes.shape({
      x: PropTypes.number.isRequired,
      y: PropTypes.number.isRequired
    }),
    isDragging: PropTypes.bool.isRequired,
    children: PropTypes.any
  };

  getItemStyles() {
    const { currentOffset } = this.props;
    if (!currentOffset) {
      return {
        display: 'none'
      };
    }

    const transform = `translate(${currentOffset.x}px, ${currentOffset.y}px)`;
    return {
      transform: transform,
      WebkitTransform: transform
    };
  }

  render() {
    const { isDragging, children } = this.props;
    if (!isDragging) {
      return null;
    }

    return (
      <div style={{
        position: 'fixed',
        pointerEvents: 'none',
        zIndex: 10000,
        left: 0,
        top: 0,
        width: '100%',
        height: '100%'
      }}>

        <div style={this.getItemStyles()}>
          {children}
        </div>
      </div>
    );
  }
}
