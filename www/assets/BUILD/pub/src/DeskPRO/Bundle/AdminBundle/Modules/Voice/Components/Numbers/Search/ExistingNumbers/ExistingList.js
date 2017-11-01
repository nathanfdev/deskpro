import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import HasNextPagination from 'DeskPRO/Component/Semantic/Pagination/HasNextPagination';
import SectionHeader from '../../../../../Common/Components/SectionHeader';
import BackButton from '../../../../../Common/Components/BackButton';
import { NumbersTable, BaseNumberRow } from '../NumbersTable';
import ExistingListForm from './ExistingListForm';

class ExistingList extends React.Component {

  static propTypes = {
    onClickBack:    PropTypes.func,
    onChangeFilter: PropTypes.func,
    onAddNumber:    PropTypes.func,
    loading:        PropTypes.bool,
    accounts:       PropTypes.object,
    filter:         PropTypes.object,
    numbers:        PropTypes.object,
    pageNum:        PropTypes.number,
    hasNext:        PropTypes.bool,
    onChangePage:   PropTypes.func
  };

  render() {
    const { accounts = Immutable.fromJS([]), numbers = Immutable.fromJS([]) } = this.props;
    const { filter, loading, pageNum, hasNext } = this.props;
    const { onClickBack, onAddNumber, onChangeFilter, onChangePage } = this.props;

    return (
      <div className="page">
        <BackButton onClick={onClickBack} />
        <SectionHeader title="Add new number" />

        {accounts.size > 1 &&
          <ExistingListForm
            value={filter}
            accounts={accounts}
            onChange={onChangeFilter}
          />}
        {loading &&
          <div className="ui active inverted dimmer">
            <div className="ui text loader">Loading</div>
          </div>}
        <div>
          <NumbersTable loading={loading} numbers={numbers}>
            <NumberRow onAddNumber={onAddNumber} />
          </NumbersTable>
          <br />
          {(pageNum > 1 || numbers.size > 0) &&
            <HasNextPagination
              pageNum={pageNum}
              hasNext={hasNext}
              onClick={onChangePage}
            />}
        </div>
      </div>
    );
  }
}

class NumberRow extends BaseNumberRow {

  render() {
    return (
      <tr>
        <td>{this.renderNumber()}</td>
        {this.renderAddButton()}
      </tr>
    );
  }
}

export default ExistingList;
