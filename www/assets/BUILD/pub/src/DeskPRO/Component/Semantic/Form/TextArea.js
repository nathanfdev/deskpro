import React, { PropTypes } from 'react';
import classNames from 'classnames';

class TextArea extends React.Component {
  static propTypes = {
    id:          PropTypes.string,
    name:        PropTypes.string,
    placeholder: PropTypes.string,
    value:       PropTypes.string,
    rows:        PropTypes.number
  };

  render() {
    const { placeholder, id, value, rows } = this.props;
    return (
      <div className={classNames('ui', 'textarea')}>
        <textarea
          id={id}
          ref={(c) => { this.input = c; }}
          name={name}
          placeholder={placeholder}
          rows={rows}
        >
          {value}
        </textarea>
      </div>
    );
  }
}
export default TextArea;
