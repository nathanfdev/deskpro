import PropTypes from 'prop-types';
import React from 'react';
import { CheckboxListItem } from './CheckboxListItem';
import { RadioListItem } from './RadioListItem';
import Immutable from 'immutable';
import invariant from 'invariant';

export class ChoiceList extends React.Component {

  static propTypes = {
    values:   PropTypes.object.isRequired,
    selected: PropTypes.object,
    listItem: PropTypes.func,
    multiple: PropTypes.bool,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);

    invariant(Immutable.Iterable.isIterable(props.values), 'Expecting the "values" prop to be an Immutable.Iterable');
    const selected = props.selected || Immutable.Set([]);
    invariant(Immutable.Set.isSet(selected), 'Expecting the "selected" prop to be an instance of Immutable.Set');

    this.state = {
      values:   props.values,
      selected: props.selected
    };

    this.listItem = props.listItem;
    if (!this.listItem) {
      this.listItem = props.multiple ? CheckboxListItem : RadioListItem;
    }

    if (props.multiple) {
      this.onChange = this.onChangeMultiple.bind(this);
    } else {
      this.onChange = this.onChangeSingle.bind(this);
    }
  }

  componentWillReceiveProps(props) {
    invariant(Immutable.Iterable.isIterable(props.values), 'Expecting the "values" prop to be an Immutable.Iterable');
    const selected = props.selected || Immutable.Set([]);
    invariant(Immutable.Set.isSet(props.selected), 'Expecting the "selected" prop to be an instance of Immutable.Set');

    const first = selected.first();
    if (this.state.selected) {
      const old = this.state.selected.first();
      if (old && old !== first && this.refs[`child-${old}`]) {
        this.refs[`child-${old}`].setState({ checked: false });
      }
    }
    this.setState({ values: props.values, selected });
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(this.state.values, state.values)
      || !Immutable.is(this.state.selected, state.selected)
      ;
  }

  onChangeMultiple(index) {
    const isChecked = !this.refs[`child-${this.selected}`].state.checked;
    const selected  = isChecked ? this.state.selected.add(index) : this.state.selected.delete(index);
    this.setState({ selected });
    if (this.props.onChange) {
      this.props.onChange(selected);
    }
  }

  onChangeSingle(index) {
    const selected = Immutable.Set([index]);
    this.setState({ selected });
    if (this.props.onChange) {
      this.props.onChange(selected);
    }
  }

  renderItem = (option, index) => {
    const checked = this.state.selected && this.state.selected.has(index);

    return (
      <li key={index} className="choice-list-item" onClick={this.onChange.bind(this, index)}>
        {React.createElement(this.listItem, {
          checked,
          onChange: this.onChange.bind(this, index),
          ref:      `child-${index}`,
          value:    option
        })}
      </li>
    );
  };

  render() {
    const { values } = this.state;
    const render  = this.renderItem.bind(this);
    this.selected = null;

    return (
      <ul className="m-5">
        {Immutable.Map.isMap(values)
          ? values.entrySeq().map(([id, item]) => render(item, id))
          : values.map(render)
        }
      </ul>
    );
  }
}
