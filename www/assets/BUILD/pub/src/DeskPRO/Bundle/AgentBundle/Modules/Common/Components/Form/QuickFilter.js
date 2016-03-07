import React, { PropTypes } from 'react';

export class QuickFilter extends React.Component {

  static propTypes = {
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      value: ''
    };
  }

  onChange = (event) => {
    this.setState({value: event.target.value});
    this.props.onChange && this.props.onChange(event.target.value);
  };

  render() {
    return (
      <div className="dpw-quick-filter">
        <div className="dpw-quick-filter-container">
          <div className="dpw-quick-filter-icon"><i className="fa fa-filter"></i></div>
          <input type="text" placeholder="Quick Filter" onChange={this.onChange} />
          <span className="dpw-quick-filter-clear-link" onClick={this.onChange.bind(this, '')}>
            <i className="fa fa-times-circle"></i>
          </span>
        </div>
      </div>
    );
  }
}
