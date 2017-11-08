import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';

export class NumbersTable extends React.Component {

  static propTypes = {
    loading:  PropTypes.bool,
    numbers:  PropTypes.object,
    children: PropTypes.node
  };

  render() {
    const { loading, numbers = Immutable.fromJS([]), children } = this.props;

    if (!loading && !numbers.size) {
      return (
        <div>No available numbers exist.</div>
      );
    }

    return (
      <table className="twilio-number-search-table">
        <tbody>
          {numbers.map((number, key) => React.cloneElement(children, { ...children.props, key, number }))}
        </tbody>
      </table>
    );
  }
}

export class BaseNumberRow extends React.Component {

  static propTypes = {
    number:      PropTypes.object,
    onAddNumber: PropTypes.func
  };

  onAddNumber = () => {
    const { number, onAddNumber } = this.props;
    onAddNumber(number);
  };

  renderAddButton() {
    const { number } = this.props;

    return (
      <td>
        {number.get('added')
          ?
            <span className="number-added">
              Added
            </span>
          :
            <button
              className="ui basic button"
              onClick={this.onAddNumber}
            >
              Add this number
            </button>
        }
      </td>
    );
  }

  renderNumber() {
    const { number } = this.props;

    return (
      <span className="number">
        +{number.get('number_country_code')} {number.get('national_number')}
      </span>
    );
  }
}
