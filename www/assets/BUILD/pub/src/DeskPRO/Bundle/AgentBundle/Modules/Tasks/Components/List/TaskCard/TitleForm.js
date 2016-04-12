import React, { PropTypes } from 'react';
import { CardWidget } from './CardWidget';
import $ from 'jquery';

export class TitleForm extends CardWidget {

  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func,
    onSubmit: PropTypes.func
  };

  componentDidMount() {
    $(this.refs.input).focus();
  }

  componentWillUnmount() {
    this.props.onSubmit(this.state.value);
  }

  onInputChange = event => {
    this.onChange(event.target.value);
  };

  onSubmit = event => {
    event.preventDefault();
    this.props.onSubmit(this.state.value);
  };

  render() {
    const { value } = this.state;
    return (
      <form className="inline-form" onSubmit={this.onSubmit}>
        <input type="text" ref="input" name="title" value={value} onChange={this.onInputChange} />
      </form>
    );
  }
}
