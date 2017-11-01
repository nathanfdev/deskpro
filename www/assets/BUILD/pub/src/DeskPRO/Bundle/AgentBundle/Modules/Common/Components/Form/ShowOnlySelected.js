import PropTypes from 'prop-types';
import React from 'react';

export class ShowOnlySelected extends React.Component {

  static propTypes = {
    value:    PropTypes.bool,
    onChange: PropTypes.func.isRequired
  };

  onClick = () => {
    this.props.onChange(!this.props.value);
  };

  render() {
    return (
      <div className="dpw-popup-content-item-show-only-selected">
        <a href="#" className="checkbox-link" onClick={this.onClick}>
          {this.props.value &&
          <span className="dpw--checkbox-boxy"><i className="fa fa-check" /></span>
          }
          <span>Show only Selected</span>
        </a>
      </div>
    );
  }
}
