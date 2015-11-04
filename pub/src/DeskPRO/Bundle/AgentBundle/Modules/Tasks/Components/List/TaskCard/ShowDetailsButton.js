import React, { PropTypes } from 'react';

export class ShowDetailsButton extends React.Component {

  static propTypes = {
    expanded: PropTypes.bool,
    onToggleExpand: PropTypes.func.isRequired
  };

  render() {
    const { expanded, onToggleExpand } = this.props;

    return (
      <div className="dpw--card-expand">
        <a href="#" onClick={onToggleExpand}>
          {expanded ? 'Collapse' : 'Expand'} <i className="fa fa-navicon"/>
        </a>
      </div>
    );
  }
}
