import PropTypes from 'prop-types';
import React from 'react';
import BackButton from '../../../../../Common/Components/BackButton';
import SectionHeader from '../../../../../Common/Components/SectionHeader';
import { NumbersTable, BaseNumberRow } from '../NumbersTable';
import AvailableListForm from './AvailableListForm';

const numberTypes = {
  local:    'Local',
  tollfree: 'Toll free',
  mobile:   'Mobile',
  national: 'National',
  fixed:    'Fixed'
};

class AvailableList extends React.Component {

  static propTypes = {
    filter:         PropTypes.object,
    accounts:       PropTypes.object,
    numbers:        PropTypes.object,
    loading:        PropTypes.bool,
    onChangeFilter: PropTypes.func,
    onAddNumber:    PropTypes.func,
    onClickBack:    PropTypes.func
  };

  render() {
    const { accounts, numbers, filter, loading } = this.props;
    const { onChangeFilter, onAddNumber, onClickBack } = this.props;

    return (
      <div className="page">
        <BackButton onClick={onClickBack} />
        <SectionHeader title="Add new number" />

        <AvailableListForm
          value={filter}
          accounts={accounts}
          onChange={onChangeFilter}
        />
        {loading &&
          <div className="ui active inverted dimmer">
            <div className="ui text loader">Loading</div>
          </div>}
        <NumbersTable loading={loading} numbers={numbers}>
          <NumberRow onAddNumber={onAddNumber} />
        </NumbersTable>
      </div>
    );
  }
}

class NumberRow extends BaseNumberRow {

  render() {
    const { number } = this.props;
    const rateCenter = number.get('rate_center');
    const region     = number.get('region');

    let location;
    if (rateCenter && region) {
      location = `${rateCenter}, ${region}`;
    } else if (rateCenter) {
      location = rateCenter;
    } else {
      location = region;
    }

    return (
      <tr>
        <td>
          {this.renderNumber()}
          {location && <span className="location">{location}</span>}
        </td>
        <td>{numberTypes[number.get('type')]}</td>
        <td>{number.get('price')} {number.get('price_unit')}</td>
        {this.renderAddButton()}
      </tr>
    );
  }
}

export default AvailableList;
