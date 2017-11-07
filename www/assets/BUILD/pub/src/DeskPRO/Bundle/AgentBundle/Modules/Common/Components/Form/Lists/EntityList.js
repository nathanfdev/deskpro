import PropTypes from 'prop-types';
import React from 'react';
import { ChoiceList } from './ChoiceList';
import Immutable from 'immutable';
import invariant from 'invariant';

export class EntityList extends React.Component {

  static propTypes = {
    values:           PropTypes.object.isRequired,
    selected:         PropTypes.object,
    filter:           PropTypes.string,
    showOnlySelected: PropTypes.bool,
    onChange:         PropTypes.func
  };

  static defaultProps = {
    filter: ''
  };

  constructor(props) {
    super(props);
    invariant(Immutable.Iterable.isIterable(props.values), 'Expecting the "values" prop to be an Immutable.Iterable');
    const selected = props.selected || Immutable.Set([]);
    invariant(Immutable.Set.isSet(selected), 'Expecting the "selected" prop to be an instance of Immutable.Set');

    this.state = {
      values:           props.values,
      selected:         props.selected,
      filter:           props.filter,
      showOnlySelected: props.showOnlySelected
    };
  }

  componentWillReceiveProps(props) {
    invariant(Immutable.Iterable.isIterable(props.values), 'Expecting the "values" prop to be an Immutable.Iterable');
    const selected = props.selected || Immutable.Set([]);
    invariant(Immutable.Set.isSet(selected), 'Expecting the "selected" prop to be an instance of Immutable.Set');

    this.setState(
      {
        values:           props.values,
        selected:         props.selected,
        filter:           props.filter,
        showOnlySelected: props.showOnlySelected
      }
    );
  }

  shouldComponentUpdate(props, state) {
    return this.state.filter !== state.filter
      || this.state.showOnlySelected !== state.showOnlySelected
      || !Immutable.is(this.state.values, state.values)
      || !Immutable.is(this.state.selected, state.selected)
      ;
  }

  keyword() {
    return '';
  }

  render() {
    const { filter, showOnlySelected, selected } = this.state;

    const values = this.state.values.filter((value, id) => {
      if (showOnlySelected && !selected.has(id)) {
        return false;
      }

      const keyword = this.keyword(value);
      return !(keyword && keyword.toLowerCase().indexOf(filter.toLowerCase()) === -1);
    });

    return (
      <ChoiceList
        values={values}
        selected={selected}
        listItem={this.item}
        onChange={this.props.onChange}
      />
    );
  }
}
