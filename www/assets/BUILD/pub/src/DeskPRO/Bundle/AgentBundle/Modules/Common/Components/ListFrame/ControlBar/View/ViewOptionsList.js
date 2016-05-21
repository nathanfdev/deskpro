import React, { Component, PropTypes } from 'react';
import update from 'react/lib/update';
import Immutable from 'immutable';
import { ViewField } from './ViewField';

export class ViewOptionsList extends Component {

  static propTypes = {
    widgetClass:         PropTypes.string,
    visibleFields:       PropTypes.object.isRequired,
    configurableFields:  PropTypes.object.isRequired,
    overrideWidgetClass: PropTypes.bool,
    children:            PropTypes.node,
    onClick:             PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.moveCard = this.moveCard.bind(this);
    this.state = {
      items: props.visibleFields
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      items: props.visibleFields
    });
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(state.items, this.state.items);
  }

  onChangeDisplayOrder = () => {
    const { value } = this.props;
    console.log('Value', value);
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
    const { configurableFields, visibleFields, onClick } = this.props;

    return (
      <div className="dpw-navigation-dropdown-column-list">
        <ul>
          {Object.entries(configurableFields).map(([name, label], index) =>
            <ViewField
              key={`${index}`}
              value={name}
              label={label}
              isShown={visibleFields.indexOf(name) > -1}
              changeState={onClick}
              widgetClass="dpw-navigation-dropdown-column-list-item"
              onChangeDisplayOrder={this.onChangeDisplayOrder}
              moveCard={this.moveCard}
              overrideWidgetClass
              listItem
            />
          )}
        </ul>
      </div>
    );
  }
}
