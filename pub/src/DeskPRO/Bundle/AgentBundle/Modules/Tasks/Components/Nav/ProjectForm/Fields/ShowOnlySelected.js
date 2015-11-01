import React, { PropTypes } from 'react';

export class ShowOnlySelected extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  onClick = () => {
    this.props.onChange(!this.props.value);
  };

  render() {
    return (
      <div className="dpw-popup-content-item-show-only-selected">
        <a href="#" className="checkbox-link" onClick={this.onClick}>
          <span>Show only Selected</span>
          {this.props.value &&
            <span className="dpw--checkbox-boxy"><i className="fa fa-check" /></span>
          }
        </a>
      </div>
    );
  }
}
