import PropTypes from 'prop-types';
import React from 'react';

export class QuickFilter extends React.Component {

  static propTypes = {
    onChange: PropTypes.func
  };

  shouldComponentUpdate() {
    return false;
  }

  onChange = (event) => {
    if (this.props.onChange) {
      this.props.onChange(event.target.value);
    }
  };

  reset = () => {
    this.forceUpdate();
    if (this.props.onChange) {
      this.props.onChange('');
    }
  };

  render() {
    return (
      <div className="dpw-quick-filter">
        <div className="dpw-quick-filter-container">
          <div className="dpw-quick-filter-icon"><i className="fa fa-filter"></i></div>
          <input type="text" placeholder="Quick Filter" key={Date.now()} onChange={this.onChange} />
          <span className="dpw-quick-filter-clear-link" onClick={this.reset}>
            <i className="fa fa-times-circle"></i>
          </span>
        </div>
      </div>
    );
  }
}
