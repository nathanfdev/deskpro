import React, { Component, PropTypes } from 'react';
import update from 'react/lib/update';

export class ViewOptionsListContainer extends Component {

  static propTypes = {
    widgetClass: PropTypes.string,
    visibleFields: PropTypes.array,
    overrideWidgetClass: PropTypes.bool,
    children: PropTypes.node
  };

  constructor(props) {
    super(props);
    this.moveCard = this.moveCard.bind(this);
    this.state = {
      items: props.visibleFields.toArray()
    };
  }

  onChangeDisplayOrder = id => {
    const { value } = this.props;
    console.log('Value', this.props);
  };

  moveCard(dragIndex, hoverIndex) {
    const { items } = this.state;
    const dragCard = items[dragIndex];
    console.log('Items', items);
    console.log('Drag index', dragIndex);
    console.log('Drag card', dragCard);

    /*this.setState(update(this.state, {
      items: {
        $splice: [
          [dragIndex, 1],
          [hoverIndex, 0, dragCard]
        ]
      }
    }));*/
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