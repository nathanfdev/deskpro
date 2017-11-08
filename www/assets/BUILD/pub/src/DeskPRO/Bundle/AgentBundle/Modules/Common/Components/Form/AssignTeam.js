import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { CollectionField } from '../../../Common/Components/Popup';
import { AgentTeamsListContainer } from './Lists/AgentTeamsListContainer';
import Immutable from 'immutable';

export class AssignTeam extends Component {

  static propTypes = {
    team:             PropTypes.object,
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

  assignMine = (event) => {
    event.preventDefault();
    if (this.props.team) {
      const values = Immutable.Set([this.props.team.get('id')]);
      this.onChange(values);
    }
  };

  renderTitle() {
    const { team } = this.props;
    const title = <span key="title">Team</span>;

    if (!team) {
      return title;
    }

    return ([
      title,
      <span key="option" className="dpw--popup-item-collection-options" onClick={this.assignMine}>
        <a href="#">
          Assign To Mine
        </a>
      </span>
    ]);
  }

  render() {
    const { selected, filter, showOnlySelected } = this.state;

    return (
      <CollectionField title={this.renderTitle()}>
        <AgentTeamsListContainer
          selected={selected}
          filter={filter}
          showOnlySelected={showOnlySelected}
          onChange={this.onChange}
        />
      </CollectionField>
    );
  }
}
