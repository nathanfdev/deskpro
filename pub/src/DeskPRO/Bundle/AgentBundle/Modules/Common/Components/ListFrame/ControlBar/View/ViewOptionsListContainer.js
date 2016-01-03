import React, { Component, PropTypes } from 'react';

export class ViewOptionsListContainer extends Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: PropTypes.string,
    overrideWidgetClass: PropTypes.bool,
    children: PropTypes.node
  };

  onChangeDisplayOrder = id => {
    const { value } = this.props;
    console.log('Value', this.props);
  };

  moveCard(dragIndex, hoverIndex) {
    const { cards } = this.state;
    const dragCard = cards[dragIndex];

    this.setState(update(this.state, {
      cards: {
        $splice: [
          [dragIndex, 1],
          [hoverIndex, 0, dragCard]
        ]
      }
    }));
  }

  render() {
// dpw-navigation-dropdown-column-list-v2 must be a widgetClass prop, because in some Item list we don't need this class
    return (
      <div className="dpw-navigation-dropdown-column-list">
        <ul>
          {React.Children.map(this.props.children, (child, index) => {
            return React.cloneElement(child, {
              widgetClass: 'dpw-navigation-dropdown-column-list-item',
              overrideWidgetClass: true,
              listItem: true,
              onChangeDisplayOrder: this.onChangeDisplayOrder,
              index: index,
              id: child.value,
              moveCard: this.moveCard,
              key: index
            });
          })}
        </ul>
      </div>
    );
  }
}