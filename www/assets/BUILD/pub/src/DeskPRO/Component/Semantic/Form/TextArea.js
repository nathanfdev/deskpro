import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class TextArea extends React.Component {
  static propTypes = {
    id:          PropTypes.string,
    name:        PropTypes.string,
    placeholder: PropTypes.string,
    value:       PropTypes.string,
    rows:        PropTypes.number,
    onChange:    PropTypes.func
  };
  static defaultProps = {
    onChange() {}
  };

  handleChange = (event) => {
    this.props.onChange(event.target.value);
  };

  render() {
    const { placeholder, id, value, name, rows } = this.props;
    return (
      <div className={classNames('ui', 'textarea')}>
        <textarea
          id={id}
          ref={(c) => { this.input = c; }}
          name={name}
          value={value}
          onChange={this.handleChange}
          placeholder={placeholder}
          rows={rows}
        />
      </div>
    );
  }
}
export default TextArea;
