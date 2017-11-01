import PropTypes from 'prop-types';
import React from 'react';

class TransferSearch extends React.Component {

  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func
  };

  render() {
    const { value, onChange } = this.props;

    return (
      <div className="ui left icon input transfer-list-search">
        <input
          type="text"
          placeholder="Search..."
          value={value}
          onChange={onChange}
        />
        <i className="search icon" />
      </div>
    );
  }
}

export default TransferSearch;
