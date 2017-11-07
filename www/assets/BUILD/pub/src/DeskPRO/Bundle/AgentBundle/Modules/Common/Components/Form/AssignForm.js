import PropTypes from 'prop-types';
import React, { Component, Children } from 'react';
import Immutable from 'immutable';
import { ShowOnlySelected, Unassign } from './';
import { QuickFilter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/QuickFilter';

export class AssignForm extends Component {

  static propTypes = {
    title:    PropTypes.string,
    children: PropTypes.any
  };

  constructor(props) {
    super(props);

    this.state = {
      filter:           '',
      showOnlySelected: false
    };
  }

  shouldComponentUpdate(props, state) {
    return this.state.filter !== state.filter || this.state.showOnlySelected !== state.showOnlySelected;
  }

  onChangeQuickFilter = value => {
    this.setState({ filter: value });
  };

  onChangeFilterSelected = value => {
    this.setState({ showOnlySelected: value });
  };

  onUnassignAll = () => {
    const set = Immutable.Set();
    Children.map(this.props.children, (child) => {
      if (child.props.onChange) {
        child.props.onChange(set);
      }
    });
  };

  render() {
    const { title } = this.props;
    const { filter, showOnlySelected } = this.state;
    const newChildren = Children.map(this.props.children, (child) =>
      React.cloneElement(child, { filter, showOnlySelected })
    );

    return (
      <div>
        <div className="dpw--popup-content-line">
          <div className="dpw--popup-content-left">
            {title ? <h2 className="dpw--popup-item-section-title">
              {title}
            </h2> : null}
            <QuickFilter value={filter} onChange={this.onChangeQuickFilter} />
          </div>

          <div className="dpw--popup-content-right">
            <div className="dpw--popup-content-item">
              <ShowOnlySelected value={showOnlySelected} onChange={this.onChangeFilterSelected} />
            </div>
            <div className="dpw--popup-content-item">
              <Unassign onClick={this.onUnassignAll} />
            </div>
          </div>
        </div>


        <div className="dpw--popup-content-line">
          {newChildren}
        </div>
      </div>
    );
  }
}
