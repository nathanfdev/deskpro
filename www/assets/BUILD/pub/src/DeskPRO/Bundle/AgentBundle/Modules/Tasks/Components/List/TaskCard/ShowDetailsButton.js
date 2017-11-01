import PropTypes from 'prop-types';
import React from 'react';

export class ShowDetailsButton extends React.Component {

  static propTypes = {
    expanded:       PropTypes.bool,
    onToggleExpand: PropTypes.func.isRequired
  };

  onClick = event => {
    event.preventDefault();
    this.props.onToggleExpand();
  };

  render() {
    return (
      <div className="dpw--card-expand">
        <a href="#" onClick={this.onClick}>
          {this.props.expanded ? 'Collapse' : 'Expand'} <i className="fa fa-navicon" />
        </a>
      </div>
    );
  }
}
