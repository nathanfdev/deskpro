import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { CollectionField } from '../../../Common/Components/Popup';
import { AgentsListContainer } from './Lists/AgentsListContainer';
import Immutable from 'immutable';

export class AssignAgent extends Component {

  static propTypes = {
    me:               PropTypes.object.isRequired,
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

  assignMe = (event) => {
    event.preventDefault();
    const values = Immutable.Set([this.props.me.get('id')]);
    this.onChange(values);
  };

  renderTitle() {
    return (
      [
        <span key="title">
          Agent
        </span>,
        <span key="option" className="dpw--popup-item-collection-options" onClick={this.assignMe}>
          <a href="#">
            Assign To Me
          </a>
        </span>
      ]
    );
  }

  render() {
    const { selected, filter, showOnlySelected } = this.state;

    return (
      <CollectionField title={this.renderTitle()}>
        <AgentsListContainer
          selected={selected}
          filter={filter}
          showOnlySelected={showOnlySelected}
          onChange={this.onChange}
        />
      </CollectionField>
    );
  }
}
