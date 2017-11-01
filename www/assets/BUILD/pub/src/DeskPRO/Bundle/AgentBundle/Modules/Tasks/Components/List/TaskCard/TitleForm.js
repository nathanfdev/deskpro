import PropTypes from 'prop-types';
import React from 'react';
import jQuery from 'jquery';
import { CardWidget } from './CardWidget';

export class TitleForm extends CardWidget {

  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func,
    onSubmit: PropTypes.func
  };

  componentDidMount() {
    jQuery(this.refs.input).focus();
  }

  onChange = event => {
    const val = event.target.value;
    this.setState({ value: val });
    this.props.onChange && this.props.onChange(val);
  };

  onSubmit = event => {
    event.preventDefault();
    this.props.onSubmit && this.props.onSubmit();
  };

  render() {
    const { value } = this.state;
    return (
      <form className="inline-form" onSubmit={this.onSubmit}>
        <input type="text"
          ref="input"
          name="title"
          value={value}
          onChange={this.onChange}
        />
      </form>
    );
  }
}
