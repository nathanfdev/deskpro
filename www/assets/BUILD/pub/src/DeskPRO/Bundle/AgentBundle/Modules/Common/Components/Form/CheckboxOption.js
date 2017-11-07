import PropTypes from 'prop-types';
import React, { Component } from 'react';

import { connect } from 'react-redux';
@connect()
export class CheckboxOption extends Component {
  static propTypes = {
    label:    PropTypes.string.isRequired,
    value:    PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
    values:   PropTypes.array,
    onClick:  PropTypes.func.isRequired,
    children: PropTypes.any
  };

  componentWillMount() {
    const { values, value } = this.props;
    this.setState({ isActive: values && values.indexOf(value) > -1 });
  }

  componentWillReceiveProps(nextProps) {
    const { values } = nextProps;
    this.setState({ isActive: values && values.indexOf(nextProps.value) > -1 });
  }

  handleClick = () => {
    const { value, onClick } = this.props;
    onClick(value);
  };

  render() {
    const { label, children } = this.props;

    return (
      <li>
        <div className={'dpw--popup-item-box'} onClick={this.handleClick}>
          <span className={'dpw--checkbox-boxy'}>
            {this.state.isActive && <i className="fa fa-check" />}
          </span>
          <span className="dpw-popup-item-collection-name">
            {label}
          </span>
        </div>
        {children}
      </li>
    );
  }
}
