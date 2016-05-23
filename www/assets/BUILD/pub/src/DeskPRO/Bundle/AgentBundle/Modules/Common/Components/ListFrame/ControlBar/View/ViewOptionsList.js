import React, { Component, PropTypes } from 'react';
import Immutable from 'immutable';
import { ViewField } from './ViewField';

export class ViewOptionsList extends Component {

  static propTypes = {
    widgetClass:         PropTypes.string,
    visibleFields:       PropTypes.object.isRequired,
    configurableFields:  PropTypes.object.isRequired,
    overrideWidgetClass: PropTypes.bool,
    children:            PropTypes.node,
    onClick:             PropTypes.func.isRequired,
    type:                PropTypes.string.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      items: this.setItemsFromProps(props)
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      items: this.setItemsFromProps(props)
    });
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(state.items, this.state.items);
  }

  onChangeDisplayOrder = (from, to) => {
    let { items } = this.state;
    const fromItem = items.get(from);
    const toItem = items.get(to);
    if (fromItem && toItem) {
      items = items.set(from, toItem);
      items = items.set(to, fromItem);
      this.setState({ items });
    }
  };

  setItemsFromProps(props) {
    return Immutable.List().withMutations(items => {
      for (const [key, value] of Object.entries(props.configurableFields)) {
        items.push({ key, value });
      }
    });
  }

  render() {
    // dpw-navigation-dropdown-column-list-v2 must be a widgetClass prop, because in some Item list we don't need this class
    const { visibleFields, onClick, type } = this.props;
    const { items } = this.state;

    return (
      <div className="dpw-navigation-dropdown-column-list">
        <ul>
          {items.map(({ key, value }, index) =>
            <ViewField
              key={`${index}`}
              index={index}
              value={key}
              label={value}
              type={type}
              isShown={visibleFields.indexOf(key) > -1}
              changeState={onClick}
              widgetClass="dpw-navigation-dropdown-column-list-item"
              onChangeDisplayOrder={this.onChangeDisplayOrder}
              overrideWidgetClass
              listItem
            />
          )}
        </ul>
      </div>
    );
  }
}
