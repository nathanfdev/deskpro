import React, { PropTypes } from 'react';

export class QuickFilter extends React.Component {

  static propTypes = {
    onChange: PropTypes.func
  };

  onChange = (event) => {
    this.props.onChange(event.target.value);
  };

  onReset = () => {
    this.forceUpdate();
    this.props.onChange('');
  };

  render() {
    return (
      <div className="dpw-quick-filter">
        <div className="dpw-quick-filter-container">
          <div className="dpw-quick-filter-icon">
            <i className="fa fa-filter" />
          </div>
          <input type="text" placeholder="Quick Filter" key={Date.now()} onChange={this.onChange} />
          <span className="dpw-quick-filter-clear-link" onClick={this.onReset}>
            <i className="fa fa-times-circle" />
          </span>
        </div>
      </div>
    );
  }
}
