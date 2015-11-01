import React, { PropTypes } from 'react';

export class QuickFilter extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  onChange = event => {
    this.props.onChange(event.target.value);
  };

  onClear = () => {
    this.props.onChange('');
  };

  render() {
    return (
      <div className="dpw-quick-filter">
        <div className="dpw-quick-filter-container">
          <div className="dpw-quick-filter-icon"><i className="fa fa-filter" /></div>
          <input type="text"
                 placeholder="Quick Filter"
                 value={this.props.value}
                 onChange={this.onChange} />

          <span className="dpw-quick-filter-clear-link" onClick={this.onClear}>
            <i className="fa fa-times-circle"></i>
          </span>
        </div>
      </div>
    );
  }
}
