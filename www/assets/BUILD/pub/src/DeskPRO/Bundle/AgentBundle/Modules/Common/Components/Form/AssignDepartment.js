import React, { Component, PropTypes } from 'react';
import { CollectionField } from '../../../Common/Components/Popup';
import { DepartmentsListContainer } from './Lists/DepartmentsListContainer';
import Immutable from 'immutable';

export class AssignDepartment extends Component {

  static propTypes = {
    me: PropTypes.object.isRequired,
    filter: PropTypes.string,
    selected: PropTypes.object,
    showOnlySelected: PropTypes.bool,
    onChange: PropTypes.func
  };

  static defaultProps = {
    multiple: false
  };

  constructor(props) {
    super(props);
    this.state = {
      selected: props.selected,
      showOnlySelected: props.showOnlySelected,
      filter: props.filter
    }
  }

  componentWillReceiveProps(props) {
    this.setState({
      selected: props.selected,
      showOnlySelected: props.showOnlySelected,
      filter: props.filter
    });
  }

  shouldComponentUpdate(props, state) {
    return this.state.filter !== state.filter
      || this.state.showOnlySelected !== state.showOnlySelected
      || !Immutable.is(this.state.selected, state.selected)
      ;
  }

  onChange(values) {
    this.setState({selected: values});
    this.props.onChange && this.props.onChange(values);
  }

  renderTitle() {
    return (
      <span key="title">
        Assign to Department
      </span>
    );
  }

  render() {
    const { selected, filter, showOnlySelected } = this.state;

    return (
      <CollectionField title={this.renderTitle()}>
        <DepartmentsListContainer selected={selected} filter={filter} showOnlySelected={showOnlySelected}
                                  onChange={this.props.onChange} />
      </CollectionField>
    );
  }
}