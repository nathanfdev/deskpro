import React, { PropTypes } from 'react';

export class Language extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  onChange = (event) => {
    this.props.onChange(event.target.value);
  };

  render() {
    return (
      <div className="bucket-column">
        <a href="#" className="select">English <i className="fa fa-caret-down"></i></a>
      </div>
    );
  }
}
