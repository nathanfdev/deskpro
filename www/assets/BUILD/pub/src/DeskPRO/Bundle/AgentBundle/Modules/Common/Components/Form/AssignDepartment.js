import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { CollectionField } from '../../../Common/Components/Popup';
import { DepartmentsListContainer } from './Lists/DepartmentsListContainer';
import Immutable from 'immutable';

export class AssignDepartment extends Component {

  static propTypes = {
    filter:           PropTypes.string,
    selected:         PropTypes.object,
    showOnlySelected: PropTypes.bool,
    onChange:         PropTypes.func
  };

  static defaultProps = {
    multiple: false
  };

  constructor(props) {
    super(props);
    this.state = {
      selected:         props.selected.toSet(),
      showOnlySelected: props.showOnlySelected,
      filter:           props.filter
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      selected:         props.selected.toSet(),
      showOnlySelected: props.showOnlySelected,
      filter:           props.filter
    });
  }

  shouldComponentUpdate(props, state) {
    return this.state.filter !== state.filter
      || this.state.showOnlySelected !== state.showOnlySelected
      || !Immutable.is(this.state.selected, state.selected)
      ;
  }

  onChange = (selected) => {
    this.setState({ selected });
    if (this.props.onChange) {
      this.props.onChange(selected.toList());
    }
  };

  renderTitle() {
    return (
      <span key="title">
        Department
      </span>
    );
  }

  render() {
    const { selected, filter, showOnlySelected } = this.state;

    return (
      <CollectionField title={this.renderTitle()}>
        <DepartmentsListContainer selected={selected} filter={filter} showOnlySelected={showOnlySelected}
          onChange={this.onChange}
        />
      </CollectionField>
    );
  }
}
