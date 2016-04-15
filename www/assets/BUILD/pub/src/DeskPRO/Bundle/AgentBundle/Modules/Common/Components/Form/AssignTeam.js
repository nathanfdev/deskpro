import React, { Component, PropTypes } from 'react';
import { CollectionField } from '../../../Common/Components/Popup';
import { AgentTeamsListContainer } from './Lists/AgentTeamsListContainer';
import Immutable from 'immutable';

export class AssignTeam extends Component {

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

  assignMe(event) {
    event.preventDefault();
    const values = Immutable.Set([this.props.me.get('id')]);
    this.onChange(values);
  }

  renderTitle() {
    return (
      [
        <span key="title">
          Assign to Team
        </span>,
        <span key="option" className="dpw--popup-item-collection-options" onClick={this.assignMe.bind(this)}>
          <a href="#">Assign To My Team</a>
        </span>
      ]
    );
  }

  render() {
    const { selected, filter, showOnlySelected } = this.state;

    return (
      <CollectionField title={this.renderTitle()}>
        <AgentTeamsListContainer selected={selected} filter={filter} showOnlySelected={showOnlySelected}
                                 onChange={this.props.onChange} />
      </CollectionField>
    );
  }
}