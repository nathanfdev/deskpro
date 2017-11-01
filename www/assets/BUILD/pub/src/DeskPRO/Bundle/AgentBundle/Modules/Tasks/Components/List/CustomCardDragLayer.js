import PropTypes from 'prop-types';
import React from 'react';
import { DragLayer } from 'react-dnd';
import { constants } from '../../../../Constants/Constants';

@DragLayer(monitor => ({
  item:          monitor.getItem(),
  itemType:      monitor.getItemType(),
  currentOffset: monitor.getSourceClientOffset(),
  isDragging:    monitor.isDragging()
}))
export class CustomCardDragLayer extends React.Component {

  static propTypes = {
    item:          PropTypes.object,
    currentOffset: PropTypes.shape({
      x: PropTypes.number.isRequired,
      y: PropTypes.number.isRequired
    }),
    isDragging: PropTypes.bool.isRequired,
    children:   PropTypes.node.isRequired,
    itemType:   PropTypes.string
  };

  shouldComponentUpdate(props) {
    return props.itemType === constants.TYPE_TASK;
  }

  getItemStyles() {
    const { currentOffset } = this.props;
    if (!currentOffset) {
      return {
        display: 'none'
      };
    }

    const transform = `translate(${currentOffset.x}px, ${currentOffset.y}px)`;

    return {
      transform,
      WebkitTransform: transform
    };
  }

  render() {
    const { isDragging, item, children, itemType } = this.props;
    const childProps = children.props;

    if (itemType !== constants.TYPE_TASK) {
      return null;
    }

    if (!isDragging) {
      return null;
    }

    return (
      <div style={{
        position:      'fixed',
        pointerEvents: 'none',
        zIndex:        10000,
        left:          0,
        top:           0,
        width:         '100%',
        height:        '100%'
      }}
      >

        <div style={this.getItemStyles()}>
          {React.cloneElement(children, { ...childProps, item })}
        </div>
      </div>
    );
  }
}
