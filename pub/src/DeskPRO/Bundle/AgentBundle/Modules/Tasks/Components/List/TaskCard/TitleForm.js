import React, { PropTypes } from 'react';
import classNames from 'classnames';
import jQuery from 'jquery';

export class TitleForm extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      error: false,
      value: props.value
    };
  }

  componentDidMount() {
    jQuery(this.refs.input).focus();
  }

  onChange = event => {
    this.setState({
      value: event.target.value,
      error: false
    });
  };

  onSubmit = event => {
    event.preventDefault();
    const { value, onChange } = this.props;

    // Prevent sending empty data or set default value if it exists
    if (this.state.value) {
      onChange(this.state.value);
      return;
    }

    if (value) {
      this.setState({
        value: value
      });
    } else {
      this.setState({
        error: true
      });
    }
  };

  render() {
    return (
      <form className="inline-form" onSubmit={this.onSubmit}>
        <input type="text"
               ref="input"
               name="title"
               value={this.state.value}
               className={classNames({'error': this.state.error})}
               onChange={this.onChange} />
      </form>
    );
  }
}
