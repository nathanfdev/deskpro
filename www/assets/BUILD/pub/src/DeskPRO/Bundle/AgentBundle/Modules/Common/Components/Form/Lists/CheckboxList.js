import React, { PropTypes } from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import classNames from 'classnames';
import { CheckboxListItem } from './CheckboxListItem';
import Immutable from 'immutable';
import invariant from 'invariant';

export class CheckboxList extends React.Component {

  static propTypes = {
    values: PropTypes.object.isRequired,
    selected: PropTypes.object,
    renderLabelComponent: PropTypes.func.isRequired,
    showOnlySelected: PropTypes.bool,
    filter: PropTypes.string,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);

    invariant(Immutable.Iterable.isIterable(props.values), 'Expecting the "values" prop to be an Immutable.Iterable');
    invariant(Immutable.Set.isSet(props.selected), 'Expecting the "selected" prop to be an instance of Immutable.Set');

    this.state = {
      values: props.values,
      selected: props.selected,
      filter: props.filter,
      showOnlySelected: props.showOnlySelected
    };
  }

  componentWillReceiveProps(props) {
    invariant(Immutable.Iterable.isIterable(props.values), 'Expecting the "values" prop to be an Immutable.Iterable');

    this.setState({
      values: props.values,
      filter: props.filter,
      showOnlySelected: props.showOnlySelected
    });
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(this.state.values, state.values)
      || this.state.filter !== state.filter
      || this.state.showOnlySelected !== state.showOnlySelected
      ;
  }

  onChange(index, isChecked) {
    this.setState({
      selected: isChecked ? this.state.selected.add(index) : this.state.selected.delete(index)
    });
  }

  componentDidUpdate() {
    const selected = this.state.values.filter((item, index) => this.state.selected.has(index));
    this.props.onChange && this.props.onChange(selected);
  }

  renderItem(option, index) {
    const { renderLabelComponent, filter = '', showOnlySelected = false } = this.props;
    const child = renderLabelComponent(option);

    if (!child) {
      return null;
    }

    const keyword = child.props && child.props.keyword;

    if (filter && keyword && keyword.toLowerCase().indexOf(filter.toLowerCase()) === -1) {
      return null;
    }

    const checked = this.state.selected.has(index);

    if (showOnlySelected && !checked) {
      return null;
    }

    return (
      <li key={index}>
        <CheckboxListItem onChange={this.onChange.bind(this, index)} checked={checked}>
          {child}
        </CheckboxListItem>
      </li>
    );
  }

  render() {
    const { values } = this.state;

    return (
      <Scrollable vertical>
        <ul>
          {values.map((option, index) => this.renderItem(option, index))}
        </ul>
      </Scrollable>
    );
  }
}
