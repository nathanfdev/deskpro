import React, { PropTypes } from 'react';
import classNames from 'classnames';
import jQuery from 'jquery';

export class TitleForm extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  componentDidMount() {
    jQuery(this.refs.input).focus();
  }

  onChange = event => {
    this.props.onChange(event.target.value);
  };

  render() {
    const { value } = this.props;
    return (
      <form className="inline-form">
        <input type="text"
               ref="input"
               name="title"
               value={value}
               onChange={this.onChange} />
      </form>
    );
  }
}
